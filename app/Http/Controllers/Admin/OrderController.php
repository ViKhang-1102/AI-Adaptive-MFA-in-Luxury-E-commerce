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
        $query = Order::with('customer', 'seller', 'items.product.images')->latest();

        // Allow filtering by status for the standard order list
        if ($request->query('status')) {
            if ($request->query('status') !== 'all') {
                $query->where('status', $request->query('status'));
            }
        } else {
            // Default: Hide handled orders (cancelled, delivered, verified_by_admin if paid)
            // Show pending, review, confirmed, shipped, paid, processing
            $query->whereIn('status', ['pending', 'review', 'confirmed', 'shipped', 'paid', 'processing', 'verified_by_admin']);
        }

        $orders = $query->paginate(15)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    public function pending()
    {
        // Show the list of orders needing manual review
        $query = Order::with('customer', 'seller', 'items.product.images', 'securityAudit')
            ->where(function ($q) {
                $q->where('status', 'review')
                    ->orWhereHas('securityAudit', function ($q2) {
                        $q2->where('result', 'pending');
                    });
            })
            ->latest();

        $orders = $query->paginate(15)->withQueryString();
        return view('admin.orders.index', [
            'orders' => $orders,
            'isPendingVerifications' => true
        ]);
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
                // Prefer cache keyed by user ID (user_{id}.json)
                $userId = null;
                if (preg_match('/user[_-]?(\d+)/', basename($order->customer->identity_image), $m)) {
                    $userId = (int)$m[1];
                }

                if ($userId) {
                    $cachePath = storage_path('app/face_verify_cache/user_' . $userId . '.json');
                } else {
                    $hash = hash_file('sha256', $identityPath);
                    $cachePath = storage_path('app/face_verify_cache/' . $hash . '.json');
                }

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

        // Update order status so customer can proceed to payment
        $order->status = 'verified_by_admin';
        $order->payment_status = 'pending';
        $order->save();

        // Note: We do not create a payment record here. The customer must complete payment via PayPal.

        // Notify customer the order is now verified and can be paid
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

