<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SystemFee;
use App\Models\WalletTransaction;
use App\Models\User;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Auth;

class PayPalController extends Controller
{
    /**
     * Start a payment for an order.
     */
    public function createPayment(Request $request, Order $order)
    {
        // ==== Chèn logic AI Risk Score tại đây ====
        // Ví dụ: $riskScore = app(RiskScoringService::class)->calculateRisk(auth()->user());
        // Nếu $riskScore > 0.7, yêu cầu xác thực MFA
        // ============================================

        if ($request->filled('mfa_verified')) {
            $riskScore = 0;
        } else {
            $riskScore = rand(0, 100) / 100;
        }

        if ($riskScore > 0.7) {
            // ask for MFA: return a view where the user can verify
            return view('mfa.verify', compact('riskScore', 'order'));
        }

        // otherwise proceed to PayPal
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        // Convert stored VND order total to USD for PayPal (PayPal expects USD)
        $vndPerUsd = (float) env('VND_PER_USD', 23000);
        $amountInUsd = round($order->total_amount / ($vndPerUsd ?: 23000), 2);

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('paypal.success', ['order_id' => $order->id]),
                "cancel_url" => route('paypal.cancel', ['order_id' => $order->id]),
            ],
            "purchase_units" => [[
                "amount" => [
                    "currency_code" => env('PAYPAL_CURRENCY', 'USD'),
                    // send converted USD amount
                    "value" => number_format($amountInUsd, 2, '.', ''),
                ],
                "description" => "Order #{$order->order_number}",
            ]],
        ]);

        if (isset($response['id']) && $response['status'] === 'CREATED') {
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return redirect()->away($link['href']);
                }
            }
        }

        return redirect()->back()->with('error', 'Unable to create PayPal order.');
    }

    /**
     * Handle PayPal success callback.
     * Commission split: Dynamic Admin %, Seller 100% - Admin %
     */
    public function paymentSuccess(Request $request)
    {
        $orderId = $request->query('order_id');
        $order = Order::findOrFail($orderId);

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $result = $provider->capturePaymentOrder($request->query('token'));

        if (isset($result['status']) && $result['status'] === 'COMPLETED') {
            // Marketplace commission split - Get from database configuration
            $total = $order->total_amount;
            $adminPercentage = SystemFee::getPlatformCommission();      // Get from DB, default 10%
            $sellerPercentage = 100 - $adminPercentage;                 // Calculate seller %
            
            $adminFee = round($total * ($adminPercentage / 100), 2);    // Admin fee
            $sellerAmount = round($total * ($sellerPercentage / 100), 2); // Seller gets

            // Fetch seller PayPal email dynamically from database
            $seller = $order->seller;
            $sellerPayPalEmail = $seller?->paypal_email;

            // Update order with payment details
            $order->payment_status = 'paid';
            $order->status = 'processing';
            $order->seller_amount = $sellerAmount;
            $order->save();

            // Record payment details in database
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'paypal',
                'status' => 'completed',
                'amount' => $total,
                'transaction_id' => $result['id'] ?? null,
                'reference_code' => $result['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
                'response_data' => json_encode($result),
                'processed_at' => now(),
            ]);

            // Create wallet transactions for platform commission
            $adminWallet = User::where('role', 'admin')->first()?->wallet;
            if ($adminWallet) {
                WalletTransaction::create([
                    'wallet_id' => $adminWallet->id,
                    'order_id' => $order->id,
                    'type' => 'credit',
                    'amount' => $adminFee,
                    'description' => "Platform commission from Order #{$order->id} ({$adminPercentage}%)",
                    'status' => 'completed',
                    'transaction_reference' => $payment->reference_code,
                ]);

                // Update admin wallet balance immediately since platform earned the commission
                try {
                    $adminWallet->adjustBalance($adminFee);
                } catch (\Exception $e) {
                    // Log but do not fail the payment flow
                    \Illuminate\Support\Facades\Log::error('Failed to adjust admin wallet balance', ['error' => $e->getMessage()]);
                }
            }

            // Create wallet transaction for seller payment
            $sellerWallet = $seller?->wallet;
            if ($sellerWallet) {
                WalletTransaction::create([
                    'wallet_id' => $sellerWallet->id,
                    'order_id' => $order->id,
                    'type' => 'credit',
                    'amount' => $sellerAmount,
                    'description' => "Order #{$order->id} payment ({$sellerPercentage}%)",
                    'status' => 'pending',
                    'transaction_reference' => $payment->reference_code,
                ]);

                // Add seller amount to seller wallet balance (platform owes this to seller)
                try {
                    $sellerWallet->adjustBalance($sellerAmount);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to adjust seller wallet balance', ['seller_id' => $seller?->id, 'error' => $e->getMessage()]);
                }
            }

            return view('paypal.success', compact('order', 'adminFee', 'sellerAmount', 'sellerPayPalEmail', 'adminPercentage', 'sellerPercentage'));
        }

        return redirect()->route('home')->with('error', 'Payment was not successful.');
    }

    /**
     * Handle PayPal cancel callback.
     */
    public function paymentCancel(Request $request)
    {
        $orderId = $request->query('order_id');
        $order = Order::find($orderId);

        if ($order) {
            // We do not cancel the order here, so the user can retry payment later.
            // Just return the cancel view.
        }

        return view('paypal.cancel');
    }
}
