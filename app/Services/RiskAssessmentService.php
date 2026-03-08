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
    public function analyze(User $user, float $amount): ?array
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
            
            return null;

        } catch (\Exception $e) {
            Log::error('Risk Scoring API Connection Failed', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }
}
