<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function getMessages(Request $request, Product $product)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $otherUserId = $request->query('user_id');

        // Get conversation between two users for this product
        $messages = Message::forConversation(Auth::id(), $otherUserId, $product->id)->get();

        // Mark messages as read
        Message::where('receiver_id', Auth::id())
            ->where('sender_id', $otherUserId)
            ->where('product_id', $product->id)
            ->update(['read' => true]);

        return response()->json($messages);
    }

    public function sendMessage(Request $request, Product $product)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized - Please login'], 401);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'receiver_id' => 'required|numeric|exists:users,id',
        ], [
            'receiver_id.required' => 'Receiver ID is required',
            'receiver_id.exists' => 'The receiver does not exist',
            'message.required' => 'Message cannot be empty',
            'message.max' => 'Message cannot exceed 1000 characters',
        ]);

        // Verify receiver exists
        $receiver = User::find($validated['receiver_id']);
        if (!$receiver) {
            return response()->json(['error' => 'Receiver not found'], 404);
        }

        // Customers can only message the seller
        if (Auth::id() !== $product->seller_id) {
            // If sender is not seller, they can only message the seller
            if ($validated['receiver_id'] != $product->seller_id) {
                return response()->json(['error' => 'You can only message the seller'], 403);
            }
        }

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'product_id' => $product->id,
            'message' => $validated['message'],
        ]);

        return response()->json($message->load('sender'), 201);
    }

    public function markAsRead(Message $message)
    {
        if ($message->receiver_id !== Auth::id()) {
            abort(403);
        }

        $message->update(['read' => true]);
        return response()->json(['success' => true]);
    }

    /**
     * Customer inbox showing all conversations with sellers.
     */
    public function customerInbox()
    {
        $customerId = Auth::id();
        $messages = Message::where('sender_id', $customerId)
            ->orWhere('receiver_id', $customerId)
            ->with('sender', 'receiver', 'product')
            ->orderByDesc('created_at')
            ->get();

        $conversations = [];
        foreach ($messages as $msg) {
            $otherId = $msg->sender_id === $customerId ? $msg->receiver_id : $msg->sender_id;
            $key = $msg->product_id . '_' . $otherId;
            if (!isset($conversations[$key])) {
                $conversations[$key] = [
                    'product' => $msg->product,
                    'seller' => $msg->sender_id === $customerId ? $msg->receiver : $msg->sender,
                    'last_message' => $msg->message,
                    'last_message_at' => $msg->created_at,
                    'unread_count' => 0,
                ];
            }
            if (!$msg->read && $msg->receiver_id === $customerId) {
                $conversations[$key]['unread_count']++;
            }
        }

        // Sort by last_message_at
        usort($conversations, function($a, $b) {
            $aTime = $a['last_message_at'] ? $a['last_message_at']->timestamp : 0;
            $bTime = $b['last_message_at'] ? $b['last_message_at']->timestamp : 0;
            return $bTime - $aTime;
        });

        return view('customer.messages.index', ['conversations' => array_values($conversations)]);
    }

    /**
     * Conversation view for customer with a particular seller and product.
     */
    public function customerConversation(Product $product, User $other)
    {
        // Allow conversations with the product's seller or the admin (support)
        if ($product->seller_id !== $other->id && !$other->isAdmin()) {
            abort(404, 'Invalid seller for this product');
        }

        return view('customer.messages.conversation', compact('product', 'other'));
    }

    /**
     * Seller inbox showing all conversations for their products.
     */
    public function sellerInbox()
    {
        $sellerId = Auth::id();
        $messages = Message::whereHas('product', function($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })->with('sender', 'receiver', 'product')
          ->orderByDesc('created_at')
          ->get();

        $conversations = [];
        foreach ($messages as $msg) {
            $otherId = $msg->sender_id === $sellerId ? $msg->receiver_id : $msg->sender_id;
            $key = $msg->product_id . '_' . $otherId;
            if (!isset($conversations[$key])) {
                $conversations[$key] = [
                    'product' => $msg->product,
                    'other' => $msg->sender_id === $sellerId ? $msg->receiver : $msg->sender,
                    'last_message' => $msg->message,
                    'unread_count' => 0,
                ];
            }
            if (!$msg->read && $msg->receiver_id === $sellerId) {
                $conversations[$key]['unread_count']++;
            }
        }

        // Use new view with split layout
        return view('seller.messages.index_new', ['conversations' => array_values($conversations)]);
    }

    /**
     * Conversation view for seller with a particular customer and product.
     */
    public function sellerConversation(Product $product, User $other)
    {
        if ($product->seller_id !== Auth::id()) {
            abort(403);
        }

        // reuse the product message area but pass customer as recipient
        return view('seller.messages.conversation', compact('product', 'other'));
    }

    /**
     * Get unread message count for current user (API endpoint)
     */
    public function getUnreadCount()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $unreadCount = Message::where('receiver_id', Auth::id())
            ->where('read', false)
            ->count();

        return response()->json(['unread_count' => $unreadCount]);
    }

    /**
     * Get customers list for seller with unread count per customer
     */
    public function getCustomersList()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $sellerId = Auth::id();
        
        // Get all messages for this seller's products
        $messages = Message::whereHas('product', function($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })->with('sender', 'receiver', 'product')
          ->orderByDesc('created_at')
          ->get();

        // Group by customer
        $customers = [];
        foreach ($messages as $msg) {
            $customerId = $msg->sender_id === $sellerId ? $msg->receiver_id : $msg->sender_id;
            $customer = $msg->sender_id === $sellerId ? $msg->receiver : $msg->sender;
            
            if (!isset($customers[$customerId])) {
                $customers[$customerId] = [
                    'id' => $customerId,
                    'name' => $customer->name,
                    'avatar' => $customer->avatar ?? null,
                    'last_message' => $msg->message,
                    'last_message_at' => $msg->created_at,
                    'unread_count' => 0,
                ];
            }
            
            if (!$msg->read && $msg->receiver_id === $sellerId) {
                $customers[$customerId]['unread_count']++;
            }
        }

        // Sort by last_message_at
        usort($customers, function($a, $b) {
            return $b['last_message_at']->timestamp - $a['last_message_at']->timestamp;
        });

        return response()->json(array_values($customers));
    }

    /**
     * Get products list for a customer (for seller)
     */
    public function getCustomerProducts($customerId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $sellerId = Auth::id();
        
        // Get all products that have messages between seller and this customer
        $messages = Message::whereHas('product', function($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })
        ->where(function($q) use ($sellerId, $customerId) {
            $q->where(function($q) use ($sellerId, $customerId) {
                $q->where('sender_id', $sellerId)->where('receiver_id', $customerId);
            })->orWhere(function($q) use ($sellerId, $customerId) {
                $q->where('sender_id', $customerId)->where('receiver_id', $sellerId);
            });
        })
        ->with('product')
        ->orderByDesc('created_at')
        ->get();

        // Group by product
        $products = [];
        foreach ($messages as $msg) {
            $productId = $msg->product_id;
            
            if (!isset($products[$productId])) {
                $products[$productId] = [
                    'id' => $msg->product->id,
                    'name' => $msg->product->name,
                    'image' => $msg->product->image ?? null,
                    'last_message' => $msg->message,
                    'last_message_at' => $msg->created_at,
                    'unread_count' => 0,
                    'messages_count' => 0,
                ];
            }
            
            if (!$msg->read && $msg->receiver_id === $sellerId) {
                $products[$productId]['unread_count']++;
            }
            
            $products[$productId]['messages_count']++;
        }

        // Sort by last_message_at (newest first)
        usort($products, function($a, $b) {
            $aTime = $a['last_message_at'] ? $a['last_message_at']->timestamp : 0;
            $bTime = $b['last_message_at'] ? $b['last_message_at']->timestamp : 0;
            return $bTime - $aTime;
        });

        return response()->json(array_values($products));
    }
}
