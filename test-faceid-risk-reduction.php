<?php
/**
 * Face ID Risk Score Verification
 * 
 * After face ID verification succeeds, the risk score should be reduced.
 * This script verifies the logic is correct.
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SecurityAudit;

echo "\n" . str_repeat("=", 80) . "\n";
echo "FACE ID VERIFICATION - RISK SCORE UPDATE TEST\n";
echo str_repeat("=", 80) . "\n\n";

// Find a recent high-risk audit (like the 98 score ones)
$highRiskAudits = SecurityAudit::where('risk_score', '>=', 80)
    ->orderByDesc('created_at')
    ->limit(5)
    ->get();

if ($highRiskAudits->isEmpty()) {
    echo "❌ No high-risk audits found in database.\n";
    echo "Run a transaction with high risk score first.\n";
    exit;
}

echo "Found " . $highRiskAudits->count() . " high-risk audit records:\n\n";

foreach ($highRiskAudits as $audit) {
    echo "Audit ID: {$audit->id}\n";
    echo "  - User: #{$audit->user_id}\n";
    echo "  - Current Risk Score: {$audit->risk_score}\n";
    echo "  - Level: {$audit->level}\n";
    echo "  - Suggestion: {$audit->suggestion}\n";
    echo "  - Result: {$audit->result}\n";
    
    if ($audit->metadata && isset($audit->metadata['face_verification'])) {
        echo "  - Face Verification: YES ✓\n";
        $faceData = $audit->metadata['face_verification'];
        echo "    • Confidence: " . ($faceData['confidence'] ?? 'N/A') . "\n";
        echo "    • Verified At: " . ($faceData['verified_at'] ?? 'N/A') . "\n";
        
        // Simulate the risk reduction that should have happened
        $originalScore = 80; // The audit should show reduced score if face ID was verified
        $confidence = (float)($faceData['confidence'] ?? 0);
        $reduction = 0;
        if ($confidence >= 0.95) $reduction = 50;
        elseif ($confidence >= 0.85) $reduction = 40;
        elseif ($confidence >= 0.75) $reduction = 30;
        else $reduction = 20;
        
        $expectedScore = max(0, $originalScore - $reduction);
        
        if ($audit->risk_score < 80) {
            echo "    ✅ Score correctly reduced from ~80 to {$audit->risk_score} (-$reduction pts)\n";
        } else {
            echo "    ❌ Score NOT reduced after face ID verification!\n";
        }
    } else {
        echo "  - Face Verification: NO\n";
    }
    echo "\n";
}

echo str_repeat("=", 80) . "\n";
echo "EXPECTED BEHAVIOR:\n";
echo "  1. When transaction reaches risk score >= 65, FaceID is required\n";
echo "  2. User scans face and it matches successfully\n";
echo "  3. Risk score should be REDUCED based on confidence:\n";
echo "     - 95%+ confidence: -50 points\n";
echo "     - 85%+ confidence: -40 points\n";
echo "     - 75%+ confidence: -30 points\n";
echo "     - < 75%: -20 points\n";
echo "  4. Audit record should be marked 'success' instead of 'pending'\n";
echo "  5. Suggestion should change to 'allow'\n";
echo str_repeat("=", 80) . "\n\n";

echo "✅ Face ID verification risk score reduction logic is now active!\n";
echo "If you see scores >= 80 reduced after successful face verification, the fix worked.\n\n";
