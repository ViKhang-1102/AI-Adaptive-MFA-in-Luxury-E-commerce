# E-Commerce Platform - Multi-Vendor Marketplace (Shopee-inspired)

A complete, production-ready multi-vendor e-commerce platform built with **Laravel 11**, **MySQL**, and **Tailwind CSS**. This platform enables multiple sellers to list and sell products while customers browse, compare, and purchase items from multiple sellers in one transaction.

## 🎯 Key Features

### 🛍️ Customer Features
- **User Authentication**: Secure registration and login system
- **Product Catalog**: Browse products by category with advanced filtering
- **Search & Filter**: Find products by name, category, price range, and ratings
- **Product Details**: View images, descriptions, prices, and customer reviews
- **Shopping Cart**: Add/remove items, update quantities, persistent cart
- **Wishlists**: Save favorite products for later
- **Multi-Seller Checkout**: Buy from multiple sellers in one order
- **Buyer–Seller Chat**: Direct messaging between customers and sellers for product questions
- **Order Management**: Track orders, cancel pending orders, view history
- **Delivery Addresses**: Manage multiple delivery addresses
- **Payment Methods**: Cash on Delivery (COD) and VNPay online payment
- **User Profile**: Update personal info, change password, manage account
- **E-Wallet**: Track wallet balance and transaction history
- **Reviews & Ratings**: Leave reviews and ratings on purchased products

### 🏪 Seller Features
- **Seller Dashboard**: Overview of sales, revenue, and performance metrics
- **Product Management**: Create, edit, delete products with images
- **Inventory Control**: Manage stock levels, automatic stock reduction on sales
- **Pricing & Discounts**: Set prices and time-based discounts
- **Order Management**: View, confirm, and fulfill customer orders
- **Category Management**: Select available categories for products
- **Order Tracking**: Update order status (processing, shipped, delivered)
- **Wallet Management**: Track earnings, view transaction history
- **Analytics**: View sales statistics and performance data

### 👨‍💼 Admin Features
- **Admin Dashboard**: Platform statistics (users, orders, revenue)
- **User Management**: Manage customers and sellers
- **Category Management**: Create and organize product categories
- **Promotional Banners**: Create and schedule promotional banners
- **Fee Configuration**: Set platform fees and transaction fees
- **System Monitoring**: Monitor platform wallet and transactions
- **Order Management**: View all orders across all sellers
- **System Settings**: Configure system fees and default values

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+ (included in Laragon)
- MySQL 5.7+ (included in Laragon)
- Composer
- Laragon

### Installation (5 minutes)

```bash
cd C:\laragon\www\E-commerce2026
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
```

Visit: `http://localhost:8000`

**Admin Login:**
- Email: `admin@gmail.com`
- Password: `admin123`

**For detailed setup instructions**, see [INSTALLATION.md](INSTALLATION.md)

## 📚 Documentation & Dev Tools
- **Docs folder**: See `docs/` for deep dives (chat system, testing guides, deployment notes, etc.)
- **Dev test scripts**: see `tests/dev-tools/` for one-off diagnostics (chat system, order flows, AI risk scenarios, PayPal config, shipper webhook, etc.)

## 📁 Project Structure

```
E-commerce2026/
├── app/Models/                   # 16 Eloquent models
├── app/Http/Controllers/         # 18 controllers (Customer, Seller, Admin)
├── app/Http/Middleware/          # Role-based access control
├── database/migrations/          # 5 migration files, 26 tables
├── database/seeders/             # Initial data with admin account
├── resources/views/              # 57 Blade templates
│   ├── layouts/                  # Master layout, header, footer
│   ├── auth/                     # Login, register
│   ├── products/                 # Product listing, details
│   ├── checkout/                 # Checkout process
│   ├── orders/                   # Order tracking
│   ├── seller/                   # Seller dashboard (10 views)
│   └── admin/                    # Admin panel (12 views)
├── routes/web.php                # 40+ routes organized by role
└── .env                          # Environment configuration
```

## 🗄️ Database Schema (26 Tables)

**User Management**: users, password_reset_tokens, sessions
**Product Catalog**: categories, seller_categories, products, product_images, product_reviews
**Shopping**: carts, cart_items, orders, order_items, wishlists, customer_addresses
**Payments**: payments, banners, system_fees, e_wallets, wallet_transactions

## 🎨 Technology Stack

| Component | Technology |
|-----------|-----------|
| **Backend** | Laravel 11 |
| **Database** | MySQL |
| **ORM** | Eloquent |
| **Frontend** | Blade Templates |
| **Styling** | Tailwind CSS (CDN) |
| **Icons** | Font Awesome 6.4 |
| **Authentication** | Session-based |

## 🔐 Role-Based Access Control

Three distinct user roles with automatic middleware enforcement:

