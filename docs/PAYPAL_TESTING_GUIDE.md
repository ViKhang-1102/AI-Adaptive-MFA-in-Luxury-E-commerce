# PayPal Marketplace - Complete Testing Guide

## 📋 System Overview

The PayPal Marketplace system connects three parties:
- **Customer**: Pays via PayPal Sandbox
- **Seller**: Receives funds after admin approval  
- **Admin**: Approves payouts and manages commission rates

### Payment Flow

```
Customer pays $100 → PayPal captures funds → System splits commission:
├─ Admin receives 10% ($10) → Status: COMPLETED, ready to withdraw
└─ Seller receives 90% ($90) → Status: PENDING, awaits admin approval

Admin approves payout → PayPal Payout API sends to seller's email
```

---

## 🔑 PayPal Sandbox Credentials Setup

### 1. Get PayPal Sandbox Accounts

Go to: https://developer.paypal.com/tools/sandbox

Create these accounts:
- **Business Account** (Admin): For marketplace approval
- **Personal Account** (Customer): For making payments  
- **Personal Account** (Seller): For receiving payouts

### 2. Configure .env File

```bash
# PayPal Sandbox Settings
PAYPAL_MODE=sandbox
PAYPAL_SANDBOX_CLIENT_ID=ASQxWYMphMw... (from your Business app)
PAYPAL_SANDBOX_SECRET=xX... (from your Business app)
PAYPAL_SANDBOX_APP_ID=APP-80W284485P519543T
PAYPAL_CURRENCY=USD
```

### 3. Link Seller PayPal Email

Each seller must have their PayPal email set:
```bash
php artisan tinker
>>> $seller = User::where('role', 'seller')->first()
>>> $seller->update(['paypal_email' => 'sb-seller123@personal.example.com'])
```

---

## 🚀 Step-by-Step Testing

### **Phase 1: Admin Configures Commission**

**1.1 Login as Admin**
```
Email: admin@example.com
Password: password
URL: http://localhost:8000/login
```

**1.2 Navigate to Commission Settings**
```
URL: http://localhost:8000/admin/fees
```

**1.3 Set Commission (Example: 15%)**
- Input field shows: "Admin Commission Percentage (%)"
- Enter: `15`
- Click: "Update Commission"
- Verify message: "Platform commission updated to 15%"
- Check display: "Admin receives: 15%" | "Seller receives: 85%"

**1.4 Database Verification**
```bash
php artisan tinker
>>> SystemFee::where('is_platform_commission', true)->first()
# Should show: fee_value = 15
```

---

### **Phase 2: Customer Makes Purchase with PayPal**

**2.1 Login as Customer**
```
Email: customer-test@example.com
Password: password
URL: http://localhost:8000
```

**2.2 Add Product to Cart**
- Browse: Products section
- Select any product
- Click: "Add to Cart"
- Verify: Cart icon shows item count

**2.3 Proceed to Checkout**
- URL: http://localhost:8000/checkout
- Scroll down to "Payment Method"
- Select: "Online Payment (PayPal)" radio button
- Delivery address: Select default or add new
- Click: "Place Order"

**2.4 Redirected to PayPal Checkout**
- URL changes to: paypal.com sandbox login
- Email: `sb-mycustomer123@personal.example.com`
- Password: Your PayPal Sandbox personal password
- Click: "Continue"
- Review: Order details should match
- Click: "Approve"

**2.5 Payment Processing**
- You'll be redirected back to: `/paypal/success?order_id=1&token=...`
- Page shows success message
- Displays commission breakdown:
  - Order Total: ₫[amount]
  - Admin Commission (15%): ₫[15% of amount]
  - Seller Receives (85%): ₫[85% of amount]
  - Seller PayPal: sb-seller@...

**2.6 Database Verification - Payment Created**
```bash
php artisan tinker
>>> Order::find(1)->load('payment', 'seller')
# Should show: payment_status = 'paid', status = 'processing'

>>> Payment::find(1)
# Should show: status = 'completed', amount = [total], method = 'paypal'
```

---

### **Phase 3: Wallet Transactions Created**

