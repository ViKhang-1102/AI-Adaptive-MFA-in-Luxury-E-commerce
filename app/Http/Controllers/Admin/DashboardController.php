<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCustomers = User::customers()->count();
        $totalSellers = User::sellers()->count();
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total_amount');
        $todayOrders = Order::whereDate('created_at', today())->count();

        return view('admin.dashboard', compact(
            'totalCustomers',
            'totalSellers',
            'totalOrders',
            'totalRevenue',
            'todayOrders'
        ));
    }
}
