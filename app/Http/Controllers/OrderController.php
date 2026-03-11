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

        $user = Auth::user();
        $requiresManualReview = false;

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
        if (!Session::get('mfa_verified')) {
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
                // Note: Multi-seller shipping fees logic may be complex, but for risk assessment a rough estimate is fine
                $shippingFee = SystemFee::first()?->shipping_fee_default ?? 0;
                $intentTotalAmount = $subtotal + $shippingFee; 
            }

            if ($enableAiMfa) {
                $riskService = app(RiskAssessmentService::class);
                $riskResult = $riskService->analyze($user, $intentTotalAmount, $validated['payment_method']);
                if ($riskResult) {
                    $suggestion = $riskResult['suggestion'] ?? 'allow';
                    $score = $riskResult['risk_score'] ?? 0;
                    $level = $riskResult['level'] ?? 'low';
                } else {
                    Log::error("Risk Scoring API Offline or Timeout. Defaulting to Static MFA Fallback.");
                    $suggestion = 'otp';
                    $score = 50.0;
                    $level = 'medium';
                    $riskResult = [
                        'explanation' => [
                            'score_breakdown' => ['Risk scoring service unavailable; defaulting to static MFA risk score.'],
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

            // Centralized Audit Log Create
            $audit = SecurityAudit::create([
                'user_id' => $user->id,
                'action' => 'checkout',
                'amount' => $intentTotalAmount,
                'risk_score' => $score,
                'level' => $level,
                'suggestion' => $suggestion,
                'result' => ($suggestion === 'allow' ? 'success' : ($suggestion === 'block' ? 'blocked' : 'pending')),
                'metadata' => [
                    'ai_enabled' => $enableAiMfa,
                    'risk_explanation' => $riskResult['explanation'] ?? null,
                    'engine_input' => [
                        'amount' => $intentTotalAmount,
                        'ip' => $request->ip(),
                        'location' => $riskResult['explanation']['input']['location'] ?? 'Unknown',
                        'device' => substr(md5($request->header('User-Agent')), 0, 16),
                        'device_is_new' => Session::has('device_verified') ? false : true,
                    ],
                ],
            ]);
            $auditId = $audit->id;
            Session::put('pending_audit_id', $auditId);

            $requiresManualReview = $suggestion === 'block';

            if ($requiresManualReview) {
                Session::flash('error', "High risk detected. Your order requires manual review before it can be processed. Please contact support for help.");
            }

            if ($suggestion === 'faceid' || $suggestion === 'otp') {
                $otp = rand(100000, 999999);
                Session::put('expected_otp', $otp);
                Session::put('pending_checkout_request', $request->all());

                Log::channel('single')->info("MFA Requested for User [{$user->id}]. OTP Code: [{$otp}]");
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\MfaOtpMail($otp));

                Session::flash('ai_warning', "Secure Authentication Required. For your protection, please verify your identity to proceed.");

                return redirect()->route('otp.verify');
            }
        }
        
        // Clean up flag for next time
        Session::forget('mfa_verified');

        // ==== END Risk Scoring Perimeter ====

        // Check if this is "Buy Now" from product page
        if ($request->has('product_id')) {
            $product = \App\Models\Product::findOrFail($request->input('product_id'));
            
            if ($product->seller_id === Auth::id()) {
                return back()->with('error', 'You cannot purchase your own products.');
            }
            
            $quantity = $request->input('quantity', 1);
            // Stock validation
            if ($product->stock < $quantity) {
                return back()->with('error', "Only {$product->stock} units of {$product->name} are available. Please adjust quantity.");
            }
            // Create order directly without cart
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

            // Add order item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_price' => $product->getDiscountedPrice(),
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ]);

            // Reduce stock
            $product->decrement('stock', $quantity);

            // Create Payment
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
                'amount' => $totalAmount,
            ]);

            // If this order needs manual review, redirect customer to contact support
            if ($requiresManualReview) {
                \App\Models\OrderNotification::create([
                    'order_id' => $order->id,
                    'customer_id' => $user->id,
                    'message' => 'Your order has been flagged for manual review. Please contact support to have it verified.',
                ]);

                return redirect()->route('support.contact', ['order_id' => $order->id]);
            }

            // Handle payment
            if ($validated['payment_method'] === 'online') {
                return redirect()->route('paypal.create', $order)->with('success', 'Order placed successfully. Proceeding to PayPal for payment.');
            }

            return redirect()->route('orders.show', $order)->with('success', 'Order placed successfully');
        }

        // Original cart-based order logic
        $cart = $user->cart;

        if (!$cart || $cart->items->isEmpty()) {
            return back()->with('error', 'Cart is empty');
        }

        $selectedItemIds = $request->input('item_ids', []);

        if (empty($selectedItemIds)) {
            $items = $cart->items()->with('product')->get();
        } else {
            $items = $cart->items()->whereIn('id', $selectedItemIds)->with('product')->get();
        }

        if ($items->isEmpty()) {
            return back()->with('error', 'No items selected for checkout');
        }

        // Stock validation: refuse checkout if any requested quantity exceeds available stock.
        $outOfStock = [];
        foreach ($items as $item) {
            $product = $item->product;
            if (!$product) {
                continue;
            }
            if ($product->stock < $item->quantity) {
                $outOfStock[] = "{$product->name} ({$product->stock} left)";
            }
        }
        if (!empty($outOfStock)) {
            $message = "The following products are out of stock or have insufficient quantity: " . implode(', ', $outOfStock) . ". Please adjust your cart.";
            return back()->with('error', $message);
        }

        foreach ($items as $item) {
            if ($item->product->seller_id === Auth::id()) {
                return back()->with('error', 'You cannot purchase your own products: ' . $item->product->name);
            }
        }

        // Group items by seller
        $groupedItems = [];
        foreach ($items as $item) {
            $sellerId = $item->product->seller_id;
            if (!isset($groupedItems[$sellerId])) {
                $groupedItems[$sellerId] = [];
            }
            $groupedItems[$sellerId][] = $item;
        }

        $lastOrder = null;
        foreach ($groupedItems as $sellerId => $sellerItems) {
            // Convert array to collection
            $sellerItemsCollection = collect($sellerItems);
            $subtotal = $sellerItemsCollection->sum(function ($item) {
                return $item->product->getDiscountedPrice() * $item->quantity;
            });

            $shippingFee = SystemFee::first()?->shipping_fee_default ?? 0;
            $totalAmount = $subtotal + $shippingFee;

            // Determine order status based on whether manual review is required
            $orderStatus = $requiresManualReview ? 'review' : 'pending';

            // Create Order
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $user->id,
                'seller_id' => $sellerId,
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
            $lastOrder = $order;

            // Create Order Items
            foreach ($sellerItemsCollection as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product->id,
                    'product_name' => $cartItem->product->name,
                    'product_price' => $cartItem->product->getDiscountedPrice(),
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $cartItem->product->getDiscountedPrice() * $cartItem->quantity,
                ]);

                // Reduce stock
                $cartItem->product->decrement('stock', $cartItem->quantity);
            }

            // Create Payment
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
                'amount' => $totalAmount,
            ]);
        }

        // Clear selected items from cart
        if (!empty($selectedItemIds)) {
            $cart->items()->whereIn('id', $selectedItemIds)->delete();
        } else {
            $cart->items()->delete();
        }

        // If any of these orders require manual review, send notification and send user to contact support.
        if ($requiresManualReview && $lastOrder) {
            \App\Models\OrderNotification::create([
                'order_id' => $lastOrder->id,
                'customer_id' => $user->id,
                'message' => 'Your order has been flagged for manual review. Please contact support to have it verified.',
            ]);

            return redirect()->route('support.contact', ['order_id' => $lastOrder->id]);
        }

        // For online payment, redirect to PayPal with the last created order.
        if ($validated['payment_method'] === 'online' && $lastOrder) {
            return redirect()->route('paypal.create', $lastOrder)->with('success', 'Order placed successfully. Proceeding to PayPal for payment.');
        }

        return redirect()->route('orders.index')->with('success', 'Orders placed successfully!');
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->customer_id !== Auth::id()) {
            abort(403);
        }

        if (!$order->canBeCancelled()) {
            return back()->with('error', 'Order cannot be cancelled');
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Restore stock
        foreach ($order->items as $item) {
            $item->product->increment('stock', $item->quantity);
        }

        return back()->with('success', 'Order cancelled successfully');
    }

    /**
     * Permanently delete a cancelled order. Only the owner may do this.
     */
    public function destroy(Order $order)
    {
        if ($order->customer_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'cancelled') {
            return back()->with('error', 'Only cancelled orders can be deleted');
        }

        // Delete associated payment record to prevent foreign key constraint violation
        if ($order->payment) {
            $order->payment->delete();
        }

        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Order removed permanently');
    }

    public function payment(Request $request, Order $order)
    {
        if ($order->customer_id !== Auth::id()) {
            abort(403);
        }

        if ($order->payment_status === 'paid') {
            return back()->with('error', 'Order already paid');
        }

        // This method is no longer used. Payment is handled via PayPal callback
        // See PayPalController::paymentSuccess() for payment completion logic
        
        return redirect()->route('paypal.create', $order);
    }

    /**
     * Add all items from an existing order back into the customer's cart for quick repurchase.
     */
    public function buyAgain(Order $order)
    {
        if ($order->customer_id !== Auth::id()) {
            abort(403);
        }

        $cart = Auth::user()->cart ?? \App\Models\Cart::create(['customer_id' => Auth::id()]);
        
        $skippedItems = [];
        $addedCount = 0;

        foreach ($order->items as $item) {
            if (!$item->product || $item->product->stock <= 0) {
                $skippedItems[] = $item->product_name ?? 'Unknown Product';
                continue; // product removed or out of stock entirely
            }

            // check if we can add the full previous quantity
            $quantityToAdd = min($item->quantity, $item->product->stock);

            if ($quantityToAdd < $item->quantity) {
                $skippedItems[] = ($item->product_name ?? 'Product') . " (only $quantityToAdd available)";
            }

            $existing = $cart->items()->where('product_id', $item->product_id)->first();
            if ($existing) {
                // Total cart quantity shouldn't exceed stock
                $newQuantity = min($existing->quantity + $quantityToAdd, $item->product->stock);
                $existing->update(['quantity' => $newQuantity]);
                $addedCount++;
            } else {
                \App\Models\CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $item->product_id,
                    'quantity' => $quantityToAdd,
                ]);
                $addedCount++;
            }
        }

        if ($addedCount === 0) {
            return redirect()->route('cart.index')->with('error', 'None of the items from this order are currently available.');
        }

        if (!empty($skippedItems)) {
            return redirect()->route('cart.index')->with('info', 'Some items were added to cart, but the following had limited or no stock: ' . implode(', ', $skippedItems));
        }

        return redirect()->route('cart.index')->with('success', 'All available items added to cart.');
    }

    private function generateOrderNumber()
    {
        return 'ORD' . date('Ymd') . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Fake webhook endpoint used by shippers to notify delivery.
     *
     * Expects JSON body containing `order_id` and `secret_key`.
     * If the key does not match the hardcoded value we return 401.
     * When valid we update the order's status to delivered and stamp delivered_at.
     * This route is deliberately left outside of any auth middleware so that
     * external services (or Postman) can hit it directly.
     */
    public function shipperUpdateStatus(Request $request)
    {
        // always check the secret key first so a wrong key never reveals order existence
        $secret = $request->input('secret_key');
        if ($secret !== 'LUXGUARD_SECRET_2026') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'order_id' => 'required|integer',
            // secret_key already checked above, validation only ensures presence
            'secret_key' => 'required|string',
        ]);

        $order = Order::find($data['order_id']);
        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->status = 'delivered';
        $order->delivered_at = now();
        $order->save();

        // When an order is delivered we finalize the seller payout:
        // - Find the pending seller wallet transaction for this order
        // - Mark it as completed
        // - Credit the seller's wallet balance (available balance)
        try {
            $tx = WalletTransaction::where('order_id', $order->id)
                ->where('type', 'credit')
                ->where('status', 'pending')
                ->first();

            if ($tx) {
                $tx->status = 'completed';
                $tx->save();

                // Adjust seller wallet balance
                try {
                    $wallet = $tx->wallet;
                    if ($wallet) {
                        $wallet->adjustBalance($tx->amount);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to credit seller wallet on delivery', ['order_id' => $order->id, 'tx' => $tx->id, 'error' => $e->getMessage()]);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error finalizing seller payout on delivery', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['message' => 'Order status updated']);
    }
}
