<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\RiskAssessmentService;

echo "Running AI risk scenario samples...\n";

$user = User::where('role', 'customer')->first();
if (!$user) {
    echo "No customer found.\n";
    exit;
}

$service = app(RiskAssessmentService::class);

$amounts = [100, 800, 2000, 8000, 15000];

foreach ($amounts as $amt) {
    $result = $service->analyze($user, $amt, 'cod');
    echo "Amount {$amt}: score={$result['risk_score']} level={$result['level']} suggestion={$result['suggestion']}\n";
}

echo "AI scenario test finished.\n";
