<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SellerController extends Controller
{
    public function index()
    {
        $sellers = User::sellers()->paginate(15);
        return view('admin.sellers.index', compact('sellers'));
    }

    public function create()
    {
        return view('admin.sellers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        $seller = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'role' => 'seller',
            'is_active' => true,
        ]);

        EWallet::create([
            'user_id' => $seller->id,
            'balance' => 0,
            'total_received' => 0,
            'total_spent' => 0,
        ]);

        return redirect()->route('admin.sellers.index')->with('success', 'Seller created');
    }

    public function edit(User $seller)
    {
        if (!$seller->isSeller()) {
            abort(404);
        }

        return view('admin.sellers.edit', compact('seller'));
    }

    public function update(Request $request, User $seller)
    {
        if (!$seller->isSeller()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $seller->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $seller->update($validated);

        return redirect()->route('admin.sellers.index')->with('success', 'Seller updated');
    }

    public function destroy(User $seller)
    {
        if (!$seller->isSeller()) {
            abort(404);
        }

        // 1. Delete Products and their images
        $seller->products()->each(function ($product) {
            $product->images()->delete();
            $product->reviews()->delete();
            $product->cartItems()->delete();
            $product->wishlistItems()->delete();
            $product->delete();
        });

        // 2. Delete Personal/Business Data
        $seller->sellerCategories()->delete();
        $seller->addresses()->delete();
        $seller->verifiedDevices()->delete();
        $seller->securityAudits()->delete();
        $seller->notifications()->delete();
        $seller->messagesSent()->delete();
        $seller->messagesReceived()->delete();

        // 3. Handle Orders as Seller
        $seller->ordersAsSeller()->each(function ($order) use ($seller) {
            // If order is in progress, cancel it and restore stock
            if (in_array($order->status, ['pending', 'review', 'confirmed', 'paid', 'processing'])) {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
                
                // Notify customer that order was cancelled due to seller deletion
                if ($order->customer) {
                    \App\Models\OrderNotification::create([
                        'order_id' => $order->id,
                        'customer_id' => $order->customer_id,
                        'message' => "Order #{$order->order_number} has been cancelled because the seller account was deleted by administration.",
                    ]);
                    
                    // Also send a direct message if possible
                    $firstItem = $order->items->first();
                    if ($firstItem && $firstItem->product_id) {
                        \App\Models\Message::create([
                            'sender_id' => Auth::id(), // Admin
                            'receiver_id' => $order->customer_id,
                            'product_id' => $firstItem->product_id,
                            'message' => "Order #{$order->order_number} was cancelled as the seller account was removed.",
                        ]);
                    }
                }
            }

            $order->items()->delete();
            $order->notifications()->delete();
            $order->walletTransactions()->delete();
            if ($order->payment) {
                $order->payment->delete();
            }
            $order->delete();
        });

        // 4. Handle Orders as Customer (In case seller bought things)
        $seller->ordersAsCustomer()->each(function ($order) use ($seller) {
            // Same logic for restoration if they were a customer
            if (in_array($order->status, ['pending', 'review', 'confirmed', 'paid', 'processing'])) {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
            }

            $order->items()->delete();
            $order->notifications()->delete();
            $order->walletTransactions()->delete();
            if ($order->payment) {
                $order->payment->delete();
            }
            $order->delete();
        });

        // 5. Delete Wallet
        if ($seller->wallet) {
            $seller->wallet->delete();
        }

        // 6. Finally delete the seller
        $seller->delete();

        return back()->with('success', 'Seller and all associated data deleted permanently');
    }
}
