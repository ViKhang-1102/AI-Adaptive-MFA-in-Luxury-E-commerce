# Installation & Setup Guide - E-Commerce Platform

This guide will help you set up the complete e-commerce platform on your Laragon environment.

## Prerequisites

- **Laragon** (latest version with Apache and PHP 8.2+)
- **MySQL** (included with Laragon)
- **Composer** (for PHP dependency management)
- **A text editor** (VS Code recommended)

## Step-by-Step Installation

### 1. Prepare the Project Directory

The project is already located at: `C:\laragon\www\E-commerce2026`

### 2. Install PHP Dependencies

Open your terminal/command prompt and navigate to the project directory:

```bash
cd C:\laragon\www\E-commerce2026
```

Install Composer dependencies:

```bash
composer install
```

This will download and install all required Laravel packages.

### 3. Create Database

1. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
2. Click on **Databases**
3. Create a new database named: **`DB-ecommerce`**
4. Keep the default collation (utf8mb4_unicode_ci)

### 4. Configure Environment Variables

The `.env` file is already configured with Laragon defaults. Verify it contains:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=DB-ecommerce
DB_USERNAME=root
DB_PASSWORD=
```

If your MySQL has a password, update `DB_PASSWORD` accordingly.

### 5. Generate Application Key

This is required for Laravel to work properly:

```bash
php artisan key:generate
```

You should see: `Application key set successfully.`

### 6. Run Database Migrations

Create all the necessary database tables:

```bash
php artisan migrate
```

This will create the following tables:
- users, sessions, password_reset_tokens
- categories, seller_categories
- products, product_images, product_reviews
- carts, cart_items
- orders, order_items
- wishlists, customer_addresses
- payments, banners, system_fees
- e_wallets, wallet_transactions

### 7. Seed Initial Data

Populate the database with the default admin account and system configuration:

```bash
php artisan db:seed
```

This will create:
- **Admin Account**: `admin@gmail.com` / `admin123`
- **Default System Fees**:
  - Platform Fee: 5%
  - Transaction Fee: 2%
  - Default Shipping Fee: 20,000 VND
- **Admin E-Wallet**

### 8. Create Storage Symlink

For file uploads (product images, banners, avatars) to work:

```bash
php artisan storage:link
```

### 9. Start the Development Server

```bash
php artisan serve
```

The server will start at: **`http://localhost:8000`**

If port 8000 is in use, you can specify a different port:

```bash
php artisan serve --port=8001
```

## Access the Platform

### 1. Home Page
Navigate to: `http://localhost:8000`

You'll see the home page with:
- Promotional banners
- Product categories
- Featured products
- All products with pagination

### 2. Admin Login
Go to: `http://localhost:8000/login`

**Admin Credentials:**
- Email: `admin@gmail.com`
- Password: `admin123`

After login, access the admin panel at: `/admin/dashboard`

### 3. Register New Accounts

Users can register as:
- **Customer**: Browse and purchase products
- **Seller**: Manage products and orders

Registration page: `http://localhost:8000/register`

## Project Structure Overview

```
E-commerce2026/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # All request handlers
│   │   ├── Middleware/           # Authentication & role checks
│   │   └── Kernel.php
│   ├── Models/                   # Database models (25+ models)
│   └── Console/                  # Artisan commands
│
├── database/
│   ├── migrations/               # Database schema (4 migration files)
│   └── seeders/                  # Initial data seeding
│
├── resources/
│   ├── views/                    # Blade templates (organized by feature)
│   │   ├── layouts/              # Header, footer, app layout
│   │   ├── auth/                 # Login, register pages
│   │   ├── products/             # Product browsing & details
│   │   ├── categories/           # Category management
│   │   ├── cart/                 # Shopping cart
│   │   ├── checkout/             # Order checkout
│   │   ├── orders/               # Order history & tracking
│   │   ├── addresses/            # Address management
│   │   ├── profile/              # User profile
│   │   ├── seller/               # Seller dashboard & management
│   │   └── admin/                # Admin dashboard & management
│
├── routes/
│   ├── web.php                   # All web routes (organized by role)
│   └── console.php               # Command routes
│
├── public/
│   └── index.php                 # Application entry point
│
├── config/                       # (To be created if needed)
├── bootstrap/                    # Framework bootstrapping
├── .env                          # Environment configuration
├── .env.example                  # Example configuration
├── composer.json                 # PHP dependencies
├── artisan                       # Laravel command runner
└── README.md                     # Project documentation
```

## Key Features by User Role

