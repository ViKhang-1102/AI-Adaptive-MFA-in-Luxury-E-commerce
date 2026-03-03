<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\User;
use App\Models\OrderItem;

$seller = User::where('role','seller')->first();
echo "seller id=" . $seller->id . "\n";
$valid = ['confirmed','processing','shipped','delivered'];
$totalOrders = Order::whereIn('status',$valid)->where('seller_id',$seller->id)->count();
$totalRevenue = OrderItem::whereHas('order', function($q) use ($seller,$valid) {
    $q->whereIn('status',$valid)->where('seller_id',$seller->id);
})->sum(DB::raw('quantity * product_price'));
echo "totalOrders=$totalOrders, totalRevenue=$totalRevenue\n";
