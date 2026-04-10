<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = $user->ordersAsSeller()->where('status', '<>', 'review');

        // Handle status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        } else {
            // By default, exclude cancelled orders to keep the list clean
            $query->where('status', '<>', 'cancelled');
        }

        $orders = $query->with('customer', 'items.product.images')
            ->latest()
            ->paginate(10);

        return view('seller.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->seller_id !== Auth::id() || $order->status === 'review') {
            abort(403);
        }

        $order->load('customer', 'items.product.images', 'payment');
        return view('seller.orders.show', compact('order'));
    }

    public function confirm(Request $request, Order $order)
    {
        // Allow confirmation after payment (paid) or for cash orders (pending)
        if ($order->seller_id !== Auth::id() || !in_array($order->status, ['pending', 'paid', 'processing'])) {
            abort(403);
        }

        $order->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        \App\Models\OrderNotification::create([
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'message' => "Your order {$order->order_number} has been confirmed.",
        ]);

        return back()->with('success', 'Order confirmed');
    }

    public function cancel(Request $request, Order $order)
    {
        // Allow seller to cancel if it hasn't shipped yet
        if ($order->seller_id !== Auth::id() || !in_array($order->status, ['pending', 'paid', 'processing', 'confirmed'])) {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function() use ($order, $validated) {
            // 1. Handle Refund if order was already paid
            if ($order->payment_status === 'paid') {
                $customer = $order->customer;
                $wallet = $customer->wallet;
                
                if ($wallet) {
                    $refundAmount = $order->total_amount;
                    
                    // Add balance back to customer wallet
                    $wallet->increment('balance', $refundAmount);
                    $wallet->increment('total_received', $refundAmount);
                    
                    // Record refund transaction
                    \App\Models\WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'credit',
                        'amount' => $refundAmount,
                        'description' => "Refund for cancelled order #{$order->id} (Cancelled by Seller)",
                        'order_id' => $order->id,
                        'reference_type' => 'refund',
                        'status' => 'completed'
                    ]);
                    
                    $order->payment_status = 'refunded';
                }
            }

            // 2. Update order status
            $order->update([
                'status' => 'cancelled',
                'cancellation_reason' => $validated['reason'],
                'cancelled_at' => now(),
            ]);

            // 3. Restore stock
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            // 4. Notify customer
            \App\Models\OrderNotification::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'message' => "Your order {$order->id} has been cancelled by the seller. Reason: {$validated['reason']}. If you paid, funds have been refunded to your wallet.",
            ]);
        });

        return back()->with('success', 'Order cancelled and refunded if necessary');
    }

    public function ship(Request $request, Order $order)
    {
        if ($order->seller_id !== Auth::id() || $order->status !== 'confirmed') {
            abort(403);
        }

        $order->update([
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);

        // notify customer
        \App\Models\OrderNotification::create([
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'message' => "Your order {$order->order_number} has been shipped.",
        ]);

        return back()->with('success', 'Order marked as shipped');
    }

    /**
     * Mark an order as delivered. Only valid when shipped.
     */
    public function deliver(Request $request, Order $order)
    {
        if ($order->seller_id !== Auth::id() || $order->status !== 'shipped') {
            abort(403);
        }

        $order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        // notify customer
        \App\Models\OrderNotification::create([
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'message' => "Your order {$order->order_number} has been delivered.",
        ]);

        return back()->with('success', 'Order marked as delivered');
    }

    /**
     * Allow seller to permanently remove a cancelled order from their list.
     */
    public function destroy(Order $order)
    {
        if ($order->seller_id !== Auth::id()) {
            abort(403);
        }
        
        if ($order->status !== 'cancelled') {
            return back()->with('error', 'Only cancelled orders can be deleted. Please cancel the order first.');
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($order) {
            // Delete all related records explicitly
            $order->payment()->delete();
            $order->items()->delete();
            $order->notifications()->delete();
            $order->walletTransactions()->delete();
            $order->delete();
        });

        return redirect()->route('seller.orders.index')->with('success', 'Order removed permanently');
    }
}
