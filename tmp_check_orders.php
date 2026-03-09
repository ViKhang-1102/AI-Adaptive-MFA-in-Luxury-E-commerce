<?php

require __DIR__ . '/vendor/autoload.php';

$orders = App\Models\Order::select('id','status','payment_status')->get();
foreach ($orders as $o) {
    echo "#{$o->id} status={$o->status} payment={$o->payment_status}\n";
}
