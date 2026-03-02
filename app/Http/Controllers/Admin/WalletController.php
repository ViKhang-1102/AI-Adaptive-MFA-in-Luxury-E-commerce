<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }
        
        $adminWallet = $user->wallet;
        $transactions = $adminWallet->transactions()
            ->with('wallet.user')
            ->latest()
            ->paginate(15);

        // Calculate metrics for dashboard
        $totalBalance = $adminWallet->balance ?? 0;
        
        // Get all seller wallets combined
        $totalSellerWallets = User::where('role', 'seller')
            ->with('wallet')
            ->get()
            ->sum(function($seller) {
                return $seller->wallet?->balance ?? 0;
            });

        // Total platform transactions (from PayPal + other methods)
        $totalTransactions = Payment::where('payment_method', 'paypal')
            ->orWhere('payment_method', 'wallet')
            ->count();

        return view('admin.wallet.index', compact(
            'adminWallet', 
            'transactions',
            'totalBalance',
            'totalSellerWallets',
            'totalTransactions'
        ));
    }
}
