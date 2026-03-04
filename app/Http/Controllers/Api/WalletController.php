<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use App\Models\User;

class WalletController extends Controller
{
    /**
     * Simulate a payout/withdrawal: deduct from the system (admin) wallet
     * and mark the seller transaction as paid out.
     *
     * Request body: { transaction_id: int }
     */
    public function withdraw(Request $request)
    {
        $data = $request->validate([
            'transaction_id' => 'required|integer|exists:wallet_transactions,id',
        ]);

        $tx = WalletTransaction::find($data['transaction_id']);
        if (! $tx) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Only proceed if it's a completed credit to a seller
        if ($tx->type !== 'credit' || $tx->status !== 'completed') {
            return response()->json(['message' => 'Transaction not eligible for payout'], 400);
        }

        $admin = User::where('role', 'admin')->first();
        $adminWallet = $admin?->wallet;
        if (! $adminWallet) {
            return response()->json(['message' => 'Admin wallet not found'], 500);
        }

        // Create a debit transaction from seller wallet
        try {
            DB::transaction(function () use ($tx, $adminWallet) {
                // Seller debit
                $sellerWallet = $tx->wallet;
                if ($sellerWallet) {
                    $sellerDebit = WalletTransaction::create([
                        'wallet_id' => $sellerWallet->id,
                        'order_id' => $tx->order_id,
                        'type' => 'debit',
                        'amount' => $tx->amount,
                        'description' => "Payout requested/approved for Transaction #{$tx->id}",
                        'status' => 'payout_approved',
                        'payout_approved_at' => now(),
                    ]);
                    $sellerWallet->adjustBalance(-1 * $tx->amount);
                }

                // Admin debit
                $adminDebit = WalletTransaction::create([
                    'wallet_id' => $adminWallet->id,
                    'order_id' => $tx->order_id,
                    'type' => 'debit',
                    'amount' => $tx->amount,
                    'description' => "Payout for Order #{$tx->order_id}",
                    'status' => 'completed',
                ]);
                $adminWallet->adjustBalance(-1 * $tx->amount);

                // Option: mark original tx as payout_approved (but this affects the "Total Earned" metric if we don't include payout_approved in calculation)
                // We keep original tx 'completed'
            });

            return response()->json(['message' => 'Payout simulated successfully']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Withdraw simulation failed', ['error' => $e->getMessage(), 'tx' => $tx->id]);
            return response()->json(['message' => 'Failed to simulate payout'], 500);
        }
    }
}
