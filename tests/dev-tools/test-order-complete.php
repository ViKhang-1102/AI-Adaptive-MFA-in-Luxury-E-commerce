<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;

echo "Marking oldest pending order as completed (if any)...\n";

$order = Order::where('status', 'pending')->orderBy('created_at')->first();
if (!$order) {
    echo "No pending orders found.\n";
    exit;
}

$order->status = 'completed';
$order->save();

echo "Order #{$order->id} marked as completed.\n";
