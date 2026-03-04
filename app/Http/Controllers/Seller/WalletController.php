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
        // Balance = sum of completed / approved credit transactions (after deducting debits)
        $balance = $wallet->transactions()
            ->whereIn('status', ['completed', 'payout_approved'])
            ->sum(DB::raw('CASE WHEN type = "credit" THEN amount ELSE -amount END'));

        // Total earned = all completed credits (payments from customers)
        $totalEarned = $wallet->transactions()
            ->where('status', 'completed')
            ->where('type', 'credit')
            ->sum('amount');

        // Pending credits (seller payments awaiting admin approval)
        $pendingBalance = $wallet->transactions()
            ->where('status', 'pending')
            ->where('type', 'credit')
            ->sum('amount');

        // Total withdrawn = all approved payouts (debits that have been processed)
        $totalWithdrawn = $wallet->transactions()
            ->where('status', 'payout_approved')
            ->sum('amount');
        // Get recent transactions with details
        $transactions = $wallet->transactions()
            ->with('order')
            ->latest()
            ->paginate(15);

        return view('seller.wallet.index', compact('wallet', 'balance', 'totalEarned', 'totalWithdrawn', 'pendingBalance', 'transactions'));
    }

    public function withdraw(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01'
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;

        $balance = $wallet->transactions()
            ->whereIn('status', ['completed', 'payout_approved'])
            ->sum(DB::raw('CASE WHEN type = "credit" THEN amount ELSE -amount END'));

        $amount = $request->amount;

        if ($amount > $balance) {
            return back()->with('error', 'Insufficient balance');
        }

        try {
            DB::transaction(function () use ($wallet, $amount) {
                // Create debit for Seller
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'description' => "Withdrawal request",
                    'status' => 'payout_approved',
                    'payout_approved_at' => now(),
                ]);
                
                // Trừ tiền thực tế
                $wallet->adjustBalance(-1 * $amount);

                // Deduct from Admin Wallet to simulate payout success
                $admin = \App\Models\User::where('role', 'admin')->first();
                $adminWallet = $admin?->wallet;
                if ($adminWallet) {
                    WalletTransaction::create([
                        'wallet_id' => $adminWallet->id,
                        'type' => 'debit',
                        'amount' => $amount,
                        'description' => "System payout to Seller #{$wallet->user_id}",
                        'status' => 'completed',
                    ]);
                    $adminWallet->adjustBalance(-1 * $amount);
                }
            });

            return back()->with('success', 'Withdrawal successful (Simulated)');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Withdraw simulation failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to process withdrawal');
        }
    }
}
