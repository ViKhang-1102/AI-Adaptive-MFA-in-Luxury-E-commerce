<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');

        /** @var \App\Models\User $seller */
        $seller = Auth::user();
        
        // Total products
        $totalProducts = $seller->products()->count();
        
        // Total orders (any non-pending statuses for this seller)
        $validStatuses = ['confirmed', 'processing', 'shipped', 'delivered'];
        
        $ordersQuery = Order::whereIn('status', $validStatuses)
            ->where('seller_id', $seller->id);
            
        if ($month && $year) {
            $ordersQuery->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month);
        }

        $totalOrders = (clone $ordersQuery)->count();
        
        // Total revenue (from those same orders for this seller)
        $totalRevenue = OrderItem::whereHas('order', function($query) use ($seller, $validStatuses, $month, $year) {
                $query->whereIn('status', $validStatuses)
                      ->where('seller_id', $seller->id);
                      
                if ($month && $year) {
                    $query->whereYear('created_at', $year)
                          ->whereMonth('created_at', $month);
                }
            })
            ->sum(DB::raw('quantity * product_price'));
        
        // Pending orders (orders with seller's products that are still pending)
        $pendingOrders = Order::where('seller_id', $seller->id)
            ->where('status', 'pending')
            ->count();

        // unread messages for seller
        $unreadMessages = \App\Models\Message::where('receiver_id', $seller->id)
            ->where('read', false)
            ->count();

        // Purchase Statistics (Seller as Customer)
        $purchaseValidStatuses = ['confirmed', 'processing', 'shipped', 'delivered'];
        $totalSpent = Order::where('customer_id', $seller->id)
            ->whereIn('status', $purchaseValidStatuses)
            ->sum('total_amount');
        
        $totalPurchaseOrders = Order::where('customer_id', $seller->id)
            ->whereIn('status', $purchaseValidStatuses)
            ->count();

        return view('seller.dashboard', compact(
            'totalProducts',
            'totalOrders',
            'totalRevenue',
            'pendingOrders',
            'unreadMessages',
            'totalSpent',
            'totalPurchaseOrders',
            'month',
            'year'
        ));
    }
}
