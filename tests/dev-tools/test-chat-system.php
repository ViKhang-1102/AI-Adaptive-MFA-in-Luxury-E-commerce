<?php

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Message;
use App\Models\Product;

echo "\n";
echo "==========================================\n";
echo "CHAT SYSTEM - COMPREHENSIVE TEST\n";
echo "==========================================\n\n";

echo "STEP 1: Setting up test data...\n";

$seller = User::where('role', 'seller')->first();
$customer = User::where('role', 'customer')->first();

if (!$seller || !$customer) {
    echo "Missing seller or customer seed data.\n";
    exit;
}

$product = Product::where('seller_id', $seller->id)->first();
if (!$product) {
    echo "Seller has no products; create one before running this test.\n";
    exit;
}

echo "Seller: {$seller->name} (ID: {$seller->id})\n";
echo "Customer: {$customer->name} (ID: {$customer->id})\n";
echo "Product: {$product->name} (ID: {$product->id})\n\n";

echo "STEP 2: Testing unread message count...\n";
Auth::login($customer);
$unreadCount = $customer->unreadMessagesCount();
echo "Customer unread count: $unreadCount\n";

echo "\nSTEP 3: Testing message creation...\n";

$message1 = Message::create([
    'sender_id' => $customer->id,
    'receiver_id' => $seller->id,
    'product_id' => $product->id,
    'message' => 'Hi, does this product have warranty?',
    'read' => false,
]);

echo "Customer message created (ID: {$message1->id})\n";

echo "\nSTEP 4: Testing message retrieval (forConversation scope)...\n";
$messages = Message::forConversation($customer->id, $seller->id, $product->id)->get();
echo "Messages retrieved: " . count($messages) . "\n";

echo "\nSTEP 5: Testing mark as read...\n";
Auth::login($seller);
$unreadBefore = Message::where('receiver_id', $seller->id)->where('read', false)->count();
echo "Seller unread count before: $unreadBefore\n";

Message::where('receiver_id', $seller->id)
    ->where('sender_id', $customer->id)
    ->where('product_id', $product->id)
    ->update(['read' => true]);

$unreadAfter = Message::where('receiver_id', $seller->id)->where('read', false)->count();
echo "Seller unread count after: $unreadAfter\n";

echo "\nSTEP 6: Testing seller reply...\n";
$message2 = Message::create([
    'sender_id' => $seller->id,
    'receiver_id' => $customer->id,
    'product_id' => $product->id,
    'message' => 'Yes, 2 years warranty included!',
    'read' => false,
]);

echo "Seller message created (ID: {$message2->id})\n";

echo "\nALL CHAT TESTS COMPLETED.\n";
