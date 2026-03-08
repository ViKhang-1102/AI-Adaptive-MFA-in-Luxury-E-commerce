<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

use App\Models\User;
use Illuminate\Support\Facades\Session;

$email = 'mfatester' . time() . '@test.com';
$user = User::create([
    'name' => 'Test User',
    'email' => $email,
    'password' => bcrypt('password'),
    'role' => 'customer'
]);

Session::start();
Session::put('device_verified', false);
Session::put('mfa_verified', false);
Session::put('mfa_verified_for_login', false);

echo "Testing Login MFA (score usually 40+ for new device)...\n";
$request = \Illuminate\Http\Request::create('/login', 'POST', [
    'email' => $email,
    'password' => 'password',
]);
$request->setSession(app('session.store'));
$app->instance('request', $request);
$controller = app(\App\Http\Controllers\AuthController::class);

try {
    $response = $controller->login($request);
    $redirect = "No Redirect";
    if (method_exists($response, 'getTargetUrl')) {
        $redirect = $response->getTargetUrl();
    } elseif (method_exists($response, 'headers') && $response->headers->get('Location')) {
        $redirect = $response->headers->get('Location');
    }
    echo "Login Redirects to: " . $redirect . "\n";
    if (Session::has('ai_warning')) {
        echo "Login Warning: " . Session::get('ai_warning') . "\n";
    }
} catch (\Exception $e) {
    echo "Login Error: " . $e->getMessage() . "\n";
}

echo "\nTesting Checkout MFA (score usually 80+ for large order + new device)...\n";
auth()->login($user);
// Make a POST to OrderController
$request2 = \Illuminate\Http\Request::create('/checkout/store', 'POST', [
    'shipping_address' => '123 Fake Street',
    'phone' => '1234567890',
    'payment_method' => 'cod',
    'note' => '',
]);
$request2->setSession(app('session.store'));
$app->instance('request', $request2);

$orderController = app(\App\Http\Controllers\OrderController::class);
try {
    // Inject a dummy cart to bypass cart empty exception
    // Wait, OrderController->store relies on Session Cart or DB Cart.
    // Let's just create a Cart logic
    $cart = \App\Models\Cart::create(['customer_id' => $user->id]);
    $product = \App\Models\Product::first();
    \App\Models\CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 200 // massive amount to trigger $5000 limit
    ]);
    
    $response2 = $orderController->store($request2);
    $redirect2 = "No Redirect";
    if (method_exists($response2, 'getTargetUrl')) {
        $redirect2 = $response2->getTargetUrl();
    } elseif (method_exists($response2, 'headers') && $response2->headers->get('Location')) {
        $redirect2 = $response2->headers->get('Location');
    }
    echo "Checkout Redirects to: " . $redirect2 . "\n";
    
    $audit = \App\Models\SecurityAudit::orderBy('id', 'desc')->first();
    echo "AI Score for checkout: " . ($audit ? $audit->risk_score : 'Not found') . "\n";
    echo "AI Suggestion for checkout: " . ($audit ? $audit->suggestion : 'Not found') . "\n";
    
} catch (\Exception $e) {
    echo "Checkout Error: " . $e->getMessage() . "\n";
}

