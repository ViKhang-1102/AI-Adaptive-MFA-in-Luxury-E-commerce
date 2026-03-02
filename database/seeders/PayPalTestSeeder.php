<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\EWallet;
use App\Models\WalletTransaction;

class PayPalTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test seller with PayPal email
        $seller = User::updateOrCreate(
            ['email' => 'seller-test@example.com'],
            [
                'role' => 'seller',
                'name' => 'Test Seller',
                'password' => bcrypt('password123'),
                'paypal_email' => 'sb-xxxxxx@business.example.com',
                'is_active' => true,
            ]
        );

        // Create test customer
        $customer = User::updateOrCreate(
            ['email' => 'customer-test@example.com'],
            [
                'role' => 'customer',
                'name' => 'Test Customer',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );

        // Create admin if needed
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'role' => 'admin',
                'name' => 'Admin User',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );

        // Ensure wallets exist for all users
        foreach ([$admin, $seller, $customer] as $user) {
            // Refresh user to get updated wallet relation
            $user->refresh();
            if (!$user->wallet) {
                EWallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'total_received' => 0,
                    'total_spent' => 0,
                ]);
            }
        }

        // Get fresh wallet instances after creation
        $admin = $admin->fresh();
        $seller = $seller->fresh();
        $customer = $customer->fresh();

        // Add test transactions to admin wallet (simulate PayPal commissions)
        $adminWallet = $admin->wallet;
        
        // Simulate 5 commission transactions
        $commissions = [
            ['amount' => 25000, 'description' => 'Commission from Order #1 (10% of 250000)'],
            ['amount' => 10000, 'description' => 'Commission from Order #2 (10% of 100000)'],
            ['amount' => 15000, 'description' => 'Commission from Order #3 (10% of 150000)'],
            ['amount' => 20000, 'description' => 'Commission from Order #4 (10% of 200000)'],
            ['amount' => 30000, 'description' => 'Commission from Order #5 (10% of 300000)'],
        ];

        $totalCommission = 0;
        foreach ($commissions as $i => $commission) {
            WalletTransaction::create([
                'wallet_id' => $adminWallet->id,
                'type' => 'credit',
                'amount' => $commission['amount'],
                'description' => $commission['description'],
                'status' => 'completed',
                'created_at' => now()->subDays($i),
                'updated_at' => now()->subDays($i),
            ]);
            $totalCommission += $commission['amount'];
        }

        // Update admin wallet balance
        $adminWallet->update([
            'balance' => $totalCommission,
            'total_received' => $totalCommission,
        ]);

        // Add test transactions to seller wallet (simulate seller earnings)
        $sellerWallet = $seller->wallet;
        $sellerEarnings = [
            ['amount' => 225000, 'description' => 'Payment from Order #1 (90% of 250000)'],
            ['amount' => 90000, 'description' => 'Payment from Order #2 (90% of 100000)'],
            ['amount' => 135000, 'description' => 'Payment from Order #3 (90% of 150000)'],
        ];

        $totalEarnings = 0;
        foreach ($sellerEarnings as $i => $earning) {
            WalletTransaction::create([
                'wallet_id' => $sellerWallet->id,
                'type' => 'credit',
                'amount' => $earning['amount'],
                'description' => $earning['description'],
                'status' => 'completed',
                'created_at' => now()->subDays($i),
                'updated_at' => now()->subDays($i),
            ]);
            $totalEarnings += $earning['amount'];
        }

        // Update seller wallet balance
        $sellerWallet->update([
            'balance' => $totalEarnings,
            'total_received' => $totalEarnings,
        ]);

        echo "\n✅ Test users created with wallets:\n";
        echo "Admin:    admin@example.com | Balance: ₫" . number_format($adminWallet->balance, 0) . "\n";
        echo "Seller:   seller-test@example.com | Balance: ₫" . number_format($sellerWallet->balance, 0) . "\n";
        echo "Customer: customer-test@example.com\n";
        echo "\n✅ Sample transactions added for dashboard demo\n";
    }
}