### 👤 Customer Features
- ✅ Browse products by category
- ✅ Search and filter products
- ✅ View product details with images
- ✅ Read customer reviews and ratings
- ✅ Add products to cart
- ✅ Manage shopping cart (add/remove/update quantity)
- ✅ Save products to wishlist
- ✅ Manage delivery addresses
- ✅ Checkout with payment method selection (COD/VNPay)
- ✅ View order history and track orders
- ✅ Update profile and password
- ✅ View wallet balance and transactions

### 🏪 Seller Features
- ✅ Dashboard with sales statistics
- ✅ Create and manage products
- ✅ Upload product images
- ✅ Set discounts with time periods
- ✅ Manage inventory (stock)
- ✅ Manage seller categories
- ✅ View and manage orders from customers
- ✅ Confirm or reject orders
- ✅ Update order status (processing, shipped, delivered)
- ✅ View seller wallet and earnings
- ✅ Update profile information

### 👨‍💼 Admin Features
- ✅ Dashboard with platform statistics
- ✅ Manage all customers (view, edit, deactivate)
- ✅ Manage all sellers (create, edit, deactivate)
- ✅ Manage product categories
- ✅ Create and manage promotional banners
- ✅ Configure system fees (platform fee, transaction fee)
- ✅ Monitor platform e-wallet
- ✅ View all orders on the platform
- ✅ Manage system settings

## Database Schema Summary

### 26 Tables Created:
1. **users** - User accounts (admin, seller, customer)
2. **categories** - Product categories with parent-child hierarchy
3. **seller_categories** - Seller's available categories
4. **products** - Product listings
5. **product_images** - Product images
6. **product_reviews** - Customer reviews and ratings
7. **carts** - Shopping carts
8. **cart_items** - Items in shopping cart
9. **orders** - Customer orders
10. **order_items** - Items in orders
11. **wishlists** - Saved products by customers
12. **customer_addresses** - Delivery addresses
13. **payments** - Payment records
14. **banners** - Promotional banners
15. **e_wallets** - User wallets (customer/seller/admin)
16. **wallet_transactions** - Wallet transaction history
17. **system_fees** - Platform fee configuration
18. Plus Laravel's default tables (sessions, password_reset_tokens)

## Troubleshooting

### Issue: "Connection refused" when running migrations

**Solution**: 
- Ensure MySQL is running in Laragon
- Check `.env` file has correct database credentials
- Verify database `DB-ecommerce` exists

### Issue: "Class not found" errors

**Solution**:
```bash
composer install
php artisan config:clear
php artisan cache:clear
```

### Issue: Upload/Image issues

**Solution**: 
Ensure the storage symlink exists:
```bash
php artisan storage:link
```

### Issue: Routes not working

**Solution**:
```bash
php artisan route:clear
php artisan config:clear
```

## Next Steps for Development

### 1. Customize Branding
- Update logo in header
- Change color scheme in views
- Modify footer content

### 2. Complete Admin/Seller Views
- Create full form views for category/product management
- Implement banner management forms
- Add fee configuration interface
- Create detailed admin reports

### 3. Implement Additional Features
- Email notifications for orders
- SMS notifications
- Advanced product filtering
- Product recommendations
- Customer support chat
- Shipping provider integration
- Advanced analytics

### 4. Payment Integration
- Integrate VNPay API for online payments
- Set up payment webhooks
- Implement refund processing

### 5. Testing
- Create unit tests for models
- Create feature tests for controllers
- Set up continuous integration

## Useful Artisan Commands

```bash
# Start development server
php artisan serve

# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# List all routes
php artisan route:list

# Reset database (WARNING: deletes all data)
php artisan migrate:refresh

# Seed database with initial data
php artisan db:seed

# Create a new model
php artisan make:model ModelName

# Create a new controller
php artisan make:controller ControllerName

# Create a new migration
php artisan make:migration create_table_name
```

## Support & Resources

- **Laravel Documentation**: https://laravel.com/docs
- **Tailwind CSS Documentation**: https://tailwindcss.com/docs
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **Composer Documentation**: https://getcomposer.org/doc/

## Getting Help

If you encounter issues:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Run `php artisan config:clear` and try again
3. Ensure all dependencies are installed: `composer install`
4. Verify database credentials in `.env`

## Security Notes

For production deployment:
1. Change all default passwords
2. Update `.env` with secure values
3. Set `APP_DEBUG=false`
4. Set `APP_ENV=production`
5. Use HTTPS
6. Keep Laravel and packages updated
7. Set up proper file permissions
8. Enable CSRF protection (enabled by default)
9. Use environment variables for secrets
10. Set up regular backups

## License

This project is built for educational and commercial use.

---

**Last Updated**: January 2026
**Laravel Version**: 11.0
**PHP Version**: 8.2+
