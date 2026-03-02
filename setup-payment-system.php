#!/usr/bin/env php
<?php

// Bootstrap the application and retrieve the application instance
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->handle(
    $input = new \Symfony\Component\Console\Input\ArgvInput,
    new \Symfony\Component\Console\Output\ConsoleOutput
);

use App\Models\User;
use App\Models\SystemFee;

echo "\n=== Setting Up Payment System ===\n";

// Setup seller PayPal emails
$sellers = User::where('role', 'seller')->get();
foreach ($sellers as $seller) {
    if (!$seller->paypal_email) {
        $seller->update([
            'paypal_email' => 'sb-seller' . $seller->id . '@personal.example.com'
        ]);
        echo "✓ Seller {$seller->name} PayPal email set to: {$seller->paypal_email}\n";
    }
}

// Initialize commission configuration
$commission = SystemFee::firstOrCreate(
    ['is_platform_commission' => true],
    [
        'name' => 'Platform Commission',
        'fee_type' => 'percentage',
        'fee_value' => 10,
        'description' => '10% admin commission - Adjust in admin/fees'
    ]
);
echo "✓ Commission configured: {$commission->fee_value}%\n";

echo "\n=== Setup Complete! ===\n";
echo "Ready to test PayPal marketplace payments.\n";
echo "Start Laravel: php artisan serve\n\n";

$kernel->terminate($input, $status);
