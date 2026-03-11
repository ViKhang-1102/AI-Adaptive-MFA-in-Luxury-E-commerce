<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\FaceVerificationService::class);

// Use an existing stored identity image for testing
$files = glob(storage_path('app/public/identity_images/*.jpg'));
if (empty($files)) {
    echo "No test identity images found in storage.\n";
    exit(1);
}
$source = $files[0];

$img = file_get_contents($source);
$b64 = 'data:image/jpeg;base64,' . base64_encode($img);

// Compare against the same identity image (should match)
$result = $service->verify($b64, 'identity_images/user_1_1772991665.jpg');

echo "FaceVerificationService result:\n";
var_dump($result);
