<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class PaymentHistoryController extends Controller
{
    public function index()
    {
        $orders = Order::with(['items.product', 'payment'])
            ->where('customer_id', Auth::id())
            ->where('payment_method', 'online')
            ->where('payment_status', 'paid')
            ->latest()
            ->paginate(15);

        return view('payments.history', compact('orders'));
    }
}
