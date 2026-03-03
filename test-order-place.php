<?php

require __DIR__ . '/vendor/autoload.php';

// Load Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;

echo "=== Testing Order Placement ===\n\n";

// Get or create a test customer
$customer = User::where('email', 'customer@test.com')->first();
if (!$customer) {
    echo "No test customer found. Creating one...\n";
    $customer = User::create([
        'name' => 'Test Customer',
        'email' => 'customer@test.com',
        'password' => bcrypt('password'),
        'role' => 'customer',
    ]);
    echo "Customer created: {$customer->id}\n";
} else {
    echo "Test customer found: {$customer->id}\n";
}

// Get a seller
$seller = User::where('role', 'seller')->first();
if (!$seller) {
    echo "ERROR: No seller found in database\n";
    exit;
}
echo "Seller found: {$seller->id}\n";

// Get or create a product
$product = Product::first();
if (!$product) {
    echo "ERROR: No product found in database\n";
    exit;
}
echo "Product found: {$product->id}\n";

// Check customer cart
$cart = $customer->cart;
if (!$cart) {
    echo "Creating cart for customer...\n";
    $cart = Cart::create(['customer_id' => $customer->id]);
} else {
    echo "Cart found: {$cart->id}\n";
}

// Add product to cart if not already there
$cartItem = $cart->items()->where('product_id', $product->id)->first();
if (!$cartItem) {
    echo "Adding product to cart...\n";
    $cartItem = CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);
    echo "Cart item created: {$cartItem->id}\n";
} else {
    echo "Cart item already exists: {$cartItem->id}\n";
}

// Test order creation
echo "\n--- Attempting to create order ---\n";

try {
    $order = Order::create([
        'order_number' => 'ORD-TEST-' . uniqid(),
        'customer_id' => $customer->id,
        'seller_id' => $seller->id,
        'status' => 'pending',
        'subtotal' => 100000,
        'shipping_fee' => 30000,
        'discount_amount' => 0,
        'total_amount' => 130000,
        'payment_method' => 'cod',
        'payment_status' => 'pending',
        'recipient_name' => 'Test Recipient',
        'recipient_phone' => '0123456789',
        'delivery_address' => 'Test Address, Ward, District, City',
    ]);
    
    echo "Order created successfully: {$order->id}\n";
    echo "Order details:\n";
    echo "  Number: {$order->order_number}\n";
    echo "  Total: {$order->total_amount}\n";
    echo "  Status: {$order->status}\n";
    echo "  Payment Status: {$order->payment_status}\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
