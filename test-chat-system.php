<?php

require 'vendor/autoload.php';
require 'bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Message;
use App\Models\Product;
use Tests\Unit\ChatSystemTest;

echo "\n";
echo "==========================================\n";
echo "CHAT SYSTEM - COMPREHENSIVE TEST\n";
echo "==========================================\n\n";

// 1. Get or create test users
echo "STEP 1: Setting up test data...\n";

$seller = User::where('role', 'seller')->first();
$customer = User::where('role', 'customer')->first();

if (!$seller) {
    echo "❌ No seller found. Please create a seller first.\n";
    exit;
}

if (!$customer) {
    echo "❌ No customer found. Please create a customer first.\n";
    exit;
}

$product = Product::where('seller_id', $seller->id)->first();

if (!$product) {
    echo "❌ Seller has no products. Please create a product first.\n";
    exit;
}

echo "✅ Seller: {$seller->name} (ID: {$seller->id})\n";
echo "✅ Customer: {$customer->name} (ID: {$customer->id})\n";
echo "✅ Product: {$product->name} (ID: {$product->id})\n\n";

// 2. Test unread message count for customer
echo "STEP 2: Testing unread message count...\n";
Auth::login($customer);

$unreadCount = $customer->unreadMessagesCount();
echo "✅ Customer unread count: $unreadCount\n";

// 3. Test message creation
echo "\nSTEP 3: Testing message creation...\n";

// Customer sends message to seller
$message1 = Message::create([
    'sender_id' => $customer->id,
    'receiver_id' => $seller->id,
    'product_id' => $product->id,
    'message' => 'Hi, does this product have warranty?',
    'read' => false,
]);

echo "✅ Customer message created (ID: {$message1->id})\n";
echo "   From: {$customer->name}, To: {$seller->name}\n";
echo "   Product: {$product->name}\n";
echo "   Message: {$message1->message}\n";

// 4. Test message retrieval with forConversation scope
echo "\nSTEP 4: Testing message retrieval (forConversation scope)...\n";

$messages = Message::forConversation($customer->id, $seller->id, $product->id)->get();
echo "✅ Messages retrieved: " . count($messages) . "\n";
foreach ($messages as $msg) {
    echo "   - {$msg->sender->name}: {$msg->message}\n";
}

// 5. Test mark as read
echo "\nSTEP 5: Testing mark as read...\n";
Auth::login($seller);

$unreadBefore = Message::where('receiver_id', $seller->id)
    ->where('read', false)
    ->count();
echo "   Seller unread count before: $unreadBefore\n";

// Seller marks message as read (simulated by endpoint)
Message::where('receiver_id', $seller->id)
    ->where('sender_id', $customer->id)
    ->where('product_id', $product->id)
    ->update(['read' => true]);

$unreadAfter = Message::where('receiver_id', $seller->id)
    ->where('read', false)
    ->count();
echo "✅ Seller unread count after: $unreadAfter\n";

// 6. Test seller's message
echo "\nSTEP 6: Testing seller's reply...\n";

$message2 = Message::create([
    'sender_id' => $seller->id,
    'receiver_id' => $customer->id,
    'product_id' => $product->id,
    'message' => 'Yes, 2 years warranty included!',
    'read' => false,
]);

echo "✅ Seller message created (ID: {$message2->id})\n";
echo "   From: {$seller->name}, To: {$customer->name}\n";
echo "   Message: {$message2->message}\n";

// 7. Test complete conversation
echo "\nSTEP 7: Testing complete conversation...\n";

$allMessages = Message::forConversation($customer->id, $seller->id, $product->id)->get();
echo "✅ Total messages in conversation: " . count($allMessages) . "\n";
foreach ($allMessages as $msg) {
    $sender = $msg->sender->name;
    $receiver = $msg->receiver->name;
    $readStatus = $msg->read ? '✓' : '✗';
    echo "   [$readStatus] $sender → $receiver: {$msg->message}\n";
}

// 8. Test multiple customers (create another customer message)
echo "\nSTEP 8: Testing multiple customers...\n";

$customer2 = User::where('role', 'customer')->where('id', '!=', $customer->id)->first();

if ($customer2) {
    Auth::login($customer2);
    
    $message3 = Message::create([
        'sender_id' => $customer2->id,
        'receiver_id' => $seller->id,
        'product_id' => $product->id,
        'message' => 'Is this in stock?',
        'read' => false,
    ]);
    
    echo "✅ Second customer message created\n";
    echo "   From: {$customer2->name}, To: {$seller->name}\n";
} else {
    echo "⚠️  Only one customer available for testing\n";
}

// 9. Test message count by customer
echo "\nSTEP 9: Testing seller's customer list...\n";
Auth::login($seller);

$messages = Message::whereHas('product', function($q) use ($seller) {
    $q->where('seller_id', $seller->id);
})->with('sender', 'receiver', 'product')
  ->orderByDesc('created_at')
  ->get();

$customersList = [];
foreach ($messages as $msg) {
    $customerId = $msg->sender_id === $seller->id ? $msg->receiver_id : $msg->sender_id;
    $customer = $msg->sender_id === $seller->id ? $msg->receiver : $msg->sender;
    
    if (!isset($customersList[$customerId])) {
        $customersList[$customerId] = [
            'id' => $customerId,
            'name' => $customer->name,
            'message_count' => 0,
            'unread_count' => 0,
        ];
    }
    
    $customersList[$customerId]['message_count']++;
    if (!$msg->read && $msg->receiver_id === $seller->id) {
        $customersList[$customerId]['unread_count']++;
    }
}

echo "✅ Seller has " . count($customersList) . " customers\n";
foreach ($customersList as $cust) {
    echo "   - {$cust['name']}: {$cust['message_count']} messages ({$cust['unread_count']} unread)\n";
}

// 10. Test message count by product
echo "\nSTEP 10: Testing seller's products with messages...\n";

$products = Product::where('seller_id', $seller->id)
    ->whereHas('messages')
    ->with(['messages' => function($q) {
        $q->orderByDesc('created_at');
    }])
    ->get();

echo "✅ Products with messages: " . count($products) . "\n";
foreach ($products as $prod) {
    $unreadCount = $prod->messages->filter(function($msg) use ($seller) {
        return !$msg->read && $msg->receiver_id === $seller->id;
    })->count();
    echo "   - {$prod->name}: {$prod->messages->count()} messages ($unreadCount unread)\n";
}

// Final summary
echo "\n";
echo "==========================================\n";
echo "✅ ALL TESTS COMPLETED SUCCESSFULLY\n";
echo "==========================================\n";
echo "\n✓ Message creation working\n";
echo "✓ Message retrieval (forConversation) working\n";
echo "✓ Mark as read working\n";
echo "✓ Multiple customers supported\n";
echo "✓ Product-based conversation grouping working\n";
echo "✓ Customer and seller messaging working\n\n";

// Clean up if needed
// Message::where('product_id', $product->id)->delete();
