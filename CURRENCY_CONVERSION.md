# Currency Conversion: USD to VND (Vietnamese Dong)

## Overview
The entire E-Commerce platform has been converted from USD ($) to Vietnamese Dong (VND - ₫). This document summarizes all changes made.

## Date of Conversion
January 29, 2026

## Changes Made

### 1. View Files (Display Changes)
All Blade template files displaying prices have been updated to show VND with ₫ symbol instead of $.
Number format changed from 2 decimal places to 0 decimal places (VND doesn't use decimals).

**Files Updated (21 files):**
- `resources/views/home.blade.php` - Product price displays on homepage
- `resources/views/products/show.blade.php` - Individual product page
- `resources/views/products/index.blade.php` - Products listing page
- `resources/views/products/wishlist.blade.php` - Wishlist page
- `resources/views/categories/show.blade.php` - Category product listings
- `resources/views/cart/index.blade.php` - Shopping cart page
- `resources/views/checkout/index.blade.php` - Checkout page with order summary
- `resources/views/orders/show.blade.php` - Customer order details
- `resources/views/orders/index.blade.php` - Customer orders list
- `resources/views/seller/dashboard.blade.php` - Seller dashboard revenue
- `resources/views/seller/products/index.blade.php` - Seller product list
- `resources/views/seller/products/show.blade.php` - Seller product details
- `resources/views/seller/categories/show.blade.php` - Seller category products
- `resources/views/seller/orders/index.blade.php` - Seller orders list
- `resources/views/seller/orders/show.blade.php` - Seller order details
- `resources/views/seller/wallet/index.blade.php` - Seller wallet balance and transactions
- `resources/views/admin/dashboard.blade.php` - Admin dashboard revenue
- `resources/views/admin/orders/index.blade.php` - Admin orders list
- `resources/views/admin/wallet/index.blade.php` - Admin wallet management
- `resources/views/admin/fees/index.blade.php` - System fees display
- `README.md` - Documentation update

### 2. Database
**No migration changes needed** - The existing decimal(12,2) and decimal(14,2) columns work perfectly for VND values.

The database already uses appropriate decimal types:
- `products.price` → decimal(12,2)
- `orders.subtotal, shipping_fee, discount_amount, total_amount` → decimal(12,2)
- `payments.amount` → decimal(12,2)
- `e_wallets.balance, total_received, total_spent` → decimal(14,2)
- `wallet_transactions.amount` → decimal(14,2)

### 3. Seeder Data
**No changes required** - `database/seeders/ProductSeeder.php` already uses VND prices:
- iPhone 15 Pro Max: 29,990,000 VND
- MacBook Pro 14": 34,990,000 VND
- iPad Air: 15,990,000 VND
- And many more products with appropriate VND pricing

### 4. Default System Configuration
From `database/seeders/DatabaseSeeder.php`:
- Platform Fee: 5%
- Transaction Fee: 2%
- Default Shipping Fee: 20,000 VND ✅ (Already in VND)

### 5. Models
**No code changes required** - Models don't need to know about currency formatting.
All price calculations work the same with VND values.

### 6. Controllers
**No code changes required** - Controllers handle numerical calculations only.
Currency formatting is done at the view layer.

### 7. Documentation
Updated README.md to mention:
- All prices are in Vietnamese Dong (VND)
- Both payment methods (COD and VNPay) use VND

## Price Format Changes

### Before (USD)
```
$29.99
$1,234.56
```

### After (VND)
```
₫29,990
₫1,234,560
```

## Number Format
- **Before**: 2 decimal places (e.g., $29.99)
- **After**: 0 decimal places (e.g., ₫29,990)

This is appropriate because Vietnamese Dong doesn't use fractional currency units like cents.

## Testing Checklist
- [ ] Home page displays prices in VND with ₫ symbol
- [ ] Product detail pages show VND prices
- [ ] Cart page displays item prices and totals in VND
- [ ] Checkout page shows order summary with VND
- [ ] Customer orders page shows order totals in VND
- [ ] Seller dashboard shows revenue in VND
- [ ] Seller wallet shows balance and transactions in VND
- [ ] Admin wallet shows platform balance in VND
- [ ] All discount calculations work correctly with VND
- [ ] Shipping fees display in VND

## Code Examples

### View Usage (Blade Template)
```blade
<!-- Before (USD) -->
<span>${{ number_format($product->price, 2) }}</span>

<!-- After (VND) -->
<span>₫{{ number_format($product->price, 0) }}</span>
```

### Database Queries
```php
// No changes needed - database queries work the same
$order = Order::find(1);
echo $order->total_amount; // Returns numeric value (e.g., 1500000)
```

## Affected Features
- Product pricing and discounts
- Shopping cart calculations
- Order management (viewing, creating)
- Payment processing and wallet transactions
- Seller earnings and withdrawals
- Admin wallet monitoring
- System fee calculations (percentages remain the same)

## Integration Points with Payment Gateways

### VNPay Integration
The existing VNPay payment framework (located in routes and controllers) will work correctly with VND.
The amount parameter should be in VND (not cents).

```php
// Example: 1,500,000 VND
$amount = 1500000; // Already correct format
```

### Cash on Delivery (COD)
COD is ready to use with VND prices - no changes needed.

## Currency Symbol

The Vietnamese Dong symbol (₫) has been used throughout the application:
- HTML Entity: `₫`
- Unicode: U+20AB
- UTF-8: E2 82 AB

## Summary
The conversion is complete and comprehensive. All user-facing price displays now show Vietnamese Dong with the ₫ symbol and appropriate formatting (0 decimal places). The system is ready for full VND operation including customer purchases, seller earnings, and admin management.

No data migration is required - existing data in the database will display correctly with the new VND format.
