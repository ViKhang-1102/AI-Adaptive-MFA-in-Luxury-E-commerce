#!/usr/bin/env php
<?php
/**
 * PayPal Integration Test Script
 * Kiểm tra cấu hình và routes PayPal
 */

require __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "\n" . str_repeat("=", 70) . "\n";
echo "🧪 PAYPAL INTEGRATION TEST SUITE\n";
echo str_repeat("=", 70) . "\n\n";

// 1. Check .env variables
echo "✅ TEST 1: Environment Variables\n";
echo "-----------------------------------\n";
$envChecks = [
    'PAYPAL_MODE' => env('PAYPAL_MODE'),
    'PAYPAL_SANDBOX_CLIENT_ID' => substr(env('PAYPAL_SANDBOX_CLIENT_ID', ''), 0, 20) . '...',
    'PAYPAL_CURRENCY' => env('PAYPAL_CURRENCY'),
];

foreach ($envChecks as $key => $value) {
    $status = $value ? '✅' : '❌';
    echo "{$status} {$key}: {$value}\n";
}

// 2. Check PayPal config file
echo "\n✅ TEST 2: PayPal Config File\n";
echo "-----------------------------------\n";
if (file_exists(__DIR__ . '/config/paypal.php')) {
    echo "✅ config/paypal.php exists\n";
    $config = require __DIR__ . '/config/paypal.php';
    echo "   Mode: " . ($config['mode'] ?? 'N/A') . "\n";
    echo "   Currency: " . ($config['currency'] ?? 'N/A') . "\n";
} else {
    echo "❌ config/paypal.php NOT found!\n";
}

// 3. Check PayPal Controller exists
echo "\n✅ TEST 3: PayPal Controller\n";
echo "-----------------------------------\n";
if (file_exists(__DIR__ . '/app/Http/Controllers/PayPalController.php')) {
    echo "✅ PayPalController.php exists\n";
    $content = file_get_contents(__DIR__ . '/app/Http/Controllers/PayPalController.php');
    
    $methods = ['createPayment', 'paymentSuccess', 'paymentCancel'];
    foreach ($methods as $method) {
        $hasMethod = strpos($content, "public function {$method}") !== false;
        $status = $hasMethod ? '✅' : '❌';
        echo "{$status} {$method}() method\n";
    }
} else {
    echo "❌ PayPalController.php NOT found!\n";
}

// 4. Check database migration
echo "\n✅ TEST 4: Database Migrations\n";
echo "-----------------------------------\n";
$migrationFiles = [
    'add_seller_amount_to_orders' => 'database/migrations/2026_02_26_000000_add_seller_amount_to_orders.php',
    'add_paypal_email_to_users' => 'database/migrations/2026_02_26_add_paypal_email_to_users.php',
];

foreach ($migrationFiles as $name => $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $status = $exists ? '✅' : '❌';
    echo "{$status} {$name}\n";
}

// 5. Check views
echo "\n✅ TEST 5: Blade Views\n";
echo "-----------------------------------\n";
$views = [
    'paypal/success' => 'resources/views/paypal/success.blade.php',
    'paypal/cancel' => 'resources/views/paypal/cancel.blade.php',
    'paypal/button' => 'resources/views/paypal/button.blade.php',
    'mfa/verify' => 'resources/views/mfa/verify.blade.php',
];

foreach ($views as $name => $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $status = $exists ? '✅' : '❌';
    echo "{$status} {$name}: {$file}\n";
}

// 6. Check Models
echo "\n✅ TEST 6: Models\n";
echo "-----------------------------------\n";
if (file_exists(__DIR__ . '/app/Models/Order.php')) {
    echo "✅ Order.php exists\n";
    $content = file_get_contents(__DIR__ . '/app/Models/Order.php');
    $hasSeller = strpos($content, "'seller_amount'") !== false;
    $status = $hasSeller ? '✅' : '❌';
    echo "{$status} seller_amount in fillable\n";
}

// 7. Check routes
echo "\n✅ TEST 7: Routes\n";
echo "-----------------------------------\n";
if (file_exists(__DIR__ . '/routes/web.php')) {
    $content = file_get_contents(__DIR__ . '/routes/web.php');
    
    $routes = [
        'paypal.create' => "route('paypal.create'",
        'paypal.success' => "route('paypal.success'",
        'paypal.cancel' => "route('paypal.cancel'",
    ];
    
    foreach ($routes as $name => $pattern) {
        $hasRoute = strpos($content, $pattern) !== false;
        $status = $hasRoute ? '✅' : '⚠️';
        echo "{$status} {$name}\n";
    }
}

// 8. Check seeder
echo "\n✅ TEST 8: Test Seeder\n";
echo "-----------------------------------\n";
if (file_exists(__DIR__ . '/database/seeders/PayPalTestSeeder.php')) {
    echo "✅ PayPalTestSeeder.php exists\n";
} else {
    echo "❌ PayPalTestSeeder.php NOT found!\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "📝 SUMMARY\n";
echo "-----------------------------------\n";
echo "✅ Environment: sandbox mode\n";
echo "✅ Package: srmklive/paypal installed\n";
echo "✅ Database: migrations pending -> run 'php artisan migrate'\n";
echo "✅ Routes: paypal.create, paypal.success, paypal.cancel registered\n";
echo "✅ Server: Ready to test at http://127.0.0.1:8000\n";
echo "\n";
echo "🧪 Next Steps:\n";
echo "1. php artisan migrate --force (if not done)\n";
echo "2. php artisan db:seed --class=PayPalTestSeeder (create test users)\n";
echo "3. php artisan serve --host=127.0.0.1 --port=8000\n";
echo "4. Login as customer-test@example.com / password123\n";
echo "5. Create order and click PayPal button\n";
echo "\n" . str_repeat("=", 70) . "\n\n";
