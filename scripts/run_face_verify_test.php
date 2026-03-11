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

$storedRelative = 'identity_images/' . basename($source);
$img = file_get_contents($source);
$b64 = 'data:image/jpeg;base64,' . base64_encode($img);

// Compare against the same identity image (should match)
$result = $service->verify($b64, $storedRelative);

echo "FaceVerificationService result (original image):\n";
var_dump($result);

// Simulate environmental shifts (color jitter + brightness) to test white balance correction
function base64_from_jittered_image(string $path, float $alpha, int $beta, array $color_shift): string
{
    // Prepare python one-liner for color/brightness jitter
    $escapedPath = str_replace('\\', '\\\\', $path);
    $colorList = sprintf('[%d, %d, %d]', $color_shift[0], $color_shift[1], $color_shift[2]);

    $python = sprintf(
        "import base64, cv2, numpy as np, sys; " .
        "img=cv2.imread(r'%s'); " .
        "sys.exit(1) if img is None else None; " .
        "img=cv2.convertScaleAbs(img, alpha=%s, beta=%s); " .
        "b,g,r=%s; " .
        "img[:,:,0]=cv2.add(img[:,:,0], b); " .
        "img[:,:,1]=cv2.add(img[:,:,1], g); " .
        "img[:,:,2]=cv2.add(img[:,:,2], r); " .
        "_,buf=cv2.imencode('.jpg', img, [int(cv2.IMWRITE_JPEG_QUALITY), 85]); " .
        "print(base64.b64encode(buf).decode('ascii'))",
        $escapedPath,
        $alpha,
        $beta,
        $colorList
    );

    $cmd = 'python -c ' . escapeshellarg($python);
    $out = shell_exec($cmd);
    return 'data:image/jpeg;base64,' . trim($out);
}

$jittered = base64_from_jittered_image($source, 0.7, -30, [20, 10, -15]);
$result2 = $service->verify($jittered, $storedRelative);

echo "FaceVerificationService result (jittered / low-light):\n";
var_dump($result2);

$jittered2 = base64_from_jittered_image($source, 1.2, 20, [-15, -10, 10]);
$result3 = $service->verify($jittered2, $storedRelative);

echo "FaceVerificationService result (jittered / warm-light):\n";
var_dump($result3);
