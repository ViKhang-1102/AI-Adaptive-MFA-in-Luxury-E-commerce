<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Payment;
use App\Models\SystemFee;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->ordersAsCustomer()
            ->with('seller', 'items')
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.product', 'seller', 'payment');

        return view('orders.show', compact('order'));
    }

    public function checkout()
    {
        $user = auth()->user();
        $cart = $user->cart;

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Cart is empty');
        }

        $items = $cart->items()->with('product.seller')->get();
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
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'delivery_address' => 'required|string',
            'payment_method' => 'required|in:cod,online',
            'address_id' => 'nullable|exists:customer_addresses,id',
        ]);

        $user = auth()->user();
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
                'payment_status' => $validated['payment_method'] === 'cod' ? 'pending' : 'pending',
                'recipient_name' => $validated['recipient_name'],
                'recipient_phone' => $validated['recipient_phone'],
                'delivery_address' => $validated['delivery_address'],
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

        return redirect()->route('orders.index')->with('success', 'Order placed successfully');
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
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

    public function payment(Request $request, Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        if ($order->payment_status === 'paid') {
            return back()->with('error', 'Order already paid');
        }

        // Simulate VNPay payment
        // In production, integrate with VNPay API
        
        $order->update(['payment_status' => 'paid']);
        $order->payment()->update(['status' => 'completed', 'processed_at' => now()]);

        return back()->with('success', 'Payment completed');
    }

    private function generateOrderNumber()
    {
        return 'ORD' . date('Ymd') . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
    }
}
