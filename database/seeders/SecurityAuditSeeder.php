<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SecurityAudit;
use App\Models\User;
use Carbon\Carbon;

class SecurityAuditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get some realistic user IDs (customers)
        $users = User::where('role', 'customer')->pluck('id')->toArray();
        if(empty($users)) {
            // fallback if no real users are seeded
            $users = [1, 2, 3, 4, 5]; 
        }

        $totalRecords = 100;
        $now = Carbon::now();

        for ($i = 0; $i < $totalRecords; $i++) {
            // Randomly scatter dates across the last 7 days
            $randomDate = $now->copy()->subDays(rand(0, 6))->subMinutes(rand(0, 1440));
            $user_id = $users[array_rand($users)];

            // Determine profile (70% Low, 20% Medium, 10% High)
            $chance = rand(1, 100);

            if ($chance <= 70) {
                // Low Risk (Allow Flow) - Casual purchases
                $amount = rand(10, 500) + (rand(0, 99) / 100);
                $score = rand(0, 20) + (rand(0, 99) / 100);
                
                SecurityAudit::create([
                    'user_id' => $user_id,
                    'action' => 'checkout',
                    'amount' => $amount,
                    'risk_score' => $score,
                    'level' => 'low',
                    'suggestion' => 'allow',
                    'result' => 'success',
                    'metadata' => ['ai_enabled' => true, 'note' => 'Standard IP, known device'],
                    'created_at' => $randomDate,
                    'updated_at' => $randomDate
                ]);

            } elseif ($chance <= 90) {
                // Medium Risk (MFA Flow) - Unusually high, new device context
                $amount = rand(500, 3000) + (rand(0, 99) / 100);
                $score = rand(40, 75) + (rand(0, 99) / 100);
                
                // Simulate sometimes they pass OTP, sometimes they abandon/fail
                $result = rand(1, 10) > 3 ? 'success' : 'failed';

                SecurityAudit::create([
                    'user_id' => $user_id,
                    'action' => 'checkout',
                    'amount' => $amount,
                    'risk_score' => $score,
                    'level' => 'medium',
                    'suggestion' => 'mfa',
                    'result' => $result,
                    'metadata' => ['ai_enabled' => true, 'note' => 'New device detected. OTP requested.'],
                    'created_at' => $randomDate,
                    'updated_at' => $randomDate
                ]);

            } else {
                // High Risk (Block Flow) - Impossible logic or extreme value
                $amount = rand(50000, 150000) + (rand(0, 99) / 100);
                $score = rand(85, 100);
                
                SecurityAudit::create([
                    'user_id' => $user_id,
                    'action' => 'checkout',
                    'amount' => $amount,
                    'risk_score' => $score,
                    'level' => 'high',
                    'suggestion' => 'block',
                    'result' => 'blocked',
                    'metadata' => ['ai_enabled' => true, 'note' => 'Velocity check failed. Extreme transaction limits breached.'],
                    'created_at' => $randomDate,
                    'updated_at' => $randomDate
                ]);
            }
        }
    }
}
