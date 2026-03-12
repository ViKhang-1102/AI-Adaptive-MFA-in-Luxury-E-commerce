<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;

echo "Recent completed orders:\n";

$orders = Order::where('status', 'completed')
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();

foreach ($orders as $o) {
    echo "#{$o->id} | {$o->customer_id} | {$o->total_amount} | {$o->created_at}\n";
}

echo "End of order summary.\n";
