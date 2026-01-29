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
        
        // Total orders (only confirmed orders that contain seller's products)
        $totalOrders = Order::where('status', 'confirmed')
            ->whereHas('items', function($query) use ($seller) {
                $query->whereIn('product_id', $seller->products()->pluck('id'));
            })
            ->distinct('id')
            ->count();
        
        // Total revenue (from confirmed orders only)
        $totalRevenue = OrderItem::whereIn('product_id', $seller->products()->pluck('id'))
            ->whereHas('order', function($query) {
                $query->where('status', 'confirmed');
            })
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
