<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SecurityAudit;
use App\Models\SystemFee;
use App\Models\WalletTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // If this is the pending-verifications filter, always redirect to the dedicated pending queue.
        // This prevents the admin list view from showing a full order list when the intent is manual review.
        if ($request->query('filter') === 'pending_verifications') {
            return redirect()->route('admin.orders.pending');
        }

        $query = Order::with('customer', 'seller', 'items.product.images')->latest();

        // Allow filtering by status for the standard order list
        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        $orders = $query->paginate(15)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    public function pending()
    {
        // Show one pending order at a time (oldest first). If none are pending, show an empty state.
        $order = Order::with('customer', 'seller', 'items.product.images', 'securityAudit')
            ->where(function ($q) {
                $q->where('status', 'review')
                    ->orWhereHas('securityAudit', function ($q2) {
                        $q2->where('result', 'pending');
                    });
            })
            ->oldest('created_at')
            ->first();

        if ($order) {
            return redirect()->route('admin.orders.show', $order);
        }

        return view('admin.orders.pending');
    }

    public function show(Order $order)
    {
        $order->load('items.product.images', 'customer', 'seller', 'payment', 'securityAudit');

        // Fetch associated security audit record for display
        $securityAudit = $order->securityAudit;

        // If user has an identity image, attempt to load face verify cache so admins can review landmark distances.
        $faceCacheInfo = null;
        if ($order->customer && $order->customer->identity_image) {
            $identityPath = storage_path('app/public/' . $order->customer->identity_image);
            if (file_exists($identityPath)) {
                $hash = hash_file('sha256', $identityPath);
                $cachePath = storage_path('app/face_verify_cache/' . $hash . '.json');
                if (file_exists($cachePath)) {
                    $faceCacheInfo = json_decode(file_get_contents($cachePath), true);
                }
            }
        }

        return view('admin.orders.show', compact('order', 'securityAudit', 'faceCacheInfo'));
    }

    public function verify(Request $request, Order $order)
    {
        // Only allow verifying orders that are flagged for manual review
        if ($order->status !== 'review') {
            return $request->wantsJson()
                ? response()->json(['error' => 'This order does not require manual verification.'], 422)
                : back()->with('error', 'This order does not require manual verification.');
        }

        // Reset user risk score (unlock account) by marking the audit as approved
        if ($order->securityAudit) {
            $audit = $order->securityAudit;
            $audit->risk_score = 0;
            $audit->result = 'approved';
            $meta = $audit->metadata ?? [];
            $meta['manual_review'] = [
                'approved_at' => now()->toDateTimeString(),
                'approved_by' => Auth::id(),
            ];
            $audit->metadata = $meta;
            $audit->save();
        }

        // Update order status and payment info
        $order->status = 'pending';
        $order->payment_status = 'paid';
        $order->save();

        // Mark or create payment record
        $payment = $order->payment;
        if (!$payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => $order->payment_method,
                'status' => 'completed',
                'amount' => $order->total_amount,
                'transaction_id' => 'manual-' . $order->id . '-' . time(),
                'processed_at' => now(),
            ]);
        } else {
            $payment->status = 'completed';
            $payment->processed_at = now();
            $payment->transaction_id = $payment->transaction_id ?: ('manual-' . $order->id . '-' . time());
            $payment->save();
        }

        // Create wallet transactions (admin commission + pending seller payout)
        $total = $order->total_amount;
        $adminPercentage = SystemFee::getPlatformCommission();
        $sellerPercentage = 100 - $adminPercentage;
        $adminFee = round($total * ($adminPercentage / 100), 2);
        $sellerAmount = round($total * ($sellerPercentage / 100), 2);

        $adminWallet = User::where('role', 'admin')->first()?->wallet;
        if ($adminWallet) {
            $tx = WalletTransaction::create([
                'wallet_id' => $adminWallet->id,
                'order_id' => $order->id,
                'type' => 'credit',
                'amount' => $adminFee,
                'description' => "Platform commission from Order #{$order->id} ({$adminPercentage}%)",
                'status' => 'completed',
                'transaction_reference' => $payment->transaction_id,
            ]);

            try {
                $adminWallet->adjustBalance($adminFee);
            } catch (\Exception $e) {
                Log::error('Failed to adjust admin wallet balance', ['error' => $e->getMessage()]);
            }
        }

        $sellerWallet = $order->seller?->wallet;
        if ($sellerWallet) {
            WalletTransaction::create([
                'wallet_id' => $sellerWallet->id,
                'order_id' => $order->id,
                'type' => 'credit',
                'amount' => $sellerAmount,
                'description' => "Order #{$order->id} payment ({$sellerPercentage}%)",
                'status' => 'pending',
                'transaction_reference' => $payment->transaction_id,
            ]);
        }

        // Notify customer that order is approved and ready for payment
        \App\Models\OrderNotification::create([
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'message' => 'Your order has been verified by support. Please proceed to complete the payment.',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Order approved and customer notified.']);
        }

        return redirect()->route('admin.orders.pending')->with('success', 'Order has been approved and the customer has been notified.');
    }

    public function reject(Request $request, Order $order)
    {
        if ($order->status !== 'review') {
            return $request->wantsJson()
                ? response()->json(['error' => 'This order is not pending verification.'], 422)
                : back()->with('error', 'This order is not pending verification.');
        }

        // Restore stock
        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        $order->status = 'cancelled';
        $order->cancelled_at = now();
        $order->cancellation_reason = 'Security verification failed';
        $order->save();

        // Update the related security audit so it shows rejected
        if ($order->securityAudit) {
            $audit = $order->securityAudit;
            $audit->result = 'rejected';
            $meta = $audit->metadata ?? [];
            $meta['manual_review'] = [
                'rejected_at' => now()->toDateTimeString(),
                'rejected_by' => Auth::id(),
            ];
            $audit->metadata = $meta;
            $audit->save();
        }

        // Notify customer
        \App\Models\OrderNotification::create([
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'message' => 'Your order has been cancelled due to security concerns. Please contact support if you believe this is an error.',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.orders.pending')->with('success', 'Order rejected and stock restored. The customer has been notified.');
    }

    public function sendMessage(Request $request, Order $order)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $customer = $order->customer;
        if (!$customer) {
            return response()->json(['error' => 'Customer not found for this order.'], 404);
        }

        $productId = $order->items->first()?->product_id;

        $msg = \App\Models\Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $customer->id,
            'product_id' => $productId,
            'message' => $request->input('message'),
        ]);

        return response()->json(['success' => true, 'message' => $msg]);
    }
}

