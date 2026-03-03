<?php

require __DIR__ . '/vendor/autoload.php';

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
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\CustomerAddress;
use App\Models\SystemFee;

echo "\n=== COMPREHENSIVE ORDER PLACEMENT TEST ===\n\n";

// Get or create test customer
$customer = User::where('email', 'testcustomer@test.com')->first();
if (!$customer) {
    echo "[1] Creating test customer...\n";
    $customer = User::create([
        'name' => 'Test Customer',
        'email' => 'testcustomer@test.com',
        'password' => bcrypt('password123'),
        'role' => 'customer',
        'phone' => '0123456789',
    ]);
    echo "    ✓ Customer created: ID {$customer->id}\n";
} else {
    echo "[1] Test customer found: ID {$customer->id}\n";
}

// Get or find seller
$seller = User::where('role', 'seller')->first();
if (!$seller) {
    echo "[ERROR] No seller found in database\n";
    exit;
}
echo "[2] Seller found: ID {$seller->id}\n";

// Get or create product
$product = Product::where('seller_id', $seller->id)->first();
if (!$product) {
    echo "[ERROR] No product from seller found\n";
    exit;
}
echo "[3] Product found: ID {$product->id}, Price: " . number_format($product->getDiscountedPrice(), 0) . " VNĐ\n";

// Setup customer cart
echo "[4] Setting up cart...\n";
$cart = $customer->cart;
if (!$cart) {
    $cart = Cart::create(['customer_id' => $customer->id]);
    echo "    ✓ Cart created: ID {$cart->id}\n";
} else {
    echo "    ✓ Existing cart: ID {$cart->id}\n";
}

// Clear existing cart items
$cart->items()->delete();
echo "    ✓ Cart cleared\n";

// Add product to cart
$cartItem = CartItem::create([
    'cart_id' => $cart->id,
    'product_id' => $product->id,
    'quantity' => 2,
]);
echo "    ✓ Product added to cart: {$cartItem->quantity}x\n";

// Add default address
echo "[5] Setting up address...\n";
$address = CustomerAddress::where('customer_id', $customer->id)->where('is_default', true)->first();
if (!$address) {
    $address = CustomerAddress::create([
        'customer_id' => $customer->id,
        'label' => 'Home',
        'recipient_name' => 'Test Recipient',
        'recipient_phone' => '0987654321',
        'address' => '123 Test Street, Test Ward, Test District, Test City',
        'is_default' => true,
    ]);
    echo "    ✓ Address created: ID {$address->id}\n";
} else {
    echo "    ✓ Existing address: ID {$address->id}\n";
}

// Get system fee
$systemFee = SystemFee::first();
$shippingFee = $systemFee?->shipping_fee_default ?? 0;
echo "[6] System shipping fee: " . number_format($shippingFee, 0) . " VNĐ\n";

// Test order creation
echo "[7] Simulating order placement...\n";
$cartItems = $cart->items()->with('product')->get();
$subtotal = $cartItems->sum(function ($item) {
    return $item->product->getDiscountedPrice() * $item->quantity;
});
$totalAmount = $subtotal + $shippingFee;

try {
    $order = Order::create([
        'order_number' => 'ORD-TEST-' . strtoupper(uniqid()),
        'customer_id' => $customer->id,
        'seller_id' => $seller->id,
        'status' => 'pending',
        'payment_status' => 'pending',
        'subtotal' => $subtotal,
        'shipping_fee' => $shippingFee,
        'discount_amount' => 0,
        'total_amount' => $totalAmount,
        'payment_method' => 'cod',
        'recipient_name' => $address->recipient_name,
        'recipient_phone' => $address->recipient_phone,
        'delivery_address' => $address->address,
    ]);
    
    echo "    ✓ Order created successfully\n";
    echo "       Order ID: {$order->id}\n";
    echo "       Order Number: {$order->order_number}\n";
    
    // Create order items
    echo "[8] Adding items to order...\n";
    foreach ($cartItems as $cartItem) {
        $itemTotal = $cartItem->product->getDiscountedPrice() * $cartItem->quantity;
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $cartItem->product->id,
            'product_name' => $cartItem->product->name,
            'product_price' => $cartItem->product->getDiscountedPrice(),
            'quantity' => $cartItem->quantity,
            'subtotal' => $itemTotal,
        ]);
        echo "    ✓ Item added: {$cartItem->product->name} x {$cartItem->quantity}\n";
    }
    
    // Create payment
    echo "[9] Creating payment record...\n";
    $payment = Payment::create([
        'order_id' => $order->id,
        'payment_method' => 'cod',
        'status' => 'pending',
        'amount' => $totalAmount,
    ]);
    echo "    ✓ Payment record created: ID {$payment->id}\n";
    
    // Clear cart
    $cart->items()->delete();
    echo "[10] Cart cleared\n";
    
    echo "\n✅ ORDER PLACEMENT TEST SUCCESSFUL\n";
    echo "=============================================================\n";
    echo "Summary:\n";
    echo "  Customer: {$customer->name} ({$customer->email})\n";
    echo "  Order: {$order->order_number}\n";
    echo "  Total Amount: " . number_format($order->total_amount, 0) . " VNĐ\n";
    echo "  Payment Method: {$order->payment_method}\n";
    echo "  Status: {$order->status}\n";
    echo "  Payment Status: {$order->payment_status}\n";
    echo "=============================================================\n\n";
    
} catch (\Exception $e) {
    echo "    ✗ ERROR: " . $e->getMessage() . "\n";
    echo "    File: " . $e->getFile() . "\n";
    echo "    Line: " . $e->getLine() . "\n\n";
}
