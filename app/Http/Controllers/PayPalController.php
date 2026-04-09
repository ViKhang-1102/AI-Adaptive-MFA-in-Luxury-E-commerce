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
use App\Services\RiskAssessmentService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\SecurityAudit;

class PayPalController extends Controller
{
    /**
     * Start a payment for an order.
     */
    public function createPayment(Request $request, Order $order)
    {
        $user = Auth::user();

        // Check if user is logged in
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        // ==========================================
        // AI Risk Scoring Perimeter (Online Payment)
        // ==========================================
        if (!Session::get('mfa_verified')) {
            $enableAiMfa = env('ENABLE_AI_MFA', true);

            if ($enableAiMfa) {
                $riskService = app(RiskAssessmentService::class);
                $riskResult = $riskService->analyze($user, $order->total_amount, 'online', $request->input('latitude'), $request->input('longitude'));
                if ($riskResult) {
                    $suggestion = $riskResult['suggestion'] ?? 'allow';
                    $score = $riskResult['risk_score'] ?? 0;
                    $level = $riskResult['level'] ?? 'low';
                } else {
                    $suggestion = 'otp';
                    $score = 50.0;
                    $level = 'medium';
                }
            } else {
                // Static MFA - Non AI branch
                $suggestion = 'otp';
                $score = 50.0;
                $level = 'medium';
                $riskResult = [
                    'explanation' => [
                        'score_breakdown' => ['Static (no-AI) MFA mode in use; online payment requires OTP.'],
                        'input' => ['amount' => $order->total_amount],
                    ],
                ];
            }

            if ($suggestion === 'faceid' || $suggestion === 'otp') {
                // Create Security Audit Record
                SecurityAudit::create([
                    'user_id' => $user->id,
                    'action' => 'online_payment',
                    'amount' => $order->total_amount,
                    'risk_score' => $score,
                    'level' => $level,
                    'suggestion' => $suggestion,
                    'result' => 'pending',
                    'metadata' => [
                        'ai_enabled' => $enableAiMfa,
                        'order_id' => $order->id,
                        'risk_explanation' => $riskResult['explanation'] ?? null,
                    ],
                ]);

                $otp = rand(100000, 999999);
                Session::put('expected_otp', $otp);
                // Redirect back to this same route after OTP success
                Session::put('intended_action_url', route('paypal.create', $order));

                Log::channel('single')->info("MFA Requested for Online Payment Order [{$order->id}]. OTP Code: [{$otp}]");
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\MfaOtpMail($otp));

                Session::flash('ai_warning', "Secure Authentication Required for Online Payment.");
                return redirect()->route('otp.verify');
            }
        }
        
        // Clean up flag for next time
        Session::forget('mfa_verified');

        // otherwise proceed to PayPal
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        // Order total is already stored in USD
        $amountInUsd = round($order->total_amount, 2);

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
     * Shows a bill preview before final payment capture.
     */
    public function paymentSuccess(Request $request)
    {
        $orderId = $request->query('order_id');
        $token = $request->query('token');
        $payerID = $request->query('PayerID');
        
        $order = Order::findOrFail($orderId);

        // If the order is already paid, just show the success view
        if ($order->payment_status === 'paid') {
            $adminPercentage = SystemFee::getPlatformCommission();
            $sellerPercentage = 100 - $adminPercentage;
            $adminFee = round($order->total_amount * ($adminPercentage / 100), 2);
            $sellerAmount = round($order->total_amount * ($sellerPercentage / 100), 2);
            $sellerPayPalEmail = $order->seller?->paypal_email;
            
            return view('paypal.success', compact('order', 'adminFee', 'sellerAmount', 'sellerPayPalEmail', 'adminPercentage', 'sellerPercentage'));
        }

        // Generate a QR code for the review page
        $qrData = route('paypal.success', ['order_id' => $orderId, 'token' => $token, 'PayerID' => $payerID]);
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData);

        return view('paypal.review', compact('order', 'token', 'payerID', 'qrCodeUrl'));
    }

    /**
     * Actually capture the payment after user confirms the bill.
     */
    public function capturePayment(Request $request, Order $order)
    {
        $token = $request->input('token');

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $result = $provider->capturePaymentOrder($token);

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

            \App\Models\OrderNotification::create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'message' => "Payment for order {$order->order_number} was successful.",
            ]);

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

            // Create wallet transaction for seller payment (pending).
            // Seller balance will be credited only when delivery is completed.
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
            }

            return view('paypal.success', compact('order', 'adminFee', 'sellerAmount', 'sellerPayPalEmail', 'adminPercentage', 'sellerPercentage'));
        }

        return redirect()->route('home')->with('error', 'Payment was not successful.');
    }

    /**
     * Handle PayPal cancel callback.
     * Moves items back to the cart and redirects user.
     */
    public function paymentCancel(Request $request)
    {
        $orderId = $request->query('order_id');
        $order = Order::with('items')->find($orderId);

        if ($order && $order->payment_status === 'pending') {
            $user = Auth::user();
            if ($user) {
                // Get or create user's cart
                $cart = \App\Models\Cart::firstOrCreate(['customer_id' => $user->id]);

                // Move each order item back to the cart
                foreach ($order->items as $item) {
                    $cartItem = \App\Models\CartItem::where('cart_id', $cart->id)
                        ->where('product_id', $item->product_id)
                        ->first();

                    if ($cartItem) {
                        $cartItem->quantity += $item->quantity;
                        $cartItem->save();
                    } else {
                        \App\Models\CartItem::create([
                            'cart_id' => $cart->id,
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity
                        ]);
                    }
                }

                // Delete the pending order since it's cancelled and items are back in cart
                // Refund stock for each item before deleting
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }

                // Delete associated payments first to avoid foreign key constraint violation
                $order->payment()->delete();
                $order->items()->delete();
                $order->delete();

                return redirect()->route('cart.index')->with('info', 'Your payment was cancelled, and the items have been moved back to your cart.');
            }
        }

        return view('paypal.cancel');
    }
}
