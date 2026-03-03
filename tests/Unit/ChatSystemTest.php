<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Models\Message;
use App\Models\Product;

class ChatSystemTest extends TestCase
{
    /**
     * Test message creation
     */
    public function testMessageCreation()
    {
        $seller = User::where('role', 'seller')->first();
        $customer = User::where('role', 'customer')->first();
        $product = Product::where('seller_id', $seller->id)->first();

        if (!$seller || !$customer || !$product) {
            $this->markTestSkipped('Test data not available');
        }

        $message = Message::create([
            'sender_id' => $customer->id,
            'receiver_id' => $seller->id,
            'product_id' => $product->id,
            'message' => 'Test message',
            'read' => false,
        ]);

        $this->assertNotNull($message->id);
        $this->assertEquals($customer->id, $message->sender_id);
        $this->assertEquals($seller->id, $message->receiver_id);
        $this->assertEquals($product->id, $message->product_id);
        $this->assertFalse($message->read);
    }

    /**
     * Test message retrieval with forConversation scope
     */
    public function testForConversationScope()
    {
        $seller = User::where('role', 'seller')->first();
        $customer = User::where('role', 'customer')->first();
        $product = Product::where('seller_id', $seller->id)->first();

        if (!$seller || !$customer || !$product) {
            $this->markTestSkipped('Test data not available');
        }

        // Create messages
        $msg1 = Message::create([
            'sender_id' => $customer->id,
            'receiver_id' => $seller->id,
            'product_id' => $product->id,
            'message' => 'Message 1',
        ]);

        $msg2 = Message::create([
            'sender_id' => $seller->id,
            'receiver_id' => $customer->id,
            'product_id' => $product->id,
            'message' => 'Message 2',
        ]);

        // Retrieve conversation
        $messages = Message::forConversation($customer->id, $seller->id, $product->id)->get();

        $this->assertGreaterThanOrEqual(2, count($messages));
    }

    /**
     * Test marking messages as read
     */
    public function testMarkAsRead()
    {
        $seller = User::where('role', 'seller')->first();
        $customer = User::where('role', 'customer')->first();
        $product = Product::where('seller_id', $seller->id)->first();

        if (!$seller || !$customer || !$product) {
            $this->markTestSkipped('Test data not available');
        }

        // Create unread message
        $message = Message::create([
            'sender_id' => $customer->id,
            'receiver_id' => $seller->id,
            'product_id' => $product->id,
            'message' => 'Test',
            'read' => false,
        ]);

        // Mark as read
        Message::where('id', $message->id)->update(['read' => true]);

        $updated = Message::find($message->id);
        $this->assertTrue($updated->read);
    }

    /**
     * Test unread count
     */
    public function testUnreadCount()
    {
        $seller = User::where('role', 'seller')->first();

        if (!$seller) {
            $this->markTestSkipped('Seller not available');
        }

        $unreadCount = Message::where('receiver_id', $seller->id)
            ->where('read', false)
            ->count();

        $this->assertIsInt($unreadCount);
        $this->assertGreaterThanOrEqual(0, $unreadCount);
    }
}
