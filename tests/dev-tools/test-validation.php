<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Validator;

echo "Running simple validation test...\n";

$data = [
    'email' => 'not-an-email',
    'password' => '123',
];

$rules = [
    'email' => 'required|email',
    'password' => 'required|min:8',
];

$validator = Validator::make($data, $rules);

if ($validator->fails()) {
    print_r($validator->errors()->toArray());
} else {
    echo "Validation passed unexpectedly.\n";
}

echo "Validation test finished.\n";
