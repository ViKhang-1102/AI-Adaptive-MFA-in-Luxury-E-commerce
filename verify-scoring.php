#!/usr/bin/env php
<?php
/**
 * Risk Scoring Test Script
 * 
 * Validates the new tiered risk scoring system against expected scenarios
 * Usage: php verify-scoring.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Models\SecurityAudit;
use App\Models\VerifiedDevice;
use App\Services\RiskAssessmentService;

echo "\n" . str_repeat("=", 80) . "\n";
echo "RISK SCORING SYSTEM - VERIFICATION TESTS (v2.0 Tiered)\n";
echo str_repeat("=", 80) . "\n\n";

$riskService = new RiskAssessmentService();

// Test scenarios
$scenarios = [
    [
        'name' => 'Scenario A: New Account, $142 Purchase (Previous Blocker)',
        'setup' => function() {
            $user = User::create([
                'name' => 'New User A',
                'email' => 'user_a_' . uniqid() . '@example.com',
                'password' => bcrypt('password'),
                'role' => 'customer',
                'created_at' => now()->subHours(2),
            ]);
            return [$user, 142, 'online'];
        },
        'expected_min' => 30,
        'expected_max' => 50,
        'expected_level' => 'MEDIUM',
        'notes' => 'Previously scored 100 (CRITICAL/Blocked). Now should be MEDIUM/OTP'
    ],
    [
        'name' => 'Scenario B: New Account, $5000 Purchase (Should Block)',
        'setup' => function() {
            $user = User::create([
                'name' => 'New User B',
                'email' => 'user_b_' . uniqid() . '@example.com',
                'password' => bcrypt('password'),
                'role' => 'customer',
                'created_at' => now()->subHours(1),
            ]);
            return [$user, 5000, 'online'];
        },
        'expected_min' => 85,
        'expected_max' => 100,
        'expected_level' => 'CRITICAL',
        'notes' => 'New account with large transaction should still trigger investigation'
    ],
    [
        'name' => 'Scenario C: Established Customer, $500 Purchase',
        'setup' => function() {
            $user = User::create([
                'name' => 'Established User C',
                'email' => 'user_c_' . uniqid() . '@example.com',
                'password' => bcrypt('password'),
                'role' => 'customer',
                'created_at' => now()->subDays(90),
            ]);
            
            // Create purchase history
            for ($i = 0; $i < 8; $i++) {
                Order::create([
                    'order_number' => 'ORD-' . strtoupper(uniqid()),
                    'customer_id' => $user->id,
                    'seller_id' => 1, // assume seller 1 exists
                    'status' => 'delivered',
                    'total_amount' => 450,
                    'created_at' => now()->subDays(rand(7, 89)),
                    'subtotal' => 450,
                    'shipping_fee' => 0,
                    'payment_method' => 'online',
                    'payment_status' => 'paid',
                    'recipient_name' => $user->name,
                    'recipient_phone' => '0123456789',
                    'delivery_address' => '123 Test St',
                ]);
            }
            
            return [$user, 500, 'online'];
        },
        'expected_min' => 0,
        'expected_max' => 35,
        'expected_level' => 'LOW',
        'notes' => 'Loyal customer with normal purchasing pattern'
    ],
    [
        'name' => 'Scenario D: VIP Customer (10k+ lifetime), $3000 Purchase',
        'setup' => function() {
            $user = User::create([
                'name' => 'VIP User D',
                'email' => 'user_d_' . uniqid() . '@example.com',
                'password' => bcrypt('password'),
                'role' => 'customer',
                'created_at' => now()->subDays(365),
            ]);
            
            // Create significant purchase history
            for ($i = 0; $i < 25; $i++) {
                Order::create([
                    'order_number' => 'ORD-VIP-' . strtoupper(uniqid()),
                    'customer_id' => $user->id,
                    'seller_id' => 1,
                    'status' => 'delivered',
                    'total_amount' => 450,
                    'created_at' => now()->subDays(rand(30, 350)),
                    'subtotal' => 450,
                    'shipping_fee' => 0,
                    'payment_method' => 'online',
                    'payment_status' => 'paid',
                    'recipient_name' => $user->name,
                    'recipient_phone' => '0123456789',
                    'delivery_address' => '123 Test St',
                ]);
            }
            VerifiedDevice::create([
                'user_id' => $user->id,
                'device_fingerprint' => substr(md5('test-device'), 0, 16),
                'ip_address' => '127.0.0.1',
                'last_used_at' => now(),
            ]);
            
            return [$user, 3000, 'online'];
        },
        'expected_min' => 0,
        'expected_max' => 20,
        'expected_level' => 'LOW',
        'notes' => 'VIP customers should rarely be blocked'
    ],
    [
        'name' => 'Scenario E: Fraud Pattern - 5 Orders in 30 Min (Micro)',
        'setup' => function() {
            $user = User::create([
                'name' => 'Fraud User E',
                'email' => 'user_e_' . uniqid() . '@example.com',
                'password' => bcrypt('password'),
                'role' => 'customer',
                'created_at' => now()->subHours(1),
            ]);
            
            // Create 5 rapid orders (simulating fraud test)
            for ($i = 0; $i < 5; $i++) {
                Order::create([
                    'order_number' => 'ORD-FR-' . strtoupper(uniqid()),
                    'customer_id' => $user->id,
                    'seller_id' => 1,
                    'status' => 'pending',
                    'total_amount' => 10,
                    'created_at' => now()->subMinutes(1),
                    'subtotal' => 10,
                    'shipping_fee' => 0,
                    'payment_method' => 'online',
                    'recipient_name' => $user->name,
                    'recipient_phone' => '0123456789',
                    'delivery_address' => '123 Test St',
                ]);
                SecurityAudit::create([
                    'user_id' => $user->id,
                    'action' => 'checkout',
                    'amount' => 10,
                    'risk_score' => 10,
                    'level' => 'low',
                    'suggestion' => 'allow',
                    'created_at' => now()->subSeconds($i * 2),
                ]);
            }
            
            return [$user, 15, 'online'];
        },
        'expected_min' => 50,
        'expected_max' => 100,
        'expected_level' => 'HIGH/CRITICAL',
        'notes' => 'Suspicious velocity pattern should be detected'
    ],
];

// Helper function to get risk level
function getRiskLevel($score) {
    if ($score < 30) return 'LOW';
    if ($score < 65) return 'MEDIUM';
    if ($score < 85) return 'HIGH';
    return 'CRITICAL';
}

// Run tests
$passed = 0;
$failed = 0;

foreach ($scenarios as $scenario) {
    echo "TEST: {$scenario['name']}\n";
    echo str_repeat("-", 80) . "\n";
    
    try {
        // Setup test data
        [$user, $amount, $paymentMethod] = $scenario['setup']();
        
        // Calculate risk score
        $result = $riskService->estimateLocalRiskWithBreakdown($user, $amount, $paymentMethod);
        $score = $result['score'];
        $breakdown = $result['breakdown'];
        $level = getRiskLevel($score);
        
        // Display results
        echo "Amount: \${$amount} | Payment: {$paymentMethod} | Score: {$score}/100 | Level: {$level}\n\n";
        echo "Score Breakdown:\n";
        foreach ($breakdown as $line) {
            echo "  • {$line}\n";
        }
        echo "\n";
        
        // Validate expectations
        $scoreOk = $score >= $scenario['expected_min'] && $score <= $scenario['expected_max'];
        $levelOk = strpos($scenario['expected_level'], $level) !== false;
        
        if ($scoreOk && $levelOk) {
            echo "✅ PASS\n";
            echo "   Expected: {$scenario['expected_min']}-{$scenario['expected_max']} ({$scenario['expected_level']})\n";
            echo "   Got:      {$score} ({$level})\n";
            $passed++;
        } else {
            echo "❌ FAIL\n";
            echo "   Expected: {$scenario['expected_min']}-{$scenario['expected_max']} ({$scenario['expected_level']})\n";
            echo "   Got:      {$score} ({$level})\n";
            $failed++;
        }
        
        echo "Note: {$scenario['notes']}\n";
        
        // Cleanup
        Order::where('customer_id', $user->id)->delete();
        SecurityAudit::where('user_id', $user->id)->delete();
        VerifiedDevice::where('user_id', $user->id)->delete();
        $user->delete();
        
    } catch (\Exception $e) {
        echo "❌ ERROR: {$e->getMessage()}\n";
        $failed++;
    }
    
    echo "\n" . str_repeat("=", 80) . "\n\n";
}

// Summary
echo "SUMMARY\n";
echo str_repeat("-", 80) . "\n";
$scenarioCount = count($scenarios);
echo "Passed: {$passed}/{$scenarioCount}\n";
echo "Failed: {$failed}/{$scenarioCount}\n";

if ($failed === 0) {
    echo "\n✅ All tests passed! Scoring system is working correctly.\n";
    exit(0);
} else {
    echo "\n⚠️  Some tests failed. Review scoring logic.\n";
    exit(1);
}