**3.1 Admin Wallet - Receives Commission**
```bash
php artisan tinker

# Verify admin transaction created (COMPLETED - immediately available)
>>> WalletTransaction::where('type', 'credit')
                       ->where('status', 'completed')
                       ->first()
# Should show:
# - wallet_id: 1 (admin)
# - amount: [15% of order total]
# - description: "Platform commission from Order #1 (15%)"
# - status: 'completed'
```

**3.2 Seller Wallet - Awaiting Approval**
```bash
php artisan tinker

# Verify seller transaction created (PENDING - awaiting payout approval)
>>> WalletTransaction::where('type', 'credit')
                       ->where('status', 'pending')
                       ->first()
# Should show:
# - wallet_id: [seller wallet id]
# - amount: [85% of order total]  
# - description: "Order #1 payment (85%)"
# - status: 'pending'  <-- IMPORTANT: Awaiting admin approval
```

---

### **Phase 4: Admin Reviews & Approves Payout**

**4.1 Login as Admin**
```
Email: admin@example.com
Password: password
```

**4.2 Navigate to Wallet Management**
```
URL: http://localhost:8000/admin/wallet
```

**4.3 Review Dashboard Metrics**
- **Total Platform Balance**: Should show admin commission amount (₫[15% of order])
- **Total Seller Wallets**: Should show ₫[85% of order] (seller pending amount)
- **Total Transactions**: Should increase by 2 (admin + seller transactions)

**4.4 Review Transactions Table**
- **Date**: Shows order payment time
- **User/Seller**: Shows seller name
- **Type**: "Credit" (money added)
- **Amount**: Shows seller's ₫[85%] amount
- **Status**: "pending" (awaiting payout approval)
- **Description**: "Order #1 payment (85%)"
- **Actions Column**: Shows "Approve" (green) and "Reject" (red) buttons

**4.5 Approve Payout**
- Click: "Approve" button for pending transaction
- Confirmation dialog appears
- Click: "Approve" to confirm
- System processes:
  1. Calls PayPal Payout API
  2. Sends ₫[85%] to seller's PayPal email
  3. Stores PayPal Batch ID as reference
  4. Updates status: `pending` → `payout_approved`

**4.6 Verify Approval Success**
- Page shows success message: "✓ Payout approved! Seller will receive ₫[amount] via PayPal"
- Status button changes: "Approve/Reject" → "✓ Paid"
- Transaction row stays visible with new status

---

### **Phase 5: Seller Receives Payment**

**5.1 Check Seller Wallet**
```
Email: seller-test@example.com
Password: password
URL: http://localhost:8000/seller/wallet
```

**5.2 Verify Completed Payment**
- **Available Balance**: Shows ₫[85% of order amount]
- **Total Earned**: Shows ₫[85%] (after commission deduction)
- **Transaction History**: Lists "Order #1 payment (85%)"
- **Status**: Shows as "completed" after PayPal payout

**5.3 Check PayPal Sandbox**
- Go to: https://www.sandbox.paypal.com
- Login: seller-test@personal.example.com
- Check notifications/activity
- Should see: Payout received from marketplace account

---

### **Phase 6: Test Rejection Flow**

**6.1 Go Back to Admin Wallet**
```
URL: http://localhost:8000/admin/wallet
```

**6.2 Create Another Order (For Testing Rejection)**
- As customer: Place new order
- PayPal payment succeeds
- New "pending" transaction appears in admin wallet

**6.3 Reject Payout**
- Click: "Reject" button (red)
- Modal form appears: "Reject Payout"
- Enter reason: (e.g., "Seller account verification failed")
- Click: "Reject Payout"
- System marks: Status = `payout_rejected`

**6.4 Verify Rejection**
- Transaction row shows: "✗ Rejected" status
- Admin can see rejection reason in transaction details
- Seller can reapply once issue is resolved

---

## 🧪 Complete Test Scenario (15 minutes)

### Quick Test Command Setup

```bash
# Terminal 1: Start Laravel server
cd c:\laragon\www\E-commerce2026
php artisan serve

# Terminal 2: Monitor logs
tail -f storage/logs/laravel.log
```

### Test Sequence

**T+0: Commission Configuration**
```
Admin logs in → /admin/fees → Set commission to 10% → Save
```

**T+2: Customer Payment**
```
Customer → Add product → Checkout → PayPal Sandbox login
Approve payment → Redirected to success page
```

