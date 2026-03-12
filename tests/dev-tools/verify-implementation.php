<?php
// Simple validation test
echo "=== E-Commerce System - Implementation Verification ===\n\n";

// 1. Check files exist and are readable
$files = [
    'app/Http/Controllers/OrderController.php',
    'app/Http/Controllers/Seller/OrderController.php',
    'app/Http/Controllers/Admin/OrderController.php',
    'resources/views/products/index.blade.php',
    'resources/views/cart/index.blade.php',
    'resources/views/orders/show.blade.php',
    'resources/views/seller/orders/show.blade.php',
    'resources/views/admin/orders/show.blade.php',
];

echo "1. File Existence Check:\n";
foreach ($files as $file) {
    $exists = file_exists($file);
    $status = $exists ? '✓' : '✗';
    echo "   $status $file\n";
}

echo "\n2. PHP Syntax Check:\n";
$phpFiles = [
    'app/Http/Controllers/OrderController.php',
    'app/Http/Controllers/Seller/OrderController.php',
    'app/Http/Controllers/Admin/OrderController.php',
];

foreach ($phpFiles as $file) {
    $output = shell_exec("php -l $file 2>&1");
    $valid = strpos($output, 'No syntax errors') !== false;
    $status = $valid ? '✓' : '✗';
    echo "   $status $file\n";
}

echo "\n3. Implementation Verification:\n";

// Check for checkout() method with selected items
$orderControllerContent = file_get_contents('app/Http/Controllers/OrderController.php');
$checks = [
    'Item selection logic' => strpos($orderControllerContent, "selectedItemIds = request('item_ids'") !== false,
    'whereIn() filtering' => strpos($orderControllerContent, "whereIn('id', \$selectedItemIds)") !== false,
    'Image eager loading' => strpos($orderControllerContent, "items.product.images") !== false,
];

foreach ($checks as $check => $result) {
    $status = $result ? '✓' : '✗';
    echo "   $status $check\n";
}

// Check cart view for checkboxes
$cartContent = file_get_contents('resources/views/cart/index.blade.php');
$cartChecks = [
    'Item checkboxes' => strpos($cartContent, 'item_ids[]') !== false,
    'Select All' => strpos($cartContent, 'select-all') !== false,
    'JavaScript logic' => strpos($cartContent, 'updateOrderSummary') !== false,
];

echo "\n4. Cart View Verification:\n";
foreach ($cartChecks as $check => $result) {
    $status = $result ? '✓' : '✗';
    echo "   $status $check\n";
}

// Check product views
$productContent = file_get_contents('resources/views/products/index.blade.php');
echo "\n5. Product Card Clickability:\n";
$productChecks = [
    'Full card anchor tag' => strpos($productContent, 'class=\"block bg-white rounded-lg shadow') !== false,
];

foreach ($productChecks as $check => $result) {
    $status = $result ? '✓' : '✗';
    echo "   $status $check\n";
}

echo "\n=== All Verifications Complete ===\n";

