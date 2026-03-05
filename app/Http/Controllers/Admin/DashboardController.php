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
    public function index(\Illuminate\Http\Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');

        $totalCustomers = User::customers()->count();
        $totalSellers = User::sellers()->count();
        // count any order that has progressed past initial pending state
        $validStatuses = ['confirmed', 'processing', 'shipped', 'delivered'];

        $ordersQuery = Order::whereIn('status', $validStatuses);
        
        if ($month && $year) {
            $ordersQuery->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month);
        }

        $totalOrders = (clone $ordersQuery)->count();
        $totalRevenue = (clone $ordersQuery)->sum('total_amount');
        
        // Today orders logic remains independent of the month/year filter 
        $todayOrders = Order::whereIn('status', $validStatuses)
            ->whereDate('created_at', today())
            ->count();
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

        // Wallet Statistics
        $adminWallet = auth()->user()->wallet;
        $totalPlatformBalance = $adminWallet ? $adminWallet->balance : 0;
        

        return view('admin.dashboard', compact(
            'totalCustomers',
            'totalSellers',
            'totalOrders',
            'totalRevenue',
            'todayOrders',
            'totalCategories',
            'totalProducts',
            'topProducts',
            'bottomProducts',
            'totalPlatformBalance',
            'month',
            'year'
        ));
    }
}
