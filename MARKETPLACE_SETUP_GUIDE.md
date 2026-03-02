# PayPal Marketplace Integration Setup Guide

## ✅ Completed Setup

All PayPal Marketplace integration components have been successfully implemented. Follow these terminal commands to finalize and test the system.

## 📋 Prerequisites

- PHP 8.2+
- Composer installed
- MySQL running
- Laravel 11 development server

## 🚀 Setup Terminal Commands

### 1. **Install Dependencies** (If not already installed)
```bash
# Navigate to project directory
cd c:\laragon\www\E-commerce2026

# Install PHP packages
composer install

# Install PayPal package (if needed)
composer require srmklive/paypal:^3.0
```

### 2. **Environment Configuration**
```bash
# Copy .env file
cp .env.example .env

# Generate application key
php artisan key:generate

# Update .env with PayPal Sandbox credentials:
# PAYPAL_MODE=sandbox
# PAYPAL_SANDBOX_CLIENT_ID=ASQxWYMphMw... (from PayPal Developer)
# PAYPAL_SANDBOX_SECRET=xX... (from PayPal Developer)
# PAYPAL_CURRENCY=USD
```

### 3. **Database Setup**
```bash
# Run all migrations (including new commission & payout fields)
php artisan migrate

# Seed test data (creates 3 sample users with wallets)
php artisan db:seed --class=PayPalTestSeeder

# Clear cache and rebuild
php artisan cache:clear
php artisan config:cache
```

### 4. **Start Development Server**
```bash
# Terminal 1: Start Laravel development server
php artisan serve

# Server will run on http://localhost:8000

# Terminal 2 (Optional): Run queue worker for async jobs
php artisan queue:work
```

## 🧪 Testing the Marketplace

### Test Scenario 1: Admin Configures Commission

1. **Login as Admin**
   - URL: http://localhost:8000/login
   - Email: `admin@example.com`
   - Password: `password`

2. **Navigate to Commission Configuration**
   - URL: http://localhost:8000/admin/fees
   - You'll see the commission slider
   - Change from 10% to your desired percentage (e.g., 15%)
   - Click "Update Commission"
   - Message confirms: "Platform commission updated to 15%"

### Test Scenario 2: Make a Test Payment

1. **Login as Customer**
   - Email: `customer-test@example.com`
   - Password: `password`

2. **Create a Test Order**
   - Browse products and add to cart
   - Proceed to checkout
   - Click "Pay with PayPal"

3. **Complete PayPal Payment**
   - Use PayPal Sandbox credentials
   - Buyer: `sb-123456@personal.example.com`
   - Password: PayPal Sandbox password
   - Approve payment

4. **Verify Commission Split**
   - System automatically calculates:
     - Admin Commission: Order Total × (Configured %)
     - Seller Amount: Order Total × (100% - Admin %)
   - Both wallet transactions created with correct amounts

### Test Scenario 3: Admin Approves/Rejects Seller Payout

1. **Navigate to Admin Wallet**
   - URL: http://localhost:8000/admin/wallet
   - View "Recent Transactions" table with seller pending payouts

2. **Approve Payout**
   - Find pending seller transaction
   - Click green "Approve" button
   - Confirm dialog
   - Transaction processes via PayPal Payout API
   - Status changes to "✓ Paid"
   - PayPal Batch ID stored as reference

3. **Reject Payout (Alternative)**
   - Click red "Reject" button
   - Modal appears for rejection reason
   - Enter reason (e.g., "Seller verification failed")
   - Status changes to "✗ Rejected"
   - Reason stored in transaction record

## 📊 Dashboard Verification

After testing, verify metrics on Admin Wallet Dashboard:

```
✓ Total Platform Balance: Should show sum of all admin commissions
✓ Total Seller Wallets: Should show sum of all seller pending amounts
✓ Total Transactions: Should increase with each test payment
✓ Recent Transactions: Shows all credit/debit entries with status
```

## 🔧 Troubleshooting

### Error: "PayPal Error: Invalid Client ID"
```bash
# Verify PayPal credentials in .env
php artisan tinker
>>> config('paypal.sandbox.client_id')
# Should output your Client ID, not null
```

### Error: "Collection::links does not exist"
```bash
# This is FIXED - Confirm FeeController uses paginate()
grep -n "paginate" app/Http/Controllers/Admin/FeeController.php
# Should show SystemFee::paginate(10)
```

