<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->ordersAsSeller()
            ->with('customer', 'items')
            ->latest()
            ->paginate(10);

        return view('seller.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->seller_id !== auth()->id()) {
            abort(403);
        }

        $order->load('customer', 'items.product');
        return view('seller.orders.show', compact('order'));
    }

    public function confirm(Request $request, Order $order)
    {
        if ($order->seller_id !== auth()->id() || $order->status !== 'pending') {
            abort(403);
        }

        $order->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return back()->with('success', 'Order confirmed');
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->seller_id !== auth()->id() || !in_array($order->status, ['pending', 'confirmed'])) {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $order->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['reason'],
            'cancelled_at' => now(),
        ]);

        // Restore stock
        foreach ($order->items as $item) {
            $item->product->increment('stock', $item->quantity);
        }

        return back()->with('success', 'Order cancelled');
    }

    public function ship(Request $request, Order $order)
    {
        if ($order->seller_id !== auth()->id() || $order->status !== 'confirmed') {
            abort(403);
        }

        $order->update([
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);

        return back()->with('success', 'Order marked as shipped');
    }
}
