<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Payment;
use App\Models\SystemFee;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RiskAssessmentService;
use App\Models\SecurityAudit;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $orders = $user->ordersAsCustomer()
            ->with('seller', 'items')
            ->latest()
            ->paginate(10);

        // check for any unread notifications for this customer
        $unread = \App\Models\OrderNotification::where('customer_id', $user->id)
            ->where('is_read', false)
            ->get();

        if ($unread->isNotEmpty()) {
            // flash so view can display them
            session()->flash('order_notifications', $unread->toArray());
            // mark as read
            \App\Models\OrderNotification::whereIn('id', $unread->pluck('id'))->update(['is_read' => true]);
        }

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->customer_id !== Auth::id()) {
            abort(403);
        }

        $order->load('items.product.images', 'seller', 'payment');

        // show any notifications specifically for this order
        $orderNotifications = \App\Models\OrderNotification::where('order_id', $order->id)
            ->where('customer_id', Auth::id())
            ->where('is_read', false)
            ->get();

        if ($orderNotifications->isNotEmpty()) {
            session()->flash('order_notifications', $orderNotifications->toArray());
            \App\Models\OrderNotification::whereIn('id', $orderNotifications->pluck('id'))->update(['is_read' => true]);
        }

        return view('orders.show', compact('order'));

    }

    public function paymentHistory()
    {
        /** @var User $user */
        $user = Auth::user();

        // Only show successful PayPal payments for this customer.
        $payments = \App\Models\Payment::where('payment_method', 'paypal')
            ->where('status', 'completed')
            ->whereHas('order', function ($q) use ($user) {
                $q->where('customer_id', $user->id);
            })
            ->with('order')
            ->latest()
            ->paginate(15);

        return view('payments.history', compact('payments'));
    }

    public function checkout()
    {
        /** @var User $user */
        $user = Auth::user();

        // Check if this is "Buy Now" from product page
        if (request()->has('product_id')) {
            $product = \App\Models\Product::findOrFail(request('product_id'));
            
            if ($product->seller_id === Auth::id()) {
                return redirect()->route('products.index')->with('error', 'You cannot purchase your own products.');
            }
            
            $quantity = request('quantity', 1);

            // Create temporary cart item data for checkout
            $items = collect([
                (object)[
                    'product' => $product,
                    'quantity' => $quantity,
                    'product_id' => $product->id
                ]
            ]);
        } else {
            // Get items from cart - either selected items or all items
            $cart = $user->cart;

            if (!$cart || $cart->items->isEmpty()) {
                return redirect()->route('cart.index')->with('error', 'Cart is empty');
            }

            // Get selected item IDs from request
            $selectedItemIds = request('item_ids', []);
            
            if (!empty($selectedItemIds)) {
                // Only get selected items
                $items = $cart->items()
                    ->whereIn('id', $selectedItemIds)
                    ->with('product.seller', 'product.images')
                    ->get();
            } else {
                // If no selection, get all items
                $items = $cart->items()->with('product.seller', 'product.images')->get();
            }

            if ($items->isEmpty()) {
                return redirect()->route('cart.index')->with('error', 'Please select at least one item');
            }
        }

        $addresses = $user->addresses;
        $defaultAddress = $addresses->where('is_default', true)->first();

        $subtotal = $items->sum(function ($item) {
            return $item->product->getDiscountedPrice() * $item->quantity;
        });

        $shippingFee = SystemFee::first()?->shipping_fee_default ?? 0;
        $total = $subtotal + $shippingFee;

        return view('checkout.index', compact(
            'items',
            'addresses',
            'defaultAddress',
            'subtotal',
            'shippingFee',
            'total'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_name' => 'nullable|string|max:255',
            'recipient_phone' => 'nullable|string|max:20',
            'delivery_address' => 'nullable|string',
            'payment_method' => 'required|in:cod,online',
            'address_id' => 'nullable|exists:customer_addresses,id',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $requiresManualReview = false;

        Log::info("Checkout process started for user: " . $user->id);

        // Get address information - either from saved address or new address
        if ($request->has('address_id') && $validated['address_id']) {
            $address = \App\Models\CustomerAddress::findOrFail($validated['address_id']);
            if ($address->customer_id !== $user->id) {
                abort(403);
            }
            $recipientName = $address->recipient_name;
            $recipientPhone = $address->recipient_phone;
            $deliveryAddress = $address->address;
        } else {
            // New address
            if (!$validated['recipient_name'] || !$validated['recipient_phone'] || !$validated['delivery_address']) {
                return back()->with('error', 'Please provide delivery address information');
            }
            $recipientName = $validated['recipient_name'];
            $recipientPhone = $validated['recipient_phone'];
            $deliveryAddress = $validated['delivery_address'];

            // Store this organically new address into global Customer Addresses
            \App\Models\CustomerAddress::create([
                'customer_id' => $user->id,
                'recipient_name' => $recipientName,
                'recipient_phone' => $recipientPhone,
                'address' => $deliveryAddress,
                'is_default' => $user->addresses()->count() === 0,
            ]);
        }

        // ==========================================
        // AI Risk Scoring Perimeter (Applies to Both Buy Now & Cart)
        // ==========================================
        $enableAiMfa = env('ENABLE_AI_MFA', true);

        // Calculate total amount intent to score
        $intentTotalAmount = 0;
        if ($request->has('product_id')) {
            $product = \App\Models\Product::findOrFail($request->input('product_id'));
            $quantity = $request->input('quantity', 1);
            $subtotal = $product->getDiscountedPrice() * $quantity;
            $shippingFee = SystemFee::first()?->shipping_fee_default ?? 0;
            $intentTotalAmount = $subtotal + $shippingFee;
        } else {
            $cart = $user->cart;
            if (!$cart || $cart->items->isEmpty()) { return back()->with('error', 'Cart is empty'); }
            $selectedItemIds = $request->input('item_ids', []);
            $items = empty($selectedItemIds) ? $cart->items()->with('product')->get() : $cart->items()->whereIn('id', $selectedItemIds)->with('product')->get();
            if ($items->isEmpty()) { return back()->with('error', 'No items selected'); }
            
            $subtotal = $items->sum(function ($item) { return $item->product->getDiscountedPrice() * $item->quantity; });
            $shippingFee = SystemFee::first()?->shipping_fee_default ?? 0;
            $intentTotalAmount = $subtotal + $shippingFee; 
        }

        Log::info("Intent total amount calculated: " . $intentTotalAmount);

        // --- 1. MANDATORY: Customer Profile Check for High-Value Transactions ---
        // For orders over a certain threshold (e.g., $1000), require phone and base address
        if ($intentTotalAmount > 1000) {
            if (empty($user->phone) || $user->addresses()->count() === 0 || !$user->identity_image) {
                $reason = "For high-value transactions, please complete your profile first:";
                if (empty($user->phone)) $reason .= " (Phone Number missing)";
                if ($user->addresses()->count() === 0) $reason .= " (Default Address missing)";
                if (!$user->identity_image) $reason .= " (FaceID Identity not registered)";
                
                return redirect()->route('profile.show')->with('error', $reason);
            }
        }

        if ($enableAiMfa) {
            Log::info("Calling RiskAssessmentService...");
            $riskService = app(RiskAssessmentService::class);
            $riskResult = $riskService->analyze($user, $intentTotalAmount, $validated['payment_method'], $request->input('latitude'), $request->input('longitude'));
            
            if ($riskResult) {
                $suggestion = $riskResult['suggestion'] ?? 'allow';
                $score = $riskResult['risk_score'] ?? 0;
                $level = $riskResult['level'] ?? 'low';
            } else {
                $suggestion = 'otp';
                $score = 50.0;
                $level = 'medium';
                $riskResult = [
                    'explanation' => [
                        'score_breakdown' => ['Risk scoring service returned null; using default MFA fallback.'],
                        'input' => ['amount' => $intentTotalAmount],
                    ],
                ];
            }
        } else {
            // Static MFA - Non AI branch
            $suggestion = 'otp';
            $score = 50.0;
            $level = 'medium';
            $riskResult = [
                'explanation' => [
                    'score_breakdown' => ['Static (no-AI) MFA mode in use; default risk score applied.'],
                    'input' => ['amount' => $intentTotalAmount],
                ],
            ];
        }

        Log::info("Risk score calculated: " . $score . " Suggestion: " . $suggestion);

        $isMfaVerified = Session::get('mfa_verified') === true;

        // Determine final result for the audit record
        $auditResult = 'pending';
        if ($suggestion === 'allow' || $isMfaVerified) {
            $auditResult = 'success';
        } elseif ($suggestion === 'block') {
            $auditResult = 'blocked';
        }

        // Centralized Audit Log Create
        $audit = SecurityAudit::create([
            'user_id' => $user->id,
            'action' => 'checkout',
            'amount' => $intentTotalAmount,
            'risk_score' => $score,
            'level' => $level,
            'suggestion' => $suggestion,
            'result' => $auditResult,
            'metadata' => [
                'ai_enabled' => $enableAiMfa,
                'risk_explanation' => $riskResult['explanation'] ?? null,
                'already_verified' => $isMfaVerified,
            ],
        ]);
        $auditId = $audit->id;
        Session::put('pending_audit_id', $auditId);

        $requiresManualReview = $suggestion === 'block';

        // Check if we need to trigger MFA challenge
        // We only skip MFA if they are already verified AND it's NOT a critical risk (which requires admin review)
        if (!$isMfaVerified && !$requiresManualReview) {
            if ($suggestion === 'faceid' || $suggestion === 'otp') {
                Log::info("MFA required for checkout. Sending OTP...");
                $otp = rand(100000, 999999);
                Session::put('expected_otp', $otp);
                
                // CRITICAL: We need the REAL request data, not the instance
                $requestData = $request->all();
                Session::put('pending_checkout_request', $requestData);
                
                try {
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\MfaOtpMail($otp));
                } catch (\Exception $e) {
                    Log::error("Failed to send Checkout MFA OTP: " . $e->getMessage());
                }
                return redirect()->route('otp.verify');
            }
        }
        
        if ($isMfaVerified) {
            Log::info("Checkout proceeding with verified MFA session.");
        }
        
        Log::info("Proceeding to order creation. Manual review: " . ($requiresManualReview ? 'Yes' : 'No'));

        // Check if this is "Buy Now" from product page
        if ($request->has('product_id')) {
            $product = \App\Models\Product::findOrFail($request->input('product_id'));
            
            if ($product->seller_id === Auth::id()) {
                return back()->with('error', 'You cannot purchase your own products.');
            }
            
            $quantity = $request->input('quantity', 1);
            if ($product->stock < $quantity) {
                return back()->with('error', "Only {$product->stock} units of {$product->name} are available.");
            }
            $subtotal = $product->getDiscountedPrice() * $quantity;
            $shippingFee = SystemFee::first()?->shipping_fee_default ?? 0;
            $totalAmount = $subtotal + $shippingFee;

            $orderStatus = $requiresManualReview ? 'review' : 'pending';

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $user->id,
                'seller_id' => $product->seller_id,
                'security_audit_id' => $auditId ?? null,
                'status' => $orderStatus,
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'recipient_name' => $recipientName,
                'recipient_phone' => $recipientPhone,
                'delivery_address' => $deliveryAddress,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_price' => $product->getDiscountedPrice(),
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ]);

            $product->decrement('stock', $quantity);

            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
                'amount' => $totalAmount,
            ]);

            if ($requiresManualReview) {
                return redirect()->route('support.contact', ['order_id' => $order->id]);
            }

            if ($validated['payment_method'] === 'online') {
                // Keep mfa_verified for PayPalController
                return redirect()->route('paypal.create', $order)->with('success', 'Order placed. Proceeding to PayPal.');
            }

            if (Session::has('mfa_verified')) {
                Session::forget('mfa_verified');
            }
            return redirect()->route('orders.show', $order)->with('success', 'Order placed successfully');
        }

        // Original cart-based order logic
        $cart = $user->cart;
        if (!$cart || $cart->items->isEmpty()) { return back()->with('error', 'Cart is empty'); }

        $selectedItemIds = $request->input('item_ids', []);
        $items = empty($selectedItemIds) ? $cart->items()->with('product')->get() : $cart->items()->whereIn('id', $selectedItemIds)->with('product')->get();
        if ($items->isEmpty()) { return back()->with('error', 'No items selected'); }

        // Stock validation
        foreach ($items as $item) {
            if ($item->product->stock < $item->quantity) {
                return back()->with('error', "Insufficient stock for {$item->product->name}.");
            }
        }

        $groupedItems = [];
        foreach ($items as $item) {
            $groupedItems[$item->product->seller_id][] = $item;
        }

        $lastOrder = null;
        foreach ($groupedItems as $sellerId => $sellerItems) {
            $sellerItemsCollection = collect($sellerItems);
            $subtotal = $sellerItemsCollection->sum(fn($i) => $i->product->getDiscountedPrice() * $i->quantity);
            $shippingFee = SystemFee::first()?->shipping_fee_default ?? 0;
            $totalAmount = $subtotal + $shippingFee;

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $user->id,
                'seller_id' => $sellerId,
                'security_audit_id' => $auditId ?? null,
                'status' => $requiresManualReview ? 'review' : 'pending',
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'recipient_name' => $recipientName,
                'recipient_phone' => $recipientPhone,
                'delivery_address' => $deliveryAddress,
            ]);
            $lastOrder = $order;

            foreach ($sellerItemsCollection as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product->id,
                    'product_name' => $cartItem->product->name,
                    'product_price' => $cartItem->product->getDiscountedPrice(),
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $cartItem->product->getDiscountedPrice() * $cartItem->quantity,
                ]);
                $cartItem->product->decrement('stock', $cartItem->quantity);
            }

            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
                'amount' => $totalAmount,
            ]);
        }

        if (!empty($selectedItemIds)) {
            $cart->items()->whereIn('id', $selectedItemIds)->delete();
        } else {
            $cart->items()->delete();
        }

        if ($requiresManualReview && $lastOrder) {
            return redirect()->route('support.contact', ['order_id' => $lastOrder->id]);
        }

        if ($validated['payment_method'] === 'online' && $lastOrder) {
            // Keep mfa_verified session for PayPalController to trust this checkout-level verification
            return redirect()->route('paypal.create', $lastOrder)->with('success', 'Order placed. Proceeding to PayPal.');
        }

        // Clean up verification flag for non-online payment flows
        if (Session::has('mfa_verified')) {
            Session::forget('mfa_verified');
        }
        return redirect()->route('orders.index')->with('success', 'Orders placed successfully!');
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->customer_id !== Auth::id()) { abort(403); }
        if (!$order->canBeCancelled()) { return back()->with('error', 'Order cannot be cancelled'); }
        
        \Illuminate\Support\Facades\DB::transaction(function() use ($order) {
            // 1. Handle Refund if order was already paid
            if ($order->payment_status === 'paid') {
                $user = Auth::user();
                $wallet = $user->wallet;
                
                if ($wallet) {
                    $refundAmount = $order->total_amount;
                    
                    // Add balance back to user wallet
                    $wallet->increment('balance', $refundAmount);
                    $wallet->increment('total_received', $refundAmount);
                    
                    // Record refund transaction
                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'credit',
                        'amount' => $refundAmount,
                        'description' => "Refund for cancelled order #{$order->order_number}",
                        'order_id' => $order->id,
                        'reference_type' => 'refund',
                        'status' => 'completed'
                    ]);
                    
                    $order->payment_status = 'refunded';
                }
            }

            // 2. Update order status
            $order->status = 'cancelled';
            $order->cancelled_at = now();
            $order->save();
            
            // 3. Return stock back to product
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }
        });
        
        return back()->with('success', 'Order cancelled successfully. If paid, funds have been refunded to your wallet.');
    }

    public function destroy(Order $order)
    {
        if ($order->customer_id !== Auth::id()) { abort(403); }
        if ($order->status !== 'cancelled') { return back()->with('error', 'Only cancelled orders can be deleted'); }
        
        // Use database transaction for safe deletion
        \Illuminate\Support\Facades\DB::transaction(function() use ($order) {
            // Delete all related records explicitly to prevent foreign key issues
            $order->payment()->delete();
            $order->items()->delete();
            $order->notifications()->delete();
            $order->walletTransactions()->delete();
            $order->delete();
        });
        
        return redirect()->route('orders.index')->with('success', 'Order removed permanently');
    }

    public function payment(Request $request, Order $order)
    {
        if ($order->customer_id !== Auth::id()) { abort(403); }
        if ($order->payment_status === 'paid') { return back()->with('error', 'Order already paid'); }

        // If order was verified by admin, it MUST pass FaceID before PayPal
        if ($order->status === 'verified_by_admin' && !Session::get("order_{$order->id}_face_verified")) {
            return back()->with('error', 'Biometric verification required to proceed with this high-risk transaction.');
        }

        return redirect()->route('paypal.create', $order);
    }

    /**
     * Final biometric verification for high-risk orders approved by admin.
     */
    public function verifyFaceID(Request $request, Order $order)
    {
        if ($order->customer_id !== Auth::id()) { abort(403); }
        if ($order->status !== 'verified_by_admin') {
            return response()->json(['success' => false, 'reason' => 'Invalid order status for FaceID verification.'], 422);
        }

        $request->validate([
            'face_data' => 'required|string',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $faceService = app(\App\Services\FaceVerificationService::class);
        
        // Use the user's registered identity image for comparison
        $result = $faceService->verify($request->input('face_data'), $user->identity_image, false, $user->id);

        if ($result['success']) {
            // Mark as verified for this specific order in this session
            Session::put("order_{$order->id}_face_verified", true);
            
            // Move order to pending so it can be paid
            $order->update(['status' => 'pending']);

            return response()->json([
                'success' => true,
                'message' => 'Biometric verification successful. Proceeding to payment...',
                'redirect' => route('paypal.create', $order)
            ]);
        }

        // FAILURE: Revert order back to 'review' status and notify admin
        $order->update(['status' => 'review']);
        
        // Update the security audit if it exists
        if ($order->securityAudit) {
            $audit = $order->securityAudit;
            $meta = $audit->metadata ?? [];
            $meta['faceid_failure'] = [
                'attempted_at' => now()->toDateTimeString(),
                'reason' => $result['reason'] ?? 'Face match failed',
            ];
            $audit->metadata = $meta;
            $audit->save();
        }

        // Create a critical notification for Admin/Support
        \App\Models\OrderNotification::create([
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'message' => 'CRITICAL: FaceID verification failed for a verified high-risk order. Order reverted to review.',
        ]);

        return response()->json([
            'success' => false,
            'reason' => 'Biometric match failed. For your security, this order has been reverted to manual review.',
            'redirect' => route('orders.show', $order)
        ]);
    }

    public function buyAgain(Order $order)
    {
        if ($order->customer_id !== Auth::id()) { abort(403); }
        /** @var User $user */
        $user = Auth::user();
        $cart = $user->cart ?? \App\Models\Cart::create(['customer_id' => $user->id]);
        foreach ($order->items as $item) {
            if (!$item->product || $item->product->stock <= 0) continue;
            $quantityToAdd = min($item->quantity, $item->product->stock);
            $existing = $cart->items()->where('product_id', $item->product_id)->first();
            if ($existing) {
                $existing->update(['quantity' => min($existing->quantity + $quantityToAdd, $item->product->stock)]);
            } else {
                \App\Models\CartItem::create(['cart_id' => $cart->id, 'product_id' => $item->product_id, 'quantity' => $quantityToAdd]);
            }
        }
        return redirect()->route('cart.index')->with('success', 'Items added to cart.');
    }

    private function generateOrderNumber()
    {
        return 'ORD' . date('Ymd') . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
    }

    public function shipperUpdateStatus(Request $request)
    {
        if ($request->input('secret_key') !== 'LUXGUARD_SECRET_2026') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $data = $request->validate(['order_id' => 'required|integer', 'secret_key' => 'required|string']);
        $order = Order::find($data['order_id']);
        if (!$order) { return response()->json(['message' => 'Order not found'], 404); }
        $order->status = 'delivered';
        $order->delivered_at = now();
        $order->save();
        try {
            $tx = WalletTransaction::where('order_id', $order->id)->where('type', 'credit')->where('status', 'pending')->first();
            if ($tx) {
                $tx->status = 'completed';
                $tx->save();
                if ($tx->wallet) { $tx->wallet->adjustBalance($tx->amount); }
            }
        } catch (\Exception $e) {
            Log::error('Error finalizing seller payout on delivery', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }
        return response()->json(['message' => 'Order status updated']);
    }
}
