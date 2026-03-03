<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TransactionController extends Controller
{
    /**
     * Approve transaction and process PayPal payout
     * 
     * Fee Calculation Logic:
     * - Commission is ALREADY deducted at payment time (PayPalController::paymentSuccess)
     * - This transaction contains ONLY the seller's final amount (after admin commission)
     * - Example: Customer pays $100, Admin gets 10% = $10, Seller wallet gets 90% = $90
     * - This approve() sends only the seller's $90 to their PayPal account
     */
    public function approve(Request $request, WalletTransaction $transaction)
    {
        // Only approve pending seller transactions (credits to seller wallet)
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Only pending transactions can be approved for payout');
        }

        // Get seller from wallet
        $seller = $transaction->wallet->user;
        if ($seller->role !== 'seller' || !$seller->paypal_email) {
            return back()->with('error', 'Seller not found or missing PayPal email');
        }

        try {
            // Initialize PayPal client with credentials from .env
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $token = $provider->getAccessToken();

            // Create payout batch to send funds to seller's PayPal email
            // Use direct HTTP call to PayPal Payouts API to avoid provider-specific method differences
            $accessToken = is_array($token) && isset($token['access_token']) ? $token['access_token'] : $token;
            $base = config('paypal.mode') === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

            $payload = [
                'sender_batch_header' => [
                    'sender_batch_id' => uniqid('payout_'),
                    'email_subject' => 'Payment from LuxuryStore Marketplace',
                ],
                'items' => [[
                    'recipient_type' => 'EMAIL',
                    'amount' => [
                        'value' => number_format($transaction->amount, 2, '.', ''),
                        'currency' => env('PAYPAL_CURRENCY', 'USD'),
                    ],
                    'note' => $transaction->description,
                    'sender_item_id' => "transaction_{$transaction->id}",
                    'receiver' => $seller->paypal_email,
                ]],
            ];

            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($base . '/v1/payments/payouts', $payload)
                ->json();

            if (isset($response['batch_header']['payout_batch_id'])) {
                // Update transaction status
                $transaction->update([
                    'status' => 'payout_approved',
                    'payout_approved_at' => now(),
                    'reference_code' => $response['batch_header']['payout_batch_id'],
                ]);

                // Deduct the payout amount from seller wallet balance (atomic)
                try {
                    $sellerWallet = $transaction->wallet;
                    if ($sellerWallet) {
                        $sellerWallet->adjustBalance(-1 * $transaction->amount);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to deduct seller wallet balance on payout approval', [
                        'transaction_id' => $transaction->id,
                        'seller_wallet_id' => $transaction->wallet_id,
                        'error' => $e->getMessage(),
                    ]);
                }

                return back()->with('success', "Payout approved! Seller will receive ₫" . number_format($transaction->amount, 0) . " via PayPal. Batch ID: {$response['batch_header']['payout_batch_id']}");
            }

            return back()->with('error', 'Failed to create PayPal payout batch: ' . json_encode($response));
        } catch (\Exception $e) {
            Log::error('PayPal Payout Error', [
                'transaction_id' => $transaction->id,
                'seller_id' => $seller->id,
                'amount' => $transaction->amount,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'PayPal Error: ' . $e->getMessage());
        }
    }

    /**
     * Reject transaction (cancel payout request)
     * The seller can re-request withdrawal later if the reason is resolved
     */
    public function reject(Request $request, WalletTransaction $transaction)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        // Store rejection reason as JSON for audit trail
        $adminEmail = Auth::user()?->email ?? 'admin';
        $transaction->update([
            'status' => 'payout_rejected',
            'payout_rejected_at' => now(),
            'reference_code' => json_encode([
                'rejection_reason' => $validated['rejection_reason'],
                'rejected_by_admin' => $adminEmail,
                'rejected_at' => now()->toIso8601String(),
            ]),
        ]);

        Log::info('Payout Rejected', [
            'transaction_id' => $transaction->id,
            'reason' => $validated['rejection_reason']
        ]);

        return back()->with('success', '✗ Transaction rejected. Seller will be notified and can reapply.');
    }
}
