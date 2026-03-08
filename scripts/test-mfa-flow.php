<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;

// Ensure storage disk exists for identity images
$dir = storage_path('app/public/identities');
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$sourceFace = __DIR__ . '/../test-face.png';
if (!file_exists($dir . '/test-face.png') && file_exists($sourceFace)) {
    copy($sourceFace, $dir . '/test-face.png');
}

// Create or get test user
$user = User::firstOrCreate(
    ['email' => 'mfa-test@example.com'],
    [
        'name' => 'MFA Test User',
        'password' => bcrypt('password'),
        'role' => 'customer',
        'is_active' => true,
        'identity_image' => 'identities/test-face.png',
    ]
);

// Start session
$session = $app['session.store'];
$session->start();

// Ensure Facades have access to app
Illuminate\Support\Facades\Facade::setFacadeApplication($app);

// Log out any existing user
Auth::logout();

// 1) Login and trigger MFA
$request = Request::create('/login', 'POST', [
    'email' => $user->email,
    'password' => 'password',
]);
$request->setLaravelSession($session);

/** @var \App\Http\Controllers\AuthController $authController */
$authController = $app->make(\App\Http\Controllers\AuthController::class);
$response = $authController->login($request);

echo "[Login] Response status: " . ($response->getStatusCode() ?? 'n/a') . "\n";
if ($response instanceof \Illuminate\Http\RedirectResponse) {
    echo "[Login] Redirecting to: " . $response->getTargetUrl() . "\n";
}

$expectedOtp = $session->get('expected_otp');
if (!$expectedOtp) {
    echo "[Login] ERROR: No OTP generated in session.\n";
    exit(1);
}

echo "[Login] OTP generated: {$expectedOtp}\n";

// 2) Verify OTP
$verifyRequest = Request::create('/verify-otp', 'POST', ['otp' => $expectedOtp]);
$verifyRequest->setLaravelSession($session);

/** @var \App\Http\Controllers\Auth\OTPController $otpController */
$otpController = $app->make(\App\Http\Controllers\Auth\OTPController::class);
$verifyResponse = $otpController->verify($verifyRequest);

echo "[OTP] Response status: " . ($verifyResponse->getStatusCode() ?? 'n/a') . "\n";
if ($verifyResponse instanceof \Illuminate\Http\RedirectResponse) {
    echo "[OTP] Redirecting to: " . $verifyResponse->getTargetUrl() . "\n";
}

if (!Auth::check()) {
    echo "[OTP] ERROR: User not authenticated after OTP.\n";
    exit(1);
}

echo "[OTP] User authenticated: " . Auth::user()->email . "\n";

// 3) Ensure we can place an order (Buy Now)
$product = Product::first();
if (!$product) {
    // create minimal product data
    $seller = User::where('role', 'seller')->first();
    if (!$seller) {
        $seller = User::create([
            'name' => 'Test Seller',
            'email' => 'mfa-seller@example.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
            'is_active' => true,
        ]);
    }

    $category = \App\Models\Category::first();
    if (!$category) {
        $category = \App\Models\Category::create(['name' => 'Test Category', 'slug' => 'test-category']);
    }

    $product = Product::create([
        'seller_id' => $seller->id,
        'category_id' => $category->id,
        'name' => 'MFA Test Product',
        'slug' => 'mfa-test-product-' . uniqid(),
        'description' => 'Test product for MFA flow',
        'price' => 100000,
        'stock' => 10,
    ]);
}

$orderRequest = Request::create('/orders', 'POST', [
    'product_id' => $product->id,
    'quantity' => 1,
    'recipient_name' => 'MFA Buyer',
    'recipient_phone' => '0123456789',
    'delivery_address' => '123 Testing Lane',
    'payment_method' => 'cod',
]);
$orderRequest->setLaravelSession($session);

/** @var \App\Http\Controllers\OrderController $orderController */
$orderController = $app->make(\App\Http\Controllers\OrderController::class);
$orderResponse = $orderController->store($orderRequest);

echo "[Order] Response status: " . ($orderResponse->getStatusCode() ?? 'n/a') . "\n";
if ($orderResponse instanceof \Illuminate\Http\RedirectResponse) {
    echo "[Order] Redirecting to: " . $orderResponse->getTargetUrl() . "\n";
}

$order = Order::latest()->first();
if ($order) {
    echo "[Order] Created order #{$order->id} for user {$order->customer_id} with total {$order->total_amount}\n";
} else {
    echo "[Order] ERROR: No order found after order controller run.\n";
}
