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
    public function analyze(User $user, float $amount, string $paymentMethod = 'unknown', ?float $lat = null, ?float $lng = null): ?array
    {
        $transactionAmount = round($amount, 2);

        // Immediate trust for Admin users to prevent false positives in the dashboard
        if ($user->isAdmin()) {
            return [
                'risk_score' => 0,
                'level' => 'low',
                'suggestion' => 'allow',
                'explanation' => [
                    'score_breakdown' => ["🛡️ Administrative account: Trusted system actor (0 pts)"],
                    'input' => ['amount' => $transactionAmount, 'role' => 'admin'],
                ],
            ];
        }

        // STATIC MODE: If Adaptive AI Engine is disabled, enforce OTP for everyone
        $aiEnabled = env('ENABLE_AI_MFA', true);
        if (!$aiEnabled) {
            return [
                'risk_score' => 45.0, // Fixed medium risk
                'level' => 'medium',
                'suggestion' => 'otp',
                'explanation' => [
                    'score_breakdown' => [
                        "⚠️ Adaptive AI Engine is DISABLED (Static Mode).",
                        "🔒 System policy: Mandatory OTP verification for all transactions.",
                        "ℹ️ Learning and behavioral patterns are ignored in this mode."
                    ],
                    'input' => ['amount' => $transactionAmount, 'ai_enabled' => false],
                ],
            ];
        }

        try {
            $loginTime = now()->toIso8601String(); 
            
            // Collect context for Guard Agent
            $knownIps = Session::get('user_known_ips', []);
            $currentIp = request()->ip() ?? '127.0.0.1';
            
            if (!in_array($currentIp, $knownIps)) {
                $knownIps[] = $currentIp;
                Session::put('user_known_ips', $knownIps);
            }
            
            $ipChangeCount = max(0, count($knownIps) - 1);
            
            // Enhanced Context: User Agent and Precise Location
            $userAgent = request()->header('User-Agent');
            $deviceFingerprint = substr(md5($userAgent), 0, 16);
            
            // Prioritize GPS coordinates over IP geolocation
            $location = ($lat && $lng) 
                ? $this->getLocationFromCoords($lat, $lng) 
                : $this->getLocationFromIp($currentIp);

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
            
            // Increased timeout for better AI processing reliability
            $response = Http::timeout(3.0)
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
            $fallback = $this->estimateLocalRiskWithBreakdown($user, $transactionAmount, $paymentMethod);
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

            $fallback = $this->estimateLocalRiskWithBreakdown($user, $transactionAmount, $paymentMethod);
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

    public function estimateLocalRiskWithBreakdown(User $user, float $amount, string $paymentMethod = 'unknown'): array
    {
        // Admin accounts have zero risk by default as they are trusted system actors
        if ($user->isAdmin()) {
            return [
                'score' => 0,
                'breakdown' => ["🛡️ Administrative account: Trusted system actor (0 pts)"],
            ];
        }

        // Tier-based risk scoring system for balanced evaluation
        $score = 0;
        $breakdown = [];
        $accountAgeHours = $user->created_at->diffInHours(now());
        $accountAgeDays = $user->created_at->diffInDays(now());

        // === TIER DEFINITIONS (Exclusive ranges to prevent overlap) ===
        $isMicro = $amount <= 100;
        $isSmall = $amount > 100 && $amount <= 500;
        $isMedium = $amount > 500 && $amount <= 2000;
        $isLarge = $amount > 2000 && $amount <= 10000;
        $isVeryLarge = $amount > 10000;

        // ===== 1. TRANSACTION AMOUNT (Base Risk) =====
        if ($isVeryLarge) {
            $score += 60;
            $breakdown[] = "🔴 Very large transaction (>$10,000): +60 pts";
        } elseif ($isLarge) {
            $score += 40;
            $breakdown[] = "🟠 Large transaction ($2,000-$10,000): +40 pts";
        } elseif ($isMedium) {
            $score += 20;
            $breakdown[] = "🟡 Medium transaction ($500-$2,000): +20 pts";
        } elseif ($isSmall) {
            $score += 5;
            $breakdown[] = "🟢 Small transaction ($100-$500): +5 pts";
        } else {
            // $amount <= 100
            $breakdown[] = $amount > 0 
                ? "🟢 Micro transaction (<$100): +0 pts" 
                : "ℹ️ Non-transactional activity ($0): +0 pts";
        }

        // ===== 2. PAYMENT METHOD (Baseline Fraud Risk) =====
        $isCod = $paymentMethod === 'cod' || $paymentMethod === 'cash_on_delivery' || $paymentMethod === 'offline';
        
        if ($paymentMethod === 'online' || $paymentMethod === 'paypal') {
            // Adjust based on transaction tier
            if ($isVeryLarge || $isLarge) {
                $score += 15;
                $breakdown[] = "Digital payment (high-value): +15 pts";
            } elseif ($isMedium) {
                $score += 10;
                $breakdown[] = "Digital payment (medium-value): +10 pts";
            } else {
                $score += 5;
                $breakdown[] = "Digital payment (low-value): +5 pts";
            }
        } elseif ($isCod) {
            // COD is inherently safer for the platform as no digital funds are at risk initially
            if ($isMicro || $isSmall || $isMedium) {
                // Major bonus to ensure small/medium COD orders skip MFA
                $score -= 40;
                $breakdown[] = "✅ Cash on Delivery (Safe Tier): -40 pts (major bonus)";
            } else {
                $breakdown[] = "Cash on Delivery (High Value): +0 pts";
            }
        } else {
            $breakdown[] = "Unknown payment method: +0 pts";
        }

        // ===== 3. ACCOUNT AGE (Trust Signal) =====
        if ($accountAgeHours < 1) {
            $basePenalty = $isSmall ? 20 : 30;
            $score += $basePenalty;
            $breakdown[] = "⏰ Brand new account (<1 hour): +{$basePenalty} pts";
        } elseif ($accountAgeHours < 24) {
            // Tier-aware new account penalty
            if ($isVeryLarge) {
                $score += 35;
                $breakdown[] = "⏰ New account (<24h) + very large amount: +35 pts";
            } elseif ($isLarge) {
                $score += 28;
                $breakdown[] = "⏰ New account (<24h) + large amount: +28 pts";
            } elseif ($isMedium) {
                $score += 20;
                $breakdown[] = "⏰ New account (<24h) + medium amount: +20 pts";
            } elseif ($isSmall) {
                $score += 12;
                $breakdown[] = "⏰ New account (<24h) + small amount: +12 pts";
            } else {
                $score += 8;
                $breakdown[] = "⏰ New account (<24h) + micro amount: +8 pts";
            }
        } elseif ($accountAgeDays < 7) {
            $score += 10;
            $breakdown[] = "⏰ New account (1-7 days): +10 pts";
        } elseif ($accountAgeDays > 30) {
            $score -= 5;
            $breakdown[] = "✅ Established account (>30 days): -5 pts (bonus)";
        }

        // ===== 4. ACTIVITY VELOCITY (Abuse Pattern Detection) =====
        // We exclude successful MFA verifications from velocity to avoid punishing legitimate users
        $recentAuditCount = SecurityAudit::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->where('result', '<>', 'success') 
            ->count();
        
        if ($recentAuditCount >= 100) {
            $score += 50;
            $breakdown[] = "🚨 Extreme activity velocity (100+ actions/hour): +50 pts";
        } elseif ($recentAuditCount >= 75) {
            $score += 35;
            $breakdown[] = "⚠️ Critical velocity (75+ actions/hour): +35 pts";
        } elseif ($recentAuditCount >= 50) { // Increased from 7 to 50 for video recording
            $score += 18;
            $breakdown[] = "⚠️ High velocity (50+ failed/pending actions/hour): +18 pts";
        }

        // ===== 4b. MFA TRUST BONUS =====
        // If the user just passed an MFA check in this session, give a massive trust bonus
        if (Session::get('mfa_verified') === true) {
            $score -= 50;
            $breakdown[] = "🛡️ Identity recently verified (MFA): -50 pts (major trust)";
        }

        // ===== 5. DAILY ORDER PATTERN (Spree Detection) =====
        $dailyOrderCount = \App\Models\Order::where('customer_id', $user->id)
            ->where('created_at', '>=', now()->subDay())
            ->count();
        
        if ($dailyOrderCount >= 8) {
            $score += 40;
            $breakdown[] = "📦 Extreme order spree (8+ orders/24h): +40 pts";
        } elseif ($dailyOrderCount >= 5) {
            $score += 25;
            $breakdown[] = "📦 High order spree (5+ orders/24h): +25 pts";
        } elseif ($dailyOrderCount >= 3) {
            $score += 12;
            $breakdown[] = "📦 Multiple orders today (3+ orders): +12 pts";
        }

        // ===== 6. DEVICE FINGERPRINT & TRUST =====
        $userAgent = request()->header('User-Agent');
        $deviceFingerprint = substr(md5($userAgent), 0, 16);
        
        $isPersistentVerified = VerifiedDevice::where('user_id', $user->id)
            ->where('device_fingerprint', $deviceFingerprint)
            ->exists();

        $deviceIsNew = !Session::has('device_verified') && !$isPersistentVerified;
        
        if ($deviceIsNew) {
            if ($isVeryLarge || $isLarge) {
                $score += 40;
                $breakdown[] = "🖥️ New device + high-value transaction: +40 pts";
            } elseif ($isMedium) {
                $score += 25;
                $breakdown[] = "🖥️ New device + medium transaction: +25 pts";
            } elseif ($isSmall) {
                $score += 15;
                $breakdown[] = "🖥️ New device + small transaction: +15 pts";
            } else {
                $score += 8;
                $breakdown[] = "🖥️ New device + micro transaction: +8 pts";
            }
        } else {
            // Trusted device reward (scaled by account age)
            $deviceBonus = ($accountAgeHours < 24) ? 5 : 15;
            $score -= $deviceBonus;
            $breakdown[] = "✅ Verified device: -{$deviceBonus} pts (bonus)";
        }

        // ===== 7. SPENDING PATTERN CONSISTENCY =====
        $historicalAvgAmount = \App\Models\Order::where('customer_id', $user->id)
            ->whereIn('status', ['completed', 'delivered'])
            ->avg('total_amount') ?? 0.0;

        if ($historicalAvgAmount > 0) {
            if ($amount <= $historicalAvgAmount * 1.2) {
                $score -= 12;
                $breakdown[] = "📊 Within normal spending range (±20%): -12 pts (bonus)";
            } elseif ($amount <= $historicalAvgAmount * 1.5) {
                $score -= 5;
                $breakdown[] = "📊 Slightly above typical spending: -5 pts";
            } elseif ($amount > $historicalAvgAmount * 3) {
                $score += 15;
                $breakdown[] = "📊 Significantly above typical (3x+): +15 pts";
            }
        }

        // ===== 8. LONG-TERM TRUST REWARDS (Loyalty) =====
        $trustedTotals = \App\Models\Order::where('customer_id', $user->id)
            ->whereIn('status', ['completed', 'delivered'])
            ->selectRaw('COUNT(*) as order_count, COALESCE(SUM(total_amount),0) as total_amount')
            ->first();

        if ($trustedTotals && $trustedTotals->total_amount > 0) {
            $orderCount = (int) $trustedTotals->order_count;
            $totalAmount = (float) $trustedTotals->total_amount;

            if ($totalAmount >= 10000 && $orderCount >= 20) {
                $score -= 40;
                $breakdown[] = "⭐ VIP customer ($10k+ / 20+ orders): -40 pts (major bonus)";
            } elseif ($totalAmount >= 5000 && $orderCount >= 10) {
                $score -= 30;
                $breakdown[] = "⭐ Elite customer ($5k+ / 10+ orders): -30 pts";
            } elseif ($totalAmount >= 2000 && $orderCount >= 5) {
                $score -= 20;
                $breakdown[] = "⭐ Trusted customer ($2k+ / 5+ orders): -20 pts";
            } elseif ($totalAmount >= 500 && $orderCount >= 2) {
                $score -= 10;
                $breakdown[] = "⭐ Regular customer ($500+ / 2+ orders): -10 pts";
            }
        }

        // ===== FINAL SCORE CALCULATION =====
        $finalScore = max(0, min(100, $score));

        return [
            'score' => $finalScore,
            'breakdown' => $breakdown,
        ];
    }

    protected function estimateLocalRisk(User $user, float $amount, string $paymentMethod = 'unknown'): float
    {
        return $this->estimateLocalRiskWithBreakdown($user, $amount, $paymentMethod)['score'];
    }

    protected function riskLevel(float $score): string
    {
        if ($score >= 85) {
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
        if ($score >= 85) {
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

    public function getLocationFromCoords(float $lat, float $lng): string
    {
        // Add a small delay for demo/visual consistency if needed, but not here
        return Cache::remember("coords_location_{$lat}_{$lng}", 86400, function() use ($lat, $lng) {
            try {
                $response = Http::withHeaders([
                        'User-Agent' => 'LuxGuard/1.0',
                        'Accept-Language' => 'en' // Force English results
                    ])
                    ->timeout(5)
                    ->get("https://nominatim.openstreetmap.org/reverse", [
                        'lat' => $lat,
                        'lon' => $lng,
                        'format' => 'json',
                        'addressdetails' => 1,
                        'zoom' => 10,
                        'accept-language' => 'en' // Also as parameter for extra certainty
                    ]);

                if ($response->successful()) {
                    $address = $response->json('address');
                    // Look for city, town, village, or suburb
                    $city = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['suburb'] ?? $address['county'] ?? 'Unknown City';
                    $country = $address['country'] ?? 'Unknown Country';
                    
                    // Specific check for Vietnam cities to ensure they are capitalized correctly in English
                    if (str_contains(strtolower($city), 'can tho')) $city = 'Can Tho';
                    if (str_contains(strtolower($city), 'ho chi minh') || str_contains(strtolower($city), 'saigon')) $city = 'Ho Chi Minh City';
                    if (str_contains(strtolower($city), 'ha noi')) $city = 'Hanoi';
                    if (str_contains(strtolower($city), 'da nang')) $city = 'Da Nang';
                    if (str_contains(strtolower($city), 'hai phong')) $city = 'Haiphong';

                    return $city . ', ' . $country;
                }
            } catch (\Exception $e) {
                Log::warning('Reverse Geocoding failed', ['lat' => $lat, 'lng' => $lng, 'error' => $e->getMessage()]);
            }
            return 'Unknown (GPS Fallback)';
        });
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
