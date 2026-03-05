<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }
        
        $adminWallet = $user->wallet;

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $transactionsQuery = $adminWallet->transactions()->with('wallet.user');
        
        if ($month && $year) {
            $transactionsQuery->whereYear('created_at', $year)
                              ->whereMonth('created_at', $month);
        }

        $transactions = $transactionsQuery->latest()->paginate(15)->withQueryString();

        // Calculate metrics for dashboard
        $totalBalance = $adminWallet->balance ?? 0;
        
        // Total platform transactions (from PayPal + other methods)
        $totalTransactionsQuery = Payment::where(function($q) {
            $q->where('payment_method', 'paypal')
              ->orWhere('payment_method', 'wallet');
        });

        if ($month && $year) {
            $totalTransactionsQuery->whereYear('created_at', $year)
                                   ->whereMonth('created_at', $month);
        }
        $totalTransactions = $totalTransactionsQuery->count();

        // Get all sellers and calculate their gross/net revenue for the selected timeframe
        $sellers = User::where('role', 'seller')
            ->with(['ordersAsSeller' => function($q) use ($year, $month) {
                if ($year && $month) {
                    $q->whereYear('created_at', $year)
                      ->whereMonth('created_at', $month);
                }
                $q->where('status', '!=', 'cancelled');
            }])
            ->get()
            ->map(function($seller) {
                // Gross revenue is sum of total_amount of non-cancelled orders
                $seller->total_gross_revenue = $seller->ordersAsSeller->sum('total_amount');
                // Net revenue is sum of seller_amount of non-cancelled orders
                $seller->total_net_revenue = $seller->ordersAsSeller->sum('seller_amount');
                return $seller;
            });

        return view('admin.wallet.index', compact(
            'adminWallet', 
            'transactions',
            'totalBalance',
            'totalTransactions',
            'sellers',
            'month',
            'year'
        ));
    }

    public function withdrawPlatformFee(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;
        $amount = $request->amount;

        if (!$wallet || $amount > $wallet->balance) {
            return back()->with('error', 'Insufficient platform balance to withdraw.');
        }

        if (!$user->paypal_email) {
            return back()->with('error', 'Please configure your Admin PayPal Email in your Profile before withdrawing fees.');
        }

        try {
            $provider = new \Srmklive\PayPal\Services\PayPal;
            $provider->setApiCredentials(config('paypal'));
            $token = $provider->getAccessToken();

            $accessToken = is_array($token) && isset($token['access_token']) ? $token['access_token'] : $token;
            $base = config('paypal.mode') === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

            $payload = [
                'sender_batch_header' => [
                    'sender_batch_id' => uniqid('admin_payout_'),
                    'email_subject' => 'Platform Fee Withdrawal',
                ],
                'items' => [[
                    'recipient_type' => 'EMAIL',
                    'amount' => [
                        'value' => number_format($amount, 2, '.', ''),
                        'currency' => env('PAYPAL_CURRENCY', 'USD'),
                    ],
                    'note' => 'Platform Fee Withdrawal',
                    'sender_item_id' => "admin_withdraw_" . uniqid(),
                    'receiver' => $user->paypal_email,
                ]],
            ];

            $response = \Illuminate\Support\Facades\Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($base . '/v1/payments/payouts', $payload)
                ->json();

            if (isset($response['batch_header']['payout_batch_id'])) {
                // Record Debit
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'description' => "Admin withdrew platform fees to {$user->paypal_email}",
                    'status' => 'completed',
                    'reference_code' => $response['batch_header']['payout_batch_id'],
                ]);

                // Deduct Balance
                $wallet->adjustBalance(-1 * $amount);

                return back()->with('success', "Withdrew $" . number_format($amount, 2) . " successfully to Admin PayPal.");
            }

            return back()->with('error', 'PayPal Payout failed: ' . json_encode($response));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Admin Payout Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to withdraw via PayPal: ' . $e->getMessage());
        }
    }
}
