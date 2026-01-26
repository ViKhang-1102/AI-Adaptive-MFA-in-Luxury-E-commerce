<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCustomers = User::customers()->count();
        $totalSellers = User::sellers()->count();
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total_amount');
        $todayOrders = Order::whereDate('created_at', today())->count();
        $totalCategories = Category::count();
        $totalProducts = Product::count();

        // Top 3 best-selling products (last 30 days)
        $topProducts = OrderItem::whereDate('created_at', '>=', now()->subDays(30))
            ->selectRaw('product_id, SUM(quantity) as total_qty')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(3)
            ->with('product')
            ->get();

        // Bottom 3 worst-selling products (last 30 days, must have order)
        $bottomProducts = OrderItem::whereDate('created_at', '>=', now()->subDays(30))
            ->selectRaw('product_id, SUM(quantity) as total_qty')
            ->groupBy('product_id')
            ->orderBy('total_qty')
            ->limit(3)
            ->with('product')
            ->get();

        return view('admin.dashboard', compact(
            'totalCustomers',
            'totalSellers',
            'totalOrders',
            'totalRevenue',
            'todayOrders',
            'totalCategories',
            'totalProducts',
            'topProducts',
            'bottomProducts'
        ));
    }
}
