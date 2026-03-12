<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking PayPal configuration...\n";

$clientId = config('services.paypal.client_id');
$secret   = config('services.paypal.secret');
$mode     = config('services.paypal.mode');

echo "Client ID: " . ($clientId ? 'SET' : 'MISSING') . "\n";
echo "Secret: " . ($secret ? 'SET' : 'MISSING') . "\n";
echo "Mode: " . ($mode ?: 'N/A') . "\n";

echo "PayPal config test finished.\n";
