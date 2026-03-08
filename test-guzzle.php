<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$client = new Client(['base_uri' => 'http://localhost:8000', 'cookies' => true, 'allow_redirects' => false]);

// 1. Get CSRF token
$response = $client->get('/login');
$html = (string) $response->getBody();
preg_match('/<meta name="csrf-token" content="(.*?)">/', $html, $matches);
$csrf = $matches[1] ?? '';

if (!$csrf) {
    die("Failed to get CSRF token.\n");
}

echo "Got CSRF token: $csrf\n";

// 2. Login
echo "Logging in...\n";
$response = $client->post('/login', [
    'form_params' => [
        '_token' => $csrf,
        'email' => 'customer1@example.com',
        'password' => 'password',
    ]
]);
echo "Login redirect: " . $response->getHeaderLine('Location') . "\n";

// The login should NOT trigger MFA since customer1 device might be verified, 
// OR it triggers OTP. Let's see!
if (strpos($response->getHeaderLine('Location'), '/verify-otp') !== false) {
    echo "Login triggered MFA!\n";
    // We would need to bypass this or check DB for OTP. For order testing, we just want to know if MFA triggers!
}

// 3. Add to cart
echo "Adding to cart...\n";
$response = $client->post('/cart/add', [
    'form_params' => [
        '_token' => $csrf,
        'product_id' => 1,
        'quantity' => 150, // Huge amount -> >$5000
    ]
]);
echo "Cart redirect: " . $response->getHeaderLine('Location') . "\n";

// 4. Checkout
echo "Checking out...\n";
$response = $client->post('/checkout/store', [
    'form_params' => [
        '_token' => $csrf,
        'shipping_address' => 'Test Address',
        'phone' => '1234567890',
        'payment_method' => 'cod',
        'note' => 'Test',
    ]
]);

$location = $response->getHeaderLine('Location');
echo "Checkout redirect: $location\n";

if (strpos($location, '/verify-otp') !== false) {
    echo "SUCCESS: Checkout triggered MFA correctly!\n";
} else {
    echo "FAILED: Checkout did not trigger MFA. Redirected to: $location\n";
}
