<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $seller */
        $seller = Auth::user();
        
        // Total products
        $totalProducts = $seller->products()->count();
        
        // Total orders (any non-pending statuses for this seller)
        // previously we relied on order_items.product_id which stopped matching when
        // the product was deleted; use seller_id directly so history stays visible.
        $validStatuses = ['confirmed', 'processing', 'shipped', 'delivered'];
        $totalOrders = Order::whereIn('status', $validStatuses)
            ->where('seller_id', $seller->id)
            ->count();
        
        // Total revenue (from those same orders for this seller)
        $totalRevenue = OrderItem::whereHas('order', function($query) use ($seller, $validStatuses) {
                $query->whereIn('status', $validStatuses)
                      ->where('seller_id', $seller->id);
            })
            ->sum(DB::raw('quantity * product_price'));
        
        // Pending orders (orders with seller's products that are still pending)
        $pendingOrders = Order::where('seller_id', $seller->id)
            ->where('status', 'pending')
            ->count();

        return view('seller.dashboard', compact(
            'totalProducts',
            'totalOrders',
            'totalRevenue',
            'pendingOrders'
        ));
    }
}
