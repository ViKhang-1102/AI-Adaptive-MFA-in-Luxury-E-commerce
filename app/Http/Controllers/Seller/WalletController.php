<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $wallet = $user->wallet;

        // Calculate wallet metrics from wallet transactions (only completed payments)
        // Balance = sum of completed credit transactions (after deducting debits)
        $balance = $wallet->transactions()
            ->where('status', 'completed')
            ->sum(DB::raw('CASE WHEN type = "credit" THEN amount ELSE -amount END'));

        // Total earned = all completed credits (payments from customers)
        $totalEarned = $wallet->transactions()
            ->where('status', 'completed')
            ->where('type', 'credit')
            ->sum('amount');

        // Total withdrawn = all completed debits (approved/processed payouts)
        $totalWithdrawn = $wallet->transactions()
            ->where('status', 'payout_approved')
            ->sum('amount');

        // Get recent transactions with details
        $transactions = $wallet->transactions()
            ->with('order')
            ->latest()
            ->paginate(15);

        return view('seller.wallet.index', compact('wallet', 'balance', 'totalEarned', 'totalWithdrawn', 'transactions'));
    }
}
