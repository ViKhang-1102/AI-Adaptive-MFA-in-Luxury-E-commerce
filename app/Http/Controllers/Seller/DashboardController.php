<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $seller = auth()->user();
        
        // Total products
        $totalProducts = $seller->products()->count();
        
        // Total orders (orders that contain seller's products)
        $totalOrders = OrderItem::whereIn('product_id', $seller->products()->pluck('id'))
            ->distinct('order_id')
            ->count();
        
        // Total revenue (from seller's products)
        $totalRevenue = OrderItem::whereIn('product_id', $seller->products()->pluck('id'))
            ->sum(DB::raw('quantity * product_price'));
        
        // Pending orders (orders with seller's products that are pending)
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
