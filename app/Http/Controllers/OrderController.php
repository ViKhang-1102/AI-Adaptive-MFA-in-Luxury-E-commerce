<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Payment;
use App\Models\SystemFee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->customer_id !== Auth::id()) {
            abort(403);
        }

        $order->load('items.product', 'seller', 'payment');

        return view('orders.show', compact('order'));
    }

    public function checkout()
    {
        $user = Auth::user();

        // Check if this is "Buy Now" from product page
        if (request()->has('product_id')) {
            $product = \App\Models\Product::findOrFail(request('product_id'));
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
            // Get items from cart
            $cart = $user->cart;

            if (!$cart || $cart->items->isEmpty()) {
                return redirect()->route('cart.index')->with('error', 'Cart is empty');
            }

            $items = $cart->items()->with('product.seller')->get();
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
        }

        // Check if this is "Buy Now" from product page
        if (request()->has('product_id')) {
            $product = \App\Models\Product::findOrFail(request('product_id'));
            $quantity = request('quantity', 1);

            // Create order directly without cart
            $subtotal = $product->getDiscountedPrice() * $quantity;
            $shippingFee = SystemFee::first()?->shipping_fee_default ?? 0;
            $totalAmount = $subtotal + $shippingFee;

            $order = Order::create([
                'customer_id' => $user->id,
                'seller_id' => $product->seller_id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'recipient_name' => $recipientName,
                'recipient_phone' => $recipientPhone,
                'delivery_address' => $deliveryAddress,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
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

            // Handle payment
            if ($validated['payment_method'] === 'online') {
                return redirect()->route('paypal.create', $order);
            }

            return redirect()->route('orders.show', $order)->with('success', 'Order placed successfully');
        }

        // Original cart-based order logic
        $cart = $user->cart;

        if (!$cart || $cart->items->isEmpty()) {
            return back()->with('error', 'Cart is empty');
        }

        $items = $cart->items()->with('product')->get();

        // Group items by seller
        $groupedItems = [];
        foreach ($items as $item) {
            $sellerId = $item->product->seller_id;
            if (!isset($groupedItems[$sellerId])) {
                $groupedItems[$sellerId] = [];
            }
            $groupedItems[$sellerId][] = $item;
        }

        foreach ($groupedItems as $sellerId => $sellerItems) {
            // Convert array to collection
            $sellerItemsCollection = collect($sellerItems);
            $subtotal = $sellerItemsCollection->sum(function ($item) {
                return $item->product->getDiscountedPrice() * $item->quantity;
            });

            $shippingFee = SystemFee::first()?->shipping_fee_default ?? 0;
            $totalAmount = $subtotal + $shippingFee;

            // Create Order
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $user->id,
                'seller_id' => $sellerId,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'recipient_name' => $recipientName,
                'recipient_phone' => $recipientPhone,
                'delivery_address' => $deliveryAddress,
            ]);

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

        // Clear cart
        $cart->items()->delete();

        // For first order in multi-seller, redirect to PayPal if online payment
        if ($validated['payment_method'] === 'online') {
            if (count($groupedItems) === 1) {
                $firstOrder = Order::find($order->id ?? null);
                if ($firstOrder) {
                    return redirect()->route('paypal.create', $firstOrder)->with('success', 'Proceeding to PayPal payment');
                }
            } else {
                return redirect()->route('orders.index')->with('success', 'Orders placed successfully. Please complete the online payment for each order below.');
            }
        }

        return redirect()->route('orders.index')->with('success', 'Order placed successfully');
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

    private function generateOrderNumber()
    {
        return 'ORD' . date('Ymd') . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
    }
}
