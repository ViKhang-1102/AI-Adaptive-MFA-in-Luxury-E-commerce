<?php
// Simple setup script - load via: php artisan tinker
// Then: @include 'setup-payment.php'

use App\Models\User;
use App\Models\SystemFee;

echo "\n=== Configuring Payment System ===\n";

// Setup seller PayPal emails
$sellers = User::where('role', 'seller')->get();
foreach ($sellers as $seller) {
    if (!$seller->paypal_email) {
        $seller->update([
            'paypal_email' => 'sb-seller' . $seller->id . '@personal.example.com'
        ]);
        echo "✓ Seller: {$seller->name}\n   Email: {$seller->paypal_email}\n";
    } else {
        echo "✓ Seller: {$seller->name}\n   Email: {$seller->paypal_email} (already set)\n";
    }
}

// Initialize commission
$commission = SystemFee::firstOrCreate(
    ['is_platform_commission' => true],
    [
        'name' => 'Platform Commission',
        'fee_type' => 'percentage',
        'fee_value' => 10,
        'description' => 'Admin commission (configurable in admin/fees)'
    ]
);
echo "\n✓ Commission Configuration:\n   Value: {$commission->fee_value}%\n   Admin receives: {$commission->fee_value}%\n   Seller receives: " . (100 - $commission->fee_value) . "%\n";

echo "\n✅ Setup Complete!\n";
echo "Ready to test PayPal marketplace.\n\n";
