<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $wallet = $user->wallet;

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $transactionsQuery = $wallet->transactions();

        if ($month && $year) {
            $transactionsQuery->whereYear('created_at', $year)
                              ->whereMonth('created_at', $month);
        }

        // Actual balance to allow withdrawals (Unrestricted by time)
        $actualBalance = $wallet->transactions()
            ->whereIn('status', ['completed', 'payout_approved'])
            ->sum(DB::raw('CASE WHEN type = "credit" THEN amount ELSE -amount END'));

        // Total Gross Revenue = all non-cancelled orders belonging to seller in the timeframe
        $ordersQuery = $user->ordersAsSeller()->where('status', '!=', 'cancelled');
        if ($month && $year) {
            $ordersQuery->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month);
        }
        $totalGrossRevenue = $ordersQuery->sum('total_amount');

        // Pending credits (seller payments awaiting admin approval)
        $pendingBalance = (clone $transactionsQuery)
            ->where('status', 'pending')
            ->where('type', 'credit')
            ->sum('amount');

        // Total withdrawn = all approved payouts (debits that have been processed)
        $totalWithdrawn = (clone $transactionsQuery)
            ->where('type', 'debit')
            ->whereIn('status', ['payout_approved', 'completed'])
            ->sum('amount');

        // Get recent transactions with details
        $transactions = (clone $transactionsQuery)
            ->with('order')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('seller.wallet.index', compact(
            'wallet', 
            'actualBalance', 
            'totalGrossRevenue', 
            'totalWithdrawn', 
            'pendingBalance', 
            'transactions',
            'month',
            'year'
        ));
    }

    public function withdraw(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10' // Enforce $10 minimum
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;

        $balance = $wallet->transactions()
            ->whereIn('status', ['completed', 'payout_approved'])
            ->sum(DB::raw('CASE WHEN type = "credit" THEN amount ELSE -amount END'));

        $amount = $request->amount;

        if ($amount > $balance) {
            return back()->with('error', 'Insufficient available balance');
        }

        // Adaptive MFA Risk Score implementation
        if ($request->filled('mfa_verified')) {
            $riskScore = 0;
        } else {
            $riskScore = rand(0, 100) / 100;
        }

        if ($riskScore > 0.7) {
            // Ask for MFA: return a view where the user can verify the withdrawal
            return view('seller.wallet.mfa_verify', compact('riskScore', 'amount'));
        }

        if (!$user->paypal_email) {
            return back()->with('error', 'Please configure your PayPal Email in your Profile settings first.');
        }

        try {
            $provider = new \Srmklive\PayPal\Services\PayPal;
            $provider->setApiCredentials(config('paypal'));
            $token = $provider->getAccessToken();

            $accessToken = is_array($token) && isset($token['access_token']) ? $token['access_token'] : $token;
            $base = config('paypal.mode') === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

            $payload = [
                'sender_batch_header' => [
                    'sender_batch_id' => uniqid('seller_payout_'),
                    'email_subject' => 'Withdrawal from LuxuryStore Marketplace',
                ],
                'items' => [[
                    'recipient_type' => 'EMAIL',
                    'amount' => [
                        'value' => number_format($amount, 2, '.', ''),
                        'currency' => env('PAYPAL_CURRENCY', 'USD'),
                    ],
                    'note' => 'Seller Wallet Withdrawal',
                    'sender_item_id' => "seller_withdraw_" . uniqid(),
                    'receiver' => $user->paypal_email,
                ]],
            ];

            $response = \Illuminate\Support\Facades\Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($base . '/v1/payments/payouts', $payload)
                ->json();

            if (isset($response['batch_header']['payout_batch_id'])) {
                DB::transaction(function () use ($wallet, $amount, $response) {
                    // Create debit for Seller
                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'debit',
                        'amount' => $amount,
                        'description' => "Withdraw to PayPal",
                        'status' => 'completed',
                        'payout_approved_at' => now(),
                        'reference_code' => $response['batch_header']['payout_batch_id'],
                    ]);
                    
                    // Deduct actual wallet database money
                    $wallet->adjustBalance(-1 * $amount);

                    // Deduct from Admin Wallet since money originated there
                    $admin = \App\Models\User::where('role', 'admin')->first();
                    $adminWallet = $admin?->wallet;
                    if ($adminWallet) {
                        WalletTransaction::create([
                            'wallet_id' => $adminWallet->id,
                            'type' => 'debit',
                            'amount' => $amount,
                            'description' => "System auto-payout to Seller #{$wallet->user_id}",
                            'status' => 'completed',
                            'reference_code' => $response['batch_header']['payout_batch_id'],
                        ]);
                        $adminWallet->adjustBalance(-1 * $amount);
                    }
                });

                return back()->with('success', 'Withdrawal successful! Funds have been sent to your PayPal app.');
            }

            return back()->with('error', 'PayPal Payout API failed to process request. Please try again.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Withdraw execution failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to process withdrawal: ' . $e->getMessage());
        }
    }
}
