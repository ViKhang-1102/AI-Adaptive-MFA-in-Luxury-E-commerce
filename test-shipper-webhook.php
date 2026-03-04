<?php

require __DIR__ . '/vendor/autoload.php';

// boot Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
// bootstrap without handling a web request to prevent HTML noise
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

use App\Models\Order;
use App\Models\User;

echo "=== Shipper Webhook Test ===\n\n";

// ensure we have at least one order
$order = Order::first();
if (! $order) {
    echo "no existing order found, creating a dummy record...\n";
    // create minimal user records
    $customer = User::firstWhere('role', 'customer') ?? User::create([
        'name' => 'Webhook Customer',
        'email' => 'webhook-customer@example.com',
        'password' => bcrypt('password'),
        'role' => 'customer',
    ]);
    $seller = User::firstWhere('role', 'seller') ?? User::create([
        'name' => 'Webhook Seller',
        'email' => 'webhook-seller@example.com',
        'password' => bcrypt('password'),
        'role' => 'seller',
    ]);

    $order = Order::create([
        'order_number' => 'WH-' . strtoupper(uniqid()),
        'customer_id' => $customer->id,
        'seller_id' => $seller->id,
        'status' => 'pending',
        'subtotal' => 100,
        'shipping_fee' => 0,
        'discount_amount' => 0,
        'total_amount' => 100,
        'payment_method' => 'cod',
        'payment_status' => 'pending',
        'recipient_name' => 'Test',
        'recipient_phone' => '000',
        'delivery_address' => 'Some address',
    ]);
    echo "created order id {$order->id} status {$order->status}\n";
} else {
    echo "using existing order id {$order->id} status {$order->status}\n";
}

// prepare payload
$payload = [
    'order_id' => $order->id,
    'secret_key' => 'LUXGUARD_SECRET_2026',
];

// make internal request
$request = \Illuminate\Http\Request::create(
    '/api/shipper/update-status',
    'POST',
    [],
    [],
    [],
    ['CONTENT_TYPE' => 'application/json'],
    json_encode($payload)
);

$response = $kernel->handle($request);

$code = $response->getStatusCode();
$body = $response->getContent();

echo "\n-- POST /api/shipper/update-status returned $code\n";
echo "Response body (first 200 chars): " . substr($body, 0, 200) . "\n";

// refresh and show status
$order->refresh();
echo "\nOrder status after webhook: {$order->status}\n";
if ($order->delivered_at) {
    echo "Delivered at: {$order->delivered_at}\n";
}

echo "\nTest complete.\n";
