<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $seller = auth()->user();
        $totalProducts = $seller->products()->count();
        $totalOrders = $seller->ordersAsSeller()->count();
        $totalRevenue = $seller->ordersAsSeller()->sum('total_amount');
        $pendingOrders = $seller->ordersAsSeller()->where('status', 'pending')->count();

        return view('seller.dashboard', compact(
            'totalProducts',
            'totalOrders',
            'totalRevenue',
            'pendingOrders'
        ));
    }
}