### Error: "transactions table missing payout columns"
```bash
# Run pending migrations
php artisan migrate --step

# Verify columns exist
php artisan tinker
>>> Schema::getColumns('wallet_transactions')
# Should include: payout_approved_at, payout_rejected_at, is_platform_commission
```

### No Transactions Appearing
```bash
# Check if seeder ran
SELECT COUNT(*) FROM wallet_transactions;

# Re-seed if needed
php artisan db:seed --class=PayPalTestSeeder --force
```

## 📁 File Structure Created

```
app/Http/Controllers/
├── PayPalController.php (Updated with dynamic commission)
├── Admin/
│   ├── FeeController.php (Updated with paginate + updatePlatformCommission)
│   ├── TransactionController.php (NEW - Approve/Reject logic)
│   └── WalletController.php (Dashboard metrics)

resources/views/
├── admin/
│   ├── fees/
│   │   ├── index.blade.php (Updated with commission form)
│   │   ├── create.blade.php
│   │   └── edit.blade.php
│   └── wallet/
│       └── index.blade.php (Updated with Approve/Reject buttons + modal)
├── paypal/
│   ├── success.blade.php
│   ├── cancel.blade.php
│   └── button.blade.php

database/migrations/
└── 2026_02_26_add_commission_and_payout_fields.php (NEW)

routes/
└── web.php (Updated with transaction routes)
```

## 🔐 Security Considerations

1. **PayPal Credentials**: Never commit .env to Git
   ```bash
   # .env is already in .gitignore
   ```

2. **Admin Access Control**: Transaction approval limited to Admin role
   ```php
   // Middleware enforces: 'auth', AdminMiddleware::class
   ```

3. **Validation**: All inputs validated before DB storage
   ```php
   // Commission: numeric 0-100
   // Rejection reason: string max 500 chars
   ```

4. **Error Logging**: PayPal API errors logged to Laravel logs
   ```bash
   tail -f storage/logs/laravel.log
   ```

## 📞 Support Information

**Key Implementation Details:**
- Commission stored in `system_fees` table with `is_platform_commission=true`
- Payout status tracked in `wallet_transactions.status` (pending/payout_approved/payout_rejected)
- PayPal Batch IDs stored in `reference_code` for payment reconciliation
- Seller emails must be filled in `users.paypal_email` for payout to work

**Database Queries:**

```bash
# Check current commission setting
php artisan tinker
>>> SystemFee::where('is_platform_commission', true)->first()

# View pending seller payouts
>>> WalletTransaction::where('status', 'pending')->where('type', 'credit')->get()

# Total admin commission collected
>>> WalletTransaction::where('status', 'completed')
    ->where('type', 'credit')
    ->where('wallet_id', Auth::user()->wallet->id)
    ->sum('amount')
```

## ✨ Features Overview

| Feature | Status | Route |
|---------|--------|-------|
| Create PayPal Payment | ✅ Complete | `GET /paypal/create/{order}` |
| Process PayPal Success | ✅ Complete | `GET /paypal/success` |
| Admin Commission Config | ✅ Complete | `POST /admin/fees/commission/update` |
| Seller Wallet Dashboard | ✅ Complete | `GET /seller/wallet` |
| Admin Wallet Management | ✅ Complete | `GET /admin/wallet` |
| Approve Seller Payout | ✅ Complete | `POST /admin/transactions/{id}/approve` |
| Reject Seller Payout | ✅ Complete | `POST /admin/transactions/{id}/reject` |
| Dynamic Commission Split | ✅ Complete | Uses `SystemFee::getPlatformCommission()` |
| PayPal Payout API | ✅ Complete | Integration via `createBatch()` |

## 🎓 Quick Reference Commands

```bash
# Start everything
php artisan serve
php artisan queue:work

# Test specific route
php artisan route:list | Select-String "transaction"

# Check database changes
php artisan tinker
>>> DB::table('system_fees')->get()
>>> DB::table('wallet_transactions')->get()

# Debug PayPal config
>>> config('paypal')

# View recent logs
tail -n 50 storage/logs/laravel.log

# Reset for fresh test
php artisan migrate:refresh --seed --class=PayPalTestSeeder
```

---

**Last Updated:** 2026-02-26  
**Version:** 1.0  
**Status:** ✅ Ready for Production Testing
