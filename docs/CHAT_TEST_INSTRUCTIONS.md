<?php

// Test the chat system via Artisan
// Run: php artisan tinker @ then paste the code below

/*
use App\Models\User;
use App\Models\Message;
use App\Models\Product;

// Get test users
$seller = User::where('role', 'seller')->first();
$customer = User::where('role', 'customer')->first();
$product = Product::where('seller_id', $seller->id)->first();

if ($seller && $customer && $product) {
    // Test 1: Create message
    $msg = Message::create([
        'sender_id' => $customer->id,
        'receiver_id' => $seller->id,
        'product_id' => $product->id,
        'message' => 'Hi, does this work?',
    ]);
    echo "✓ Message created: " . $msg->id . "\n";
    
    // Test 2: Retrieve conversation
    $msgs = Message::forConversation($customer->id, $seller->id, $product->id)->get();
    echo "✓ Messages in conversation: " . count($msgs) . "\n";
    
    // Test 3: Mark as read
    Message::where('receiver_id', $seller->id)
        ->where('sender_id', $customer->id)
        ->where('product_id', $product->id)
        ->update(['read' => true]);
    echo "✓ Messages marked as read\n";
    
    // Test 4: Check unread count
    $unread = Message::where('receiver_id', $seller->id)->where('read', false)->count();
    echo "✓ Seller unread count: $unread\n";
} else {
    echo "Missing test data\n";
}
*/
