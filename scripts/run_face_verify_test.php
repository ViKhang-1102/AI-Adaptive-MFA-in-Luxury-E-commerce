<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\FaceVerificationService::class);

// Use an existing stored identity image for testing
$source = storage_path('app/public/identity_images/user_1_1772991665.jpg');
if (!file_exists($source)) {
    echo "Test image not found: $source\n";
    exit(1);
}

$img = file_get_contents($source);
$b64 = 'data:image/jpeg;base64,' . base64_encode($img);

// Compare against the same identity image (should match)
$result = $service->verify($b64, 'identity_images/user_1_1772991665.jpg');

echo "FaceVerificationService result:\n";
var_dump($result);
