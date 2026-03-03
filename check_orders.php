<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;

$valid = ['confirmed','processing','shipped','delivered'];
$cnt = Order::whereIn('status', $valid)->count();
echo "orders with status in [".implode(',', $valid)."]: $cnt\n";
$today = Order::whereIn('status',$valid)->whereDate('created_at', today())->count();
echo "today orders: $today\n";