**T+3: Verify Transactions**
```
php artisan tinker
>>> WalletTransaction::all() [should show 2 entries: admin completed + seller pending]
```

**T+5: Admin Approval**
```
Admin → /admin/wallet → Click "Approve" → Confirm
Verify: PayPal Payout API called → Batch ID stored
```

**T+8: Seller Verification**
```
Seller → /seller/wallet → Check balance shows correct amount
```

**T+10: Test Rejection**
```
Place another order → Payment succeeds → Admin rejects with reason
Verify: Status = payout_rejected → Reason logged
```

---

## 🛠️ Troubleshooting

### Error: "Seller not found or missing PayPal email"
```bash
# Solution: Set seller's PayPal email
php artisan tinker
>>> $seller = User::where('role', 'seller')->first()
>>> $seller->update(['paypal_email' => 'sb-your-seller@personal.example.com'])
```

### Error: "Only pending transactions can be approved"
- Probably trying to approve already approved transaction
- Verify transaction status: `php artisan tinker >>> WalletTransaction::find(id)->status`

### Error: "PayPal Error: Invalid authentication signature"
- Check .env file has correct PAYPAL_SANDBOX_CLIENT_ID and SECRET
- Verify API credentials from https://developer.paypal.com/dashboard

### Orders not showing in wallet dashboard
```bash
# Verify transactions exist
php artisan tinker
>>> WalletTransaction::count()  # Should be > 0
>>> Order::where('payment_status', 'paid')->count()
```

### Commission not showing on success page
- Verify: `SystemFee::getPlatformCommission()` returns a value
- Check database: `SELECT * FROM system_fees WHERE is_platform_commission = 1`

---

## 📊 Expected Database State After Full Test

```sql
-- Assuming order total was $10 USD with 10% admin commission

-- Orders Table
Orders: 1 record
  - Id: 1
  - customer_id: [customer user id]
  - seller_id: [seller user id]
  - total_amount: 10.00
  - payment_status: 'paid'
  - status: 'processing'

-- Payments Table
Payments: 1 record
  - Id: 1
  - order_id: 1
  - payment_method: 'paypal'
  - status: 'completed'
  - amount: 10.00
  - transaction_id: [PayPal Order ID]

-- Wallet Transactions Table
WalletTransactions: 2 records
  1) Admin Commission:
     - wallet_id: 1 (admin)
     - order_id: 1
     - type: 'credit'
     - amount: 1.00 (10% of $10)
     - status: 'completed'
     - payout_approved_at: null
     
  2) Seller Payment:
     - wallet_id: [seller wallet id]
     - order_id: 1
     - type: 'credit'
     - amount: 9.00 (90% of $10)
     - status: 'payout_approved' (after admin approval)
     - payout_approved_at: [timestamp]
     - reference_code: [PayPal Batch ID]

-- System Fees Table
SystemFees: 1 record
  - Id: 1
  - name: 'Platform Commission'
  - is_platform_commission: true
  - fee_type: 'percentage'
  - fee_value: 10
```

---

## 🔐 Security Notes

1. **Never commit .env to Git** - It contains PayPal credentials
2. **Sandbox vs Live** - Always test in Sandbox first
3. **Error Logging** - All PayPal errors logged to `storage/logs/laravel.log`
4. **Amount Precision** - All amounts formatted to 2 decimal places for PayPal API
5. **Seller Email Validation** - PayPal email must match account used for Sandbox

---

## 📝 Key Files to Review

- **PayPalController.php**: Payment flow and commission calculation
- **Admin/TransactionController.php**: Payout approval/rejection logic
- **Admin/FeeController.php**: Commission rate configuration
- **Seller/WalletController.php**: Seller balance calculation (only completed transactions)
- **wallet_transactions table**: Transaction logs with status tracking

---

## ✅ Success Criteria

- [ ] Customer can complete payment via PayPal Sandbox
- [ ] Commission split applies automatically (configurable % works)
- [ ] Admin sees both admin and seller transactions in wallet
- [ ] Admin can approve/reject seller payouts
- [ ] Seller receives correct amount in PayPal account
- [ ] All amounts correctly formatted with 2 decimals
- [ ] Transaction history shows proper statuses
- [ ] No VNPay references remain in UI

---

**Version**: 1.0  
**Last Updated**: 2026-02-26  
**Status**: Ready for Testing