```
CUSTOMER          SELLER            ADMIN
─────────         ──────            ─────
Browse products   Manage products   Manage all users
Add to cart       View orders       Configure fees
Checkout          Confirm orders    Create banners
Track orders      Update status     Monitor wallet
Rate products     View earnings     Manage categories
```

## 🤖 AI Security & Risk Engine

This project includes an **AI-assisted security layer** that evaluates risky actions (login, checkout, password change) and enforces multi-factor authentication (MFA) when necessary.

- **RiskAssessmentService**
  - Builds an input payload: user ID, device fingerprint (session-based), IP history, geo-location, transaction amount, and user role.
  - Sends the payload to an external risk-scoring API (AI Core).
  - If the API fails, falls back to a local heuristic:
    - Adds risk for large transactions, new/unverified devices, unusual amounts.
    - Reduces risk for known devices and spending patterns close to historical averages.
  - Maps the final score (0–100) to:
    - `allow` (no MFA), `otp` (email OTP), `faceid` (FaceID verification), or `block`.
  - Stores a full explanation payload in `security_audits.metadata.risk_explanation` which powers the **Recent Audit Decisions** view in the admin panel.

- **MFA & OTP Flow**
  - When `suggestion=otp` or `faceid`, the checkout/login flow:
    - Generates a 6-digit OTP and stores it in session (`expected_otp`).
    - Redirects to `/otp/verify` with an AI warning banner.
  - On the OTP screen:
    - If FaceID is not used, OTP is required.
    - If FaceID passes, OTP becomes optional depending on risk.
  - Successful OTP/FaceID:
    - Marks session as `mfa_verified` and `device_verified`, so future actions on the same device may skip MFA if risk is low.

- **FaceID Digital Identity**
  - Frontend captures a webcam snapshot as Base64 JPEG.
  - Backend (`FaceVerificationService`) writes an identity image and calls `scripts/face_verify.py`.
  - Python performs:
    - Face detection, alignment, light normalization (GrayWorld/CLAHE).
    - Builds grid descriptors and generates multiple templates (normal, rotated, dark, noisy).
    - Persists cache descriptors in `storage/app/face_verify_cache/user_{id}.json`.
  - During verification:
    - Live capture is converted to a descriptor.
    - Compared against all cached templates using weighted grid similarity.
    - Returns JSON with `match`, `confidence`, lighting info, and detailed score breakdown.

- **Admin Risk Audits**
  - Every intercepted action writes a `SecurityAudit` record including:
    - risk score, level, suggestion, final decision.
    - engine input and score breakdown (API or local heuristic).
  - Admin UI shows a human-readable breakdown so you can see exactly *why* AI requested OTP/FaceID or blocked an action.

## 💳 Payment Methods

- **Cash on Delivery (COD)**: Ready to use in Vietnamese Dong (VND)
- **VNPay**: Framework in place (requires merchant account, uses VND)

## 📊 Project Statistics

```
Total Lines of Code:     5,000+
Database Tables:         26
Models:                  16
Controllers:             18
Routes:                  40+
Views:                   57
Migrations:              5
Development Status:      ✅ Complete & Ready for Production
```

## 🧪 Testing the Platform

1. **Register Test Accounts**
   - Go to `/register`
   - Create account as Customer, Seller
   - Admin: `admin@gmail.com` / `admin123`

2. **Seller Setup**
   - Login as Seller at `/seller/dashboard`
   - Create product with `/seller/products/create`
   - Set pricing and discounts

3. **Customer Shopping**
   - Browse products on home page
   - Add to cart
   - Complete checkout with COD
   - Track order status

4. **Admin Monitoring**
   - Login as Admin at `/admin/dashboard`
   - Manage customers, sellers, categories
   - Monitor platform wallet

## 🚀 Deployment

For production:
- Update `.env` with production database credentials
- Set `APP_ENV=production` and `APP_DEBUG=false`
- Generate new `APP_KEY` on production server
- Set up HTTPS/SSL certificate
- Configure proper file permissions
- Set up automated backups
- Configure email/SMTP for notifications

## 📞 Support Resources

- **Installation Guide**: [INSTALLATION.md](INSTALLATION.md)
- **Laravel Documentation**: https://laravel.com/docs
- **Tailwind CSS**: https://tailwindcss.com/docs
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **Eloquent ORM**: https://laravel.com/docs/11.x/eloquent

## 🐛 Troubleshooting

**Database Connection Failed**
```bash
# Verify MySQL is running in Laragon
# Check DB-ecommerce exists in phpMyAdmin
# Verify .env database credentials
php artisan migrate
```

**Storage/Upload Issues**
```bash
php artisan storage:link
chmod -R 755 storage/
```

**Routes Not Working**
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

## 📄 License

This project is built for educational and commercial purposes.

---

**Created**: January 2026
**Laravel Version**: 11.0
**PHP Version**: 8.2+
**Status**: ✅ Production Ready
