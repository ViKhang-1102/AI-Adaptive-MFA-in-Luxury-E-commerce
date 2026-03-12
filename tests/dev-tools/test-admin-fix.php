<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$admin = User::where('role', 'admin')->first();

if (!$admin) {
    echo "No admin user found.\n";
    exit;
}

$admin->email_verified_at = now();
$admin->save();

echo "Admin user #{$admin->id} email marked as verified.\n";
