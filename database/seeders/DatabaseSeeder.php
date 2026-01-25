<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\EWallet;
use App\Models\SystemFee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin Account
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'role' => 'admin',
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'is_active' => true,
            ]
        );

        // Create wallet for admin
        if (!$admin->wallet) {
            EWallet::create([
                'user_id' => $admin->id,
                'balance' => 0,
                'total_received' => 0,
                'total_spent' => 0,
            ]);
        }

        // Create System Fees (default)
        SystemFee::firstOrCreate(
            ['id' => 1],
            [
                'platform_fee_percent' => 5,
                'transaction_fee_percent' => 2,
                'shipping_fee_default' => 20000,
                'description' => 'Default system fees',
            ]
        );

        // Call other seeders
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            BannerSeeder::class,
        ]);
    }
}
