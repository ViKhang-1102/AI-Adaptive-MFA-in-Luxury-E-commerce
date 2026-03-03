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
use App\Models\CustomerAddress;
use Illuminate\Support\Facades\Auth;

echo "\n╔════════════════════════════════════════════════════════╗\n";
echo "║         ORDER PLACEMENT COMPLETE TEST                  ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

// 1. Find or create customer
echo "STEP 1: Customer Setup\n";
echo "  ┣ Finding test customer...\n";
$customer = User::where('email', 'fulltest@customer.com')->first();
if (!$customer) {
    $customer = User::create([
        'name' => 'Full Test Customer',
        'email' => 'fulltest@customer.com',
        'password' => bcrypt('password'),
        'role' => 'customer',
    ]);
    echo "  ┗ ✓ New customer created: ID {$customer->id}\n";
} else {
    echo "  ┗ ✓ Existing customer found: ID {$customer->id}\n";
}

// 2. Verify seller exists
echo "\nSTEP 2: Seller Verification\n";
$seller = User::where('role', 'seller')->first();
echo "  ┗ ✓ Seller found: ID {$seller->id}\n";

// 3. Verify product exists
echo "\nSTEP 3: Product Verification\n";
$product = Product::first();
if (!$product) {
    echo "  ┗ ✗ ERROR: No products in database\n";
    exit;
}
$price = $product->getDiscountedPrice();
echo "  ┗ ✓ Product found: '{$product->name}' - Price: ₫" . number_format($price, 0) . "\n";

// 4. Setup customer cart
echo "\nSTEP 4: Cart Setup\n";
$cart = $customer->cart ?? Cart::create(['customer_id' => $customer->id]);
$cart->items()->delete(); // clear existing
$cartItem = CartItem::create([
    'cart_id' => $cart->id,
    'product_id' => $product->id,
    'quantity' => 1,
]);
echo "  ┗ ✓ Cart prepared with 1 item\n";

// 5. Address setup
echo "\nSTEP 5: Address Setup\n";
$address = CustomerAddress::where('customer_id', $customer->id)->first();
if (!$address) {
    $address = CustomerAddress::create([
        'customer_id' => $customer->id,
        'label' => 'Test Address',
        'recipient_name' => 'Test Recipient',
        'recipient_phone' => '0123456789',
        'address' => '123 Test Street, Test Ward, Test District, Test City',
        'is_default' => true,
    ]);
    echo "  ┗ ✓ New address created\n";
} else {
    echo "  ┗ ✓ Existing address found\n";
}

// 6. Test checkout data preparation
echo "\nSTEP 6: Checkout Data Preparation\n";
$items = $cart->items()->with('product')->get();
$subtotal = $items->sum(fn($item) => $item->product->getDiscountedPrice() * $item->quantity);
$shippingFee = 20000; // default fee
$total = $subtotal + $shippingFee;
echo "  ├ Subtotal: ₫" . number_format($subtotal, 0) . "\n";
echo "  ├ Shipping: ₫" . number_format($shippingFee, 0) . "\n";
echo "  ┗ Total: ₫" . number_format($total, 0) . "\n";

// 7. Simulate form submission
echo "\nSTEP 7: Form Submission Simulation\n";
$formData = [
    'address_id' => $address->id,
    'payment_method' => 'cod',
];
echo "  ├ Address ID: {$formData['address_id']}\n";
echo "  ├ Payment Method: {$formData['payment_method']}\n";

// Validate form (simulating controller validation)
echo "\nSTEP 8: Form Validation\n";
$errors = [];

if (!isset($formData['payment_method']) || !in_array($formData['payment_method'], ['cod', 'online'])) {
    $errors[] = 'Invalid payment method';
}

$addressObj = CustomerAddress::find($formData['address_id'] ?? null);
if (!$addressObj || $addressObj->customer_id != $customer->id) {
    $errors[] = 'Invalid address selection';
}

if (empty($errors)) {
    echo "  ┗ ✓ All validations passed\n";
} else {
    echo "  ┗ ✗ Validation errors:\n";
    foreach ($errors as $error) {
        echo "     - $error\n";
    }
    exit;
}

// 8. Create order
echo "\nSTEP 9: Order Creation\n";
try {
    $order = Order::create([
        'order_number' => 'ORD' . date('Ymd') . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT),
        'customer_id' => $customer->id,
        'seller_id' => $product->seller_id,
        'status' => 'pending',
        'payment_status' => 'pending',
        'subtotal' => $subtotal,
        'shipping_fee' => $shippingFee,
        'discount_amount' => 0,
        'total_amount' => $total,
        'payment_method' => $formData['payment_method'],
        'recipient_name' => $addressObj->recipient_name,
        'recipient_phone' => $addressObj->recipient_phone,
        'delivery_address' => $addressObj->address,
    ]);
    echo "  ├ Order ID: {$order->id}\n";
    echo "  ├ Order Number: {$order->order_number}\n";
    echo "  ├ Status: {$order->status}\n";
    echo "  ┗ ✓ Order created successfully\n";
} catch (\Exception $e) {
    echo "  ┗ ✗ Order creation failed: " . $e->getMessage() . "\n";
    exit;
}

// 9. Create order items
echo "\nSTEP 10: Add Order Items\n";
try {
    foreach ($items as $item) {
        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item->product->id,
            'product_name' => $item->product->name,
            'product_price' => $item->product->getDiscountedPrice(),
            'quantity' => $item->quantity,
            'subtotal' => $item->product->getDiscountedPrice() * $item->quantity,
        ]);
    }
    echo "  ┗ ✓ Order items added successfully\n";
} catch (\Exception $e) {
    echo "  ┗ ✗ Failed to add items: " . $e->getMessage() . "\n";
    exit;
}

// 10. Create payment
echo "\nSTEP 11: Create Payment Record\n";
try {
    \App\Models\Payment::create([
        'order_id' => $order->id,
        'payment_method' => $formData['payment_method'],
        'status' => 'pending',
        'amount' => $total,
    ]);
    echo "  ┗ ✓ Payment record created\n";
} catch (\Exception $e) {
    echo "  ┗ ✗ Failed to create payment: " . $e->getMessage() . "\n";
    exit;
}

// 11. Clear cart
echo "\nSTEP 12: Cart Cleanup\n";
$cart->items()->delete();
echo "  ┗ ✓ Cart cleared\n";

// 12. Verify final state
echo "\nSTEP 13: Verification\n";
$dbOrder = Order::find($order->id);
$dbItems = $dbOrder->items()->count();
$dbPayment = $dbOrder->payment;
echo "  ├ Order in DB: {$dbOrder->order_number}\n";
echo "  ├ Order Items: {$dbItems}\n";
echo "  ├ Payment Status: {$dbPayment->status}\n";
echo "  ├ Total Amount: ₫" . number_format($dbOrder->total_amount, 0) . "\n";
echo "  ┗ ✓ Verification complete\n";

// Success message
echo "\n╔════════════════════════════════════════════════════════╗\n";
echo "║  ✅ ORDER PLACEMENT TEST - ALL STEPS COMPLETED          ║\n";
echo "╠════════════════════════════════════════════════════════╣\n";
echo "║ Order successfully created and saved to database!       ║\n";
echo "║                                                        ║\n";
echo "║ Order Details:                                         ║\n";
echo "║  • Order Number: {$order->order_number}\n";
echo "║  • Customer: {$customer->name}                          ║\n";
echo "║  • Total: ₫" . number_format($total, 0) . "                                     ║\n";
echo "║  • Status: {$order->status}                              ║\n";
echo "║  • Payment: {$order->payment_method}                              ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";
