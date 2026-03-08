<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
            $deviceIsNew = !Session::has('device_verified');
            
            // Enhanced Context: User Agent and Mock Location (for demo)
            $userAgent = request()->header('User-Agent');
            $location = "Hanoi, Vietnam"; // In real app, use GeoIP library

            $historicalAvgAmount = \App\Models\Order::where('customer_id', $user->id)
                ->whereIn('status', ['completed', 'delivered'])
                ->avg('total_amount') ?? 0.0;
            
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
                'device_fingerprint' => substr(md5($userAgent), 0, 16),
            ];
            
            $apiUrl = env('RISK_SCORE_API_URL', 'http://localhost:5000/risk-score');
            
            // Retry Mechanism: 3 attempts, 100ms delay, 3s timeout
            $response = Http::retry(3, 100)
                ->timeout(3)
                ->post($apiUrl, $payload);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('Risk Scoring API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            // Use local heuristic fallback if API responds with error
            $fallbackScore = $this->estimateLocalRisk($user, $transactionAmount);
            return [
                'risk_score' => $fallbackScore,
                'level' => $this->riskLevel($fallbackScore),
                'suggestion' => $this->suggestionFromScore($fallbackScore),
                'explanation' => [
                    'score_breakdown' => ['Risk scoring service returned HTTP error; using local heuristic fallback.'],
                    'input' => ['amount' => $transactionAmount],
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Risk Scoring Exception', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $fallbackScore = $this->estimateLocalRisk($user, $transactionAmount);
            return [
                'risk_score' => $fallbackScore,
                'level' => $this->riskLevel($fallbackScore),
                'suggestion' => $this->suggestionFromScore($fallbackScore),
                'explanation' => [
                    'score_breakdown' => ['Exception while calling risk scoring service; using local heuristic fallback.'],
                    'input' => ['amount' => $transactionAmount],
                ],
            ];
        }
    }

    protected function estimateLocalRisk(User $user, float $amount): float
    {
        // Basic heuristic fallback when AI scoring is unavailable.
        $score = 0;

        if ($amount > 10000) {
            $score += 60;
        } elseif ($amount > 5000) {
            $score += 50;
        } elseif ($amount > 1000) {
            $score += 30;
        }

        // Add some additional risk for new devices
        $deviceIsNew = !Session::has('device_verified');
        if ($deviceIsNew) {
            $score += 45;
        }

        // Cap between 0..100
        return max(0, min(100, $score));
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
}
