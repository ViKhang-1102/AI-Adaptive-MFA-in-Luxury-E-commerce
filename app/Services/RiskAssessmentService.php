<?php

namespace App\Services;

use App\Mail\RedFlagWarningMail;
use App\Models\SecurityAudit;
use App\Models\User;
use App\Models\VerifiedDevice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class RiskAssessmentService
{
    /**
     * Analyze a transaction using the AI-Driven Risk Scoring API.
     */
    public function analyze(User $user, float $amount, string $paymentMethod = 'unknown'): ?array
    {
        try {
            $transactionAmount = round($amount, 2);
            $loginTime = now()->toIso8601String(); 
            
            // Collect context for Guard Agent
            $knownIps = Session::get('user_known_ips', []);
            $currentIp = request()->ip() ?? '127.0.0.1';
            
            if (!in_array($currentIp, $knownIps)) {
                $knownIps[] = $currentIp;
                Session::put('user_known_ips', $knownIps);
            }
            
            $ipChangeCount = max(0, count($knownIps) - 1);
            
            // Enhanced Context: User Agent and Mock Location (for demo)
            $userAgent = request()->header('User-Agent');
            $deviceFingerprint = substr(md5($userAgent), 0, 16);
            $location = $this->getLocationFromIp($currentIp);

            // Persistent Device Check
            $isPersistentVerified = VerifiedDevice::where('user_id', $user->id)
                ->where('device_fingerprint', $deviceFingerprint)
                ->exists();

            $deviceIsNew = !Session::has('device_verified') && !$isPersistentVerified;
            
            $historicalAvgAmount = Cache::remember("user_{$user->id}_avg_spending", 3600, function() use ($user) {
                return \App\Models\Order::where('customer_id', $user->id)
                    ->whereIn('status', ['completed', 'delivered'])
                    ->avg('total_amount') ?? 0.0;
            });
            
            $payload = [
                'user_id' => $user->id,
                'amount' => $transactionAmount,
                'payment_method' => $paymentMethod,
                'login_time' => $loginTime,
                'ip_change_count' => $ipChangeCount,
                'device_is_new' => $deviceIsNew,
                'historical_avg_amount' => round($historicalAvgAmount, 2),
                'ip_address' => $currentIp,
                'location' => $location,
                'device_fingerprint' => $deviceFingerprint,
            ];
            
            $apiUrl = env('RISK_SCORE_API_URL', 'http://localhost:5000/risk-score');
            
            // Retry Mechanism: 3 attempts, 100ms delay, 3s timeout
            $response = Http::retry(3, 100)
                ->timeout(3)
                ->post($apiUrl, $payload);
            
            if ($response->successful()) {
                $result = $response->json();
                $this->maybeSendRedFlagWarning($user, $result);
                return $result;
            }
            
            Log::error('Risk Scoring API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            // Use local heuristic fallback if API responds with error
            $fallback = $this->estimateLocalRiskWithBreakdown($user, $transactionAmount);
            $fallbackScore = $fallback['score'];
            $result = [
                'risk_score' => $fallbackScore,
                'level' => $this->riskLevel($fallbackScore),
                'suggestion' => $this->suggestionFromScore($fallbackScore),
                'explanation' => [
                    'score_breakdown' => array_merge(
                        ['Risk scoring service returned HTTP error; using local heuristic fallback.'],
                        $fallback['breakdown']
                    ),
                    'input' => $payload,
                ],
            ];

            $this->maybeSendRedFlagWarning($user, $result);

            return $result;
        } catch (\Throwable $e) {
            Log::error('Risk Scoring Exception', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $fallback = $this->estimateLocalRiskWithBreakdown($user, $transactionAmount);
            $fallbackScore = $fallback['score'];
            $result = [
                'risk_score' => $fallbackScore,
                'level' => $this->riskLevel($fallbackScore),
                'suggestion' => $this->suggestionFromScore($fallbackScore),
                'explanation' => [
                    'score_breakdown' => array_merge(
                        ['Exception while calling risk scoring service; using local heuristic fallback.'],
                        $fallback['breakdown']
                    ),
                    'input' => isset($payload) ? $payload : ['amount' => $transactionAmount],
                ],
            ];

            $this->maybeSendRedFlagWarning($user, $result);

            return $result;
        }
    }

    protected function estimateLocalRiskWithBreakdown(User $user, float $amount): array
    {
        // Basic heuristic fallback when AI scoring is unavailable.
        $score = 0;
        $breakdown = [];

        if ($amount > 10000) {
            $score += 60;
            $breakdown[] = 'Very large transaction (> $10,000): +60 risk';
        } elseif ($amount > 5000) {
            $score += 50;
            $breakdown[] = 'Large transaction ($5,000 - $10,000): +50 risk';
        } elseif ($amount > 1000) {
            $score += 30;
            $breakdown[] = 'Elevated transaction ($1,000 - $5,000): +30 risk';
        } elseif ($amount > 0) {
            $breakdown[] = 'Small transaction (<= $1,000): +0 risk';
        }

        // Add some additional risk for new devices (Persistent DB Check)
        $userAgent = request()->header('User-Agent');
        $deviceFingerprint = substr(md5($userAgent), 0, 16);
        
        $isPersistentVerified = VerifiedDevice::where('user_id', $user->id)
            ->where('device_fingerprint', $deviceFingerprint)
            ->exists();

        $deviceIsNew = !Session::has('device_verified') && !$isPersistentVerified;
        
        if ($deviceIsNew) {
            $score += 45;
            $breakdown[] = 'New or untrusted device: +45 risk';
        }

        $historicalAvgAmount = \App\Models\Order::where('customer_id', $user->id)
            ->whereIn('status', ['completed', 'delivered'])
            ->avg('total_amount') ?? 0.0;

        if ($historicalAvgAmount > 0) {
            if ($amount <= $historicalAvgAmount * 1.5) {
                $score -= 25;
                $breakdown[] = 'Amount within normal spending range: -25 risk';
            } elseif ($amount <= $historicalAvgAmount * 2.5) {
                $score -= 10;
                $breakdown[] = 'Amount moderately above normal spending: -10 risk';
            }
        }

        $trustedTotals = \App\Models\Order::where('customer_id', $user->id)
            ->whereIn('status', ['completed', 'delivered'])
            ->selectRaw('COUNT(*) as order_count, COALESCE(SUM(total_amount),0) as total_amount')
            ->first();

        if ($trustedTotals && $trustedTotals->total_amount > 0) {
            $orderCount = (int) $trustedTotals->order_count;
            $totalAmount = (float) $trustedTotals->total_amount;

            if ($totalAmount >= 2000 && $orderCount >= 5) {
                $score -= 30; // Increased trust reduction
                $breakdown[] = 'Highly trusted customer (>= $2,000 across 5+ completed orders): -30 risk';
            } elseif ($totalAmount >= 500 && $orderCount >= 3) {
                $score -= 15; // Increased trust reduction
                $breakdown[] = 'Trusted customer (>= $500 across 3+ completed orders): -15 risk';
            }
        }

        // Learning: Account age trust
        $accountAgeDays = $user->created_at->diffInDays(now());
        if ($accountAgeDays > 30) {
            $score -= 5;
            $breakdown[] = 'Mature account (> 30 days old): -5 risk';
        }
        if ($accountAgeDays > 90) {
            $score -= 5;
            $breakdown[] = 'Established account (> 90 days old): -5 risk';
        }

        if (!$deviceIsNew) {
            $score -= 20; // Increased reward for known devices
            $breakdown[] = 'Known/verified device: -20 risk';
        }

        // Cap between 0..100
        $finalScore = max(0, min(100, $score));

        return [
            'score' => $finalScore,
            'breakdown' => $breakdown,
        ];
    }

    protected function estimateLocalRisk(User $user, float $amount): float
    {
        return $this->estimateLocalRiskWithBreakdown($user, $amount)['score'];
    }

    protected function riskLevel(float $score): string
    {
        if ($score >= 80) {
            return 'critical';
        }
        if ($score >= 65) {
            return 'high';
        }
        if ($score >= 30) {
            return 'medium';
        }
        return 'low';
    }

    protected function suggestionFromScore(float $score): string
    {
        if ($score >= 80) {
            return 'block';
        }
        if ($score >= 65) {
            return 'faceid';
        }
        if ($score >= 30) {
            return 'otp';
        }
        return 'allow';
    }

    public function getLocationFromIp(string $ip): string
    {
        return Cache::remember("ip_location_{$ip}", 86400, function() use ($ip) {
            // If the request is from localhost in a local environment, fetch the public IP.
            if (in_array($ip, ['127.0.0.1', '::1']) && app()->isLocal()) {
                try {
                    $publicIpResponse = Http::timeout(2)->get('https://api.ipify.org');
                    if ($publicIpResponse->successful()) {
                        $ip = trim($publicIpResponse->body());
                    } else {
                        return 'Unknown (Local IP)';
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not fetch public IP for local dev.', ['error' => $e->getMessage()]);
                    return 'Unknown (Local IP)';
                }
            }

            // Proceed with GeoIP lookup using the public IP.
            try {
                $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}");
                if ($response->successful() && $response->json('status') === 'success') {
                    return $response->json('city') . ', ' . $response->json('country');
                }
            } catch (\Exception $e) {
                Log::warning('GeoIP lookup failed', ['ip' => $ip, 'error' => $e->getMessage()]);
            }

            return 'Unknown';
        });
    }

    protected function maybeSendRedFlagWarning(User $user, array $riskResult): void
    {
        $score = $riskResult['risk_score'] ?? 0;

        // Only warn on critical-level scores.
        if ($score < 80) {
            return;
        }

        // Count the number of high-risk audits in the last 30 days
        $redFlagCount = SecurityAudit::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->where('risk_score', '>=', 80)
            ->count();

        $threshold = 3;
        if ($redFlagCount < $threshold) {
            return;
        }

        // Avoid spamming the user more than once per day
        if (Session::has('red_flag_warning_sent_at')) {
            $sentAt = \Carbon\Carbon::parse(Session::get('red_flag_warning_sent_at'));
            if ($sentAt->diffInHours(now()) < 24) {
                return;
            }
        }

        Session::put('red_flag_warning_sent_at', now()->toDateTimeString());

        Session::flash('error', "Multiple high-risk activities were detected on your account. Please contact support to verify your identity.");

        try {
            Mail::to($user->email)->send(new RedFlagWarningMail($user, $redFlagCount));
        } catch (\Exception $e) {
            Log::warning('Failed to send red flag warning email', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }
}
