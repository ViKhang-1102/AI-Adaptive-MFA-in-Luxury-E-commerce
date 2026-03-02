<?php
require __DIR__ . '/vendor/autoload.php';
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

// manually create users without factories
$seller = User::create([
    'name' => 'Seller',
    'email' => 'seller' . time() . '@example.com',
    'password' => bcrypt('password'),
    'role' => 'seller',
    'is_active' => true,
]);
$customer = User::create([
    'name' => 'Customer',
    'email' => 'customer' . time() . '@example.com',
    'password' => bcrypt('password'),
    'role' => 'customer',
    'is_active' => true,
]);

$product = Product::create([
    'seller_id' => $seller->id,
    'category_id' => 1,
    'name' => 'Temp product',
    'slug' => 'temp-product-' . time(),
    'description' => 'desc',
    'price' => 1000,
    'stock' => 5,
    'is_active' => true,
]);

$order = Order::create([
    'order_number' => 'TEST' . time(),
    'customer_id' => $customer->id,
    'seller_id' => $seller->id,
    'subtotal' => 1000,
    'shipping_fee' => 0,
    'discount_amount' => 0,
    'total_amount' => 1000,
    'recipient_name' => 'Foo',
    'recipient_phone' => '123123',
    'delivery_address' => 'Addr',
]);

OrderItem::create([
    'order_id' => $order->id,
    'product_id' => $product->id,
    'product_name' => $product->name,
    'product_price' => $product->price,
    'quantity' => 1,
    'subtotal' => 1000,
]);

echo "before delete orders=" . Order::count() . " items=" . OrderItem::count() . "\n";
$product->delete();
echo "after delete orders=" . Order::count() . " items=" . OrderItem::count() . "\n";
$remaining = Order::find($order->id);
echo "order exists? " . (!is_null($remaining) ? 'yes' : 'no') . "\n";
$orderItemPid = OrderItem::where('order_id', $order->id)->pluck('product_id')->first();
echo "order_item product_id after delete: ".var_export($orderItemPid,true)."\n";
