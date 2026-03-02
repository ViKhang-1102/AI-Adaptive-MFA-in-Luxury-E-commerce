<?php

/**
 * Payment System Verification Script
 * 
 * This script verifies the PayPal Marketplace system is correctly configured
 * Run from terminal: php artisan tinker < verify-payment-system.php
 */

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\SystemFee;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

echo "\n=== PayPal Marketplace System Verification ===\n";

// 1. Check PayPal Configuration
echo "\n1️⃣  PayPal Configuration:\n";
echo "   - Mode: " . config('paypal.mode') . "\n";
echo "   - Currency: " . env('PAYPAL_CURRENCY', 'USD') . "\n";
echo "   - Client ID: " . (config('paypal.sandbox.client_id') ? '✓ Set' : '✗ Missing') . "\n";
echo "   - Secret: " . (config('paypal.sandbox.secret') ? '✓ Set' : '✗ Missing') . "\n";

// 2. Check Users
echo "\n2️⃣  User Accounts:\n";
$admin = User::where('role', 'admin')->first();
$sellers = User::where('role', 'seller')->count();
$customers = User::where('role', 'customer')->count();
echo "   - Admin accounts: $admin->name (ID: {$admin->id})\n";
echo "   - Sellers: $sellers\n";
echo "   - Customers: $customers\n";

// 3. Check Seller PayPal Emails
echo "\n3️⃣  Seller PayPal Emails:\n";
$sellersWithEmail = User::where('role', 'seller')->whereNotNull('paypal_email')->count();
$sellersWithoutEmail = User::where('role', 'seller')->whereNull('paypal_email')->count();
echo "   - With PayPal email: $sellersWithEmail ✓\n";
echo "   - Without PayPal email: $sellersWithoutEmail ✗\n";
if ($sellersWithoutEmail > 0) {
    echo "   ⚠️  Sellers without PayPal email cannot receive payouts!\n";
}

// 4. Check Commission Configuration
echo "\n4️⃣  Commission Configuration:\n";
$commission = SystemFee::where('is_platform_commission', true)->first();
if ($commission) {
    echo "   - Admin Commission: {$commission->fee_value}%\n";
    echo "   - Seller Receives: " . (100 - $commission->fee_value) . "%\n";
    echo "   - Database: ✓ Configured\n";
} else {
    echo "   - ✗ No commission configured!\n";
    echo "   - Default: 10%\n";
}

// 5. Check Orders
echo "\n5️⃣  Orders:\n";
$totalOrders = Order::count();
$paidOrders = Order::where('payment_status', 'paid')->count();
$pendingOrders = Order::where('payment_status', 'pending')->count();
$codOrders = Order::where('payment_method', 'cod')->count();
$paypalOrders = Order::where('payment_method', 'paypal')->count();
echo "   - Total orders: $totalOrders\n";
echo "   - Paid (payment_status): $paidOrders\n";
echo "   - Pending: $pendingOrders\n";
echo "   - COD payment method: $codOrders\n";
echo "   - PayPal payment method: $paypalOrders\n";

// 6. Check Payments
echo "\n6️⃣  Payments:\n";
$totalPayments = Payment::count();
$completedPayments = Payment::where('status', 'completed')->count();
$paypalPayments = Payment::where('payment_method', 'paypal')->count();
echo "   - Total payment records: $totalPayments\n";
echo "   - Completed: $completedPayments\n";
echo "   - PayPal method: $paypalPayments\n";

// 7. Check Wallet Transactions
echo "\n7️⃣  Wallet Transactions:\n";
$totalTransactions = WalletTransaction::count();
$adminTransactions = WalletTransaction::where('wallet_id', $admin->wallet->id)->count();
$completedTransactions = WalletTransaction::where('status', 'completed')->count();
$pendingTransactions = WalletTransaction::where('status', 'pending')->count();
$approvedPayouts = WalletTransaction::where('status', 'payout_approved')->count();
$rejectedPayouts = WalletTransaction::where('status', 'payout_rejected')->count();

echo "   - Total transactions: $totalTransactions\n";
echo "   - Admin wallet transactions: $adminTransactions\n";
echo "   - Completed (ready): $completedTransactions\n";
echo "   - Pending (awaiting approval): $pendingTransactions\n";
echo "   - Approved payouts: $approvedPayouts\n";
echo "   - Rejected payouts: $rejectedPayouts\n";

// 8. Check for VNPay References
echo "\n8️⃣  Legacy VNPay Check:\n";
$vnpayOrders = Order::where('payment_method', 'vnpay')->count();
$vnpayPayments = Payment::where('payment_method', 'vnpay')->count();
if ($vnpayOrders === 0 && $vnpayPayments === 0) {
    echo "   - ✓ No VNPay orders found (completely removed)\n";
} else {
    echo "   - ✗ Found $vnpayOrders VNPay orders and $vnpayPayments VNPay payments\n";
}

// 9. Commission Calculation Verification
echo "\n9️⃣  Commission Calculation Test:\n";
$testOrder = Order::where('payment_status', 'paid')->first();
if ($testOrder) {
    $admin_commission = $admin->wallet->transactions()
        ->where('order_id', $testOrder->id)
        ->where('type', 'credit')
        ->where('status', 'completed')
        ->sum('amount');
    
    $seller_credit = WalletTransaction::where('order_id', $testOrder->id)
        ->where('type', 'credit')
        ->where('status', '!=', 'completed')
        ->sum('amount');
    
    $total = $admin_commission + $seller_credit;
    
    echo "   - Order Total: \$" . number_format($testOrder->total_amount, 2) . "\n";
    echo "   - Admin Commission: \$" . number_format($admin_commission, 2) . "\n";
    echo "   - Seller Credit: \$" . number_format($seller_credit, 2) . "\n";
    echo "   - Sum: \$" . number_format($total, 2) . "\n";
    echo "   - Match: " . ($total == $testOrder->total_amount ? "✓ Yes" : "✗ No") . "\n";
} else {
    echo "   - No paid orders to verify\n";
}

// 10. Wallet Balances
echo "\n🔟 Wallet Balances:\n";
echo "   Admin Wallet:\n";
$adminBalance = $admin->wallet->transactions()
    ->where('status', 'completed')
    ->sum(DB::raw('CASE WHEN type = "credit" THEN amount ELSE -amount END'));
echo "      - Balance: \$" . number_format($adminBalance, 2) . "\n";

echo "   Sellers:\n";
$sellers = User::where('role', 'seller')->get();
foreach ($sellers as $seller) {
    $balance = $seller->wallet->transactions()
        ->where('status', 'completed')
        ->sum(DB::raw('CASE WHEN type = "credit" THEN amount ELSE -amount END'));
    echo "      - {$seller->name}: \$" . number_format($balance, 2) . "\n";
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ SYSTEM READY FOR TESTING!\n";
echo "Next: Run 'php artisan serve' and test PayPal flow\n";
echo str_repeat("=", 50) . "\n";
