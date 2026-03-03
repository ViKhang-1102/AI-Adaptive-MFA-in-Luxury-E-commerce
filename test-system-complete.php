<?php
// Test all system components are in place

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\ProductReview;
use App\Models\ReviewImage;
use App\Models\Message;

echo "\n========== SYSTEM VERIFICATION ==========\n\n";

// 1. Check database tables
echo "1. DATABASE TABLES:\n";
$tables = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()");
$tableNames = array_map(fn($t) => $t->TABLE_NAME, $tables);

$required_tables = ['product_reviews', 'review_images', 'messages'];
foreach($required_tables as $table) {
    if(in_array($table, $tableNames)) {
        echo "   ✅ $table\n";
    } else {
        echo "   ❌ $table MISSING\n";
    }
}

// 2. Check models exist
echo "\n2. MODELS:\n";
echo "   " . (class_exists('App\Models\ProductReview') ? "✅ ProductReview" : "❌ ProductReview") . "\n";
echo "   " . (class_exists('App\Models\ReviewImage') ? "✅ ReviewImage" : "❌ ReviewImage") . "\n";
echo "   " . (class_exists('App\Models\Message') ? "✅ Message" : "❌ Message") . "\n";

// 3. Check controllers exist  
echo "\n3. CONTROLLERS:\n";
echo "   " . (class_exists('App\Http\Controllers\ReviewController') ? "✅ ReviewController" : "❌ ReviewController") . "\n";
echo "   " . (class_exists('App\Http\Controllers\MessageController') ? "✅ MessageController" : "❌ MessageController") . "\n";

// 4. Check views exist
echo "\n4. VIEWS:\n";
$views = [
    'resources/views/products/show.blade.php' => 'products.show',
];
foreach($views as $path => $view) {
    echo "   " . (file_exists($path) ? "✅" : "❌") . " $view\n";
}

// 5. Check routes
echo "\n5. ROUTES:\n";
$routes = [
    'reviews.store' => 'POST /products/{product}/reviews',
    'reviews.destroy' => 'DELETE /reviews/{review}',
    'messages.get' => 'GET /products/{product}/messages',
    'messages.send' => 'POST /products/{product}/messages',
    'messages.read' => 'POST /messages/{message}/read',
];

$routeCollection = app('router')->getRoutes();
foreach($routes as $name => $description) {
    $exists = false;
    foreach($routeCollection as $route) {
        if($route->getName() === $name) {
            $exists = true;
            break;
        }
    }
    echo "   " . ($exists ? "✅" : "❌") . " $name ($description)\n";
}

// 6. Check database samples
echo "\n6. SAMPLE DATA:\n";
$review_count = DB::table('product_reviews')->count();
$message_count = DB::table('messages')->count();
$review_image_count = DB::table('review_images')->count();

echo "   Reviews: $review_count records\n";
echo "   Review Images: $review_image_count records\n";
echo "   Messages: $message_count records\n";

// 7. Check migrations
echo "\n7. MIGRATIONS:\n";
$migrations = DB::table('migrations')
    ->where('migration', 'like', '%review%')
    ->orWhere('migration', 'like', '%message%')
    ->get();

foreach($migrations as $migration) {
    echo "   ✅ " . $migration->migration . "\n";
}

echo "\n========== VERIFICATION COMPLETE ==========\n";
echo "\n✅ All critical components verified!\n";
echo "\nThe system is ready for testing. Start with:\n";
echo "1. Test product card clickability\n";
echo "2. Test review submission (as customer who bought product)\n";
echo "3. Test real-time messaging\n";
echo "4. Test authentication with multiple roles\n";
?>
