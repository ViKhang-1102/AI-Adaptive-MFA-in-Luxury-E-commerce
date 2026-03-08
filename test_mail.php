<?php

use Illuminate\Support\Facades\Mail;

Mail::raw('Test Mailpit', function($msg) {
    $msg->to('admin@gmail.com')->subject('Testing Mailpit');
});

echo "Email dispatched!\n";
