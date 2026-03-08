<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

Mail::raw('This is a test to see if Mailpit receives emails from Laravel.', function($msg) {
    $msg->to('admin@gmail.com')->subject('Testing Mailpit Integration');
});

echo "Test email dispatched successfully.\n";
