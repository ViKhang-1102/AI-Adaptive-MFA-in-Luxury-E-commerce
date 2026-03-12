# ⚡ PayPal Marketplace - Quick Reference Card

## 🚀 Getting Started (5 Minutes)

### 1. Setup Configuration
```bash
cd c:\laragon\www\E-commerce2026
php artisan tinker
>>> @include 'setup-payment.php'
>>> exit
```

### 2. Start Server
```bash
php artisan serve
# Visit: http://localhost:8000
```

### 3. Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | password |
| Seller | seller-test@example.com | password |
| Customer | customer-test@example.com | password |

---

## 💰 Payment Flow Summary

```
Customer pays $100 (PayPal)
    ├─ Admin: 10% = $10 ✓ (ready)
    └─ Seller: 90% = $90 ⏳ (awaiting approval)

Admin clicks "Approve"
    ├─ PayPal sends $90 to seller email
    └─ Seller: $90 ✓ (received)
```

---

## 📋 Key Pages

| Page | URL | Purpose |
|------|-----|---------|
| Commission Config | `/admin/fees` | Set admin commission % |
| Wallet Management | `/admin/wallet` | Review & approve payouts |
| Seller Earnings | `/seller/wallet` | View balance & earnings |
| Checkout | `/checkout` | Place order with PayPal |
| Payment Success | `/paypal/success` | Order confirmation |

---

## 🔑 Configuration

### .env PayPal Settings
```
PAYPAL_MODE=sandbox
PAYPAL_SANDBOX_CLIENT_ID=<your-client-id>
PAYPAL_SANDBOX_SECRET=<your-secret>
PAYPAL_CURRENCY=USD
```

### Commission Setting (Database)
```sql
-- View current commission
SELECT fee_value FROM system_fees WHERE is_platform_commission = 1;
-- Default: 10% admin, 90% seller
```

---

## 🧪 Quick Test (2 Minutes)

1. **Admin Sets Commission**
   - Login: http://localhost:8000/login
   - Go: `/admin/fees`
   - Set: 10%
   - Save

2. **Customer Pays**
   - Login: customer-test@example.com
   - Add product to cart
   - Checkout → Select "PayPal" → Place Order
   - PayPal Sandbox: Approve

3. **Admin Approves**
   - Go: `/admin/wallet`
   - Click: "Approve" button
   - Verify: Status changes to "✓ Paid"

---

## 🐛 Common Issues

| Problem | Solution |
|---------|----------|
| Seller PayPal email missing | `Setup` must be run first |
| "Undefined variable $balance" | Already fixed in WalletController |
| "Route [paypal.create] not found" | Ensure migrations ran and cache clear |
| PayPal error "Invalid credentials" | Check .env PAYPAL_SANDBOX_* values |
| No transactions showing | Check if orders have `payment_status='paid'` |

---

## 📊 Database Queries

```php
// Current commission
SystemFee::where('is_platform_commission', true)->first()->fee_value

// Pending seller payouts
WalletTransaction::where('status', 'pending')->get()

// Admin balance
User::find(1)->wallet->transactions()
    ->where('status', 'completed')
    ->sum('amount')

// Seller earnings
$seller->wallet->transactions()
    ->where('status', 'completed')
    ->where('type', 'credit')
    ->sum('amount')
```

---

## 📝 Logs Location

```
storage/logs/laravel.log
```

Contains:
- PayPal API calls
- Payment processing
- Payout approvals
- Errors and exceptions

---

## ✅ Verification Commands

```bash
# Check all systems
php artisan tinker --execute="@include 'verify-payment-system.php'"

# Verify routes
php artisan route:list | grep paypal

# Check PHP syntax
php -l app/Http/Controllers/PayPalController.php

# View logs
tail -100 storage/logs/laravel.log
```

---

## 🔄 Payment Status Map

| Status | Meaning | User |
|--------|---------|------|
| pending | Awaiting payment | Customer → PayPal |
| completed | Payment captured | System → Admin |
| payout_approved | Funds sent | Admin → Seller |
| payout_rejected | Payment denied | Admin reason given |

---

## 🎯 Key Files Modified

- ✅ OrderController.php (removed VNPay)
- ✅ PayPalController.php (dynamic commission)
- ✅ Seller/WalletController.php (fixed balance calc)
- ✅ Admin/WalletController.php (transactions query)
- ✅ Admin/TransactionController.php (enhanced logging)
- ✅ checkout/index.blade.php (removed VNPay label)
- ✅ admin/wallet/index.blade.php (added actions)

---

## 🔐 Security Checklist

- ✓ PayPal credentials in .env (not in code)
- ✓ No sensitive data in logs
- ✓ Seller email validated
- ✓ Amount precision (2 decimals)
- ✓ All DB queries parameterized

---

## 📞 Support

**Issues?**
1. Check tail logs: `tail storage/logs/laravel.log`
2. Run verification: `php artisan tinker @include 'tests/dev-tools/verify-payment-system.php'`
3. Review: [PAYPAL_TESTING_GUIDE.md](PAYPAL_TESTING_GUIDE.md)

**API References:**
- PayPal Sandbox: https://developer.paypal.com/tools/sandbox
- API Docs: https://developer.paypal.com/api/rest/
- srmklive/paypal: https://github.com/srmklive/paypal

---

**Status**: ✅ Ready to Test  
**Test File**: [PAYPAL_TESTING_GUIDE.md](PAYPAL_TESTING_GUIDE.md)  
**Complete Guide**: [PAYPAL_COMPLETE_FIX.md](PAYPAL_COMPLETE_FIX.md)
