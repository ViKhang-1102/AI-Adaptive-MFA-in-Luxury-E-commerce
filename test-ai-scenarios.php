<?php

use App\Models\User;
use App\Services\RiskAssessmentService;
use App\Services\FaceVerificationService;
use Illuminate\Support\Facades\Storage;

// 1. Mock a high-risk scenario
echo "--- Scenario 1: Testing High Risk Scoring ---\n";
$user = User::where('email', 'customer1@example.com')->first();
$riskService = app(RiskAssessmentService::class);
$result = $riskService->analyze($user, 5000.0, 'cod');
echo "Risk Score: " . ($result['risk_score'] ?? 'N/A') . "\n";
echo "Level: " . ($result['level'] ?? 'N/A') . "\n";
echo "Suggestion: " . ($result['suggestion'] ?? 'N/A') . "\n";

// 2. Check if identity_image is null (should be after my reset)
echo "\n--- Scenario 2: Checking FaceID Data ---\n";
echo "Current Identity Image Path: " . ($user->identity_image ?? 'NULL (Ready for Enrollment)') . "\n";

// 3. Mock FaceID Enrollment
echo "\n--- Scenario 3: Testing Enrollment Logic ---\n";
if (!$user->identity_image) {
    echo "User has no FaceID. System will enroll on next scan.\n";
}

// 4. Mock FaceID Comparison (Verification)
echo "\n--- Scenario 4: Testing AI Comparison Logic ---\n";
if ($user->identity_image && Storage::disk('public')->exists($user->identity_image)) {
    echo "Performing AI Face Comparison test...\n";
    // We can't easily mock a real base64 image here for OpenAI Vision, 
    // but we can check if the Service is ready.
    $faceService = app(FaceVerificationService::class);
    echo "FaceVerificationService is instantiated.\n";
} else {
    echo "No identity image to compare against. Please perform a real scan in the browser first to 'Enroll'.\n";
}
