# Quick Start Guide - E-Commerce Platform

Get the platform running in 5 minutes!

## Prerequisites ✓

- **Laragon** (installed and running)
- **Composer** (for PHP dependency management)
- **VS Code** or your favorite text editor

## Setup Steps

### Step 1: Install Dependencies (2 minutes)

Open Command Prompt or PowerShell and run:

```bash
cd C:\laragon\www\E-commerce2026
composer install
```

Wait for all packages to download and install.

### Step 2: Generate Application Key (30 seconds)

```bash
php artisan key:generate
```

You should see: `Application key set successfully.`

### Step 3: Create Database (1 minute)

1. Open **phpMyAdmin** (usually at `http://localhost/phpmyadmin`)
2. Click **Databases** in the left sidebar
3. In the **Create database** section:
   - Enter name: `DB-ecommerce`
   - Leave collation as default
   - Click **Create**

### Step 4: Run Migrations (1 minute)

```bash
php artisan migrate
```

This creates all database tables automatically.

### Step 5: Seed Initial Data (30 seconds)

```bash
php artisan db:seed
```

This creates:
- Admin account: `admin@gmail.com` / `admin123`
- Default system fees
- Admin wallet

### Step 6: Create Storage Link (30 seconds)

```bash
php artisan storage:link
```

This enables image uploads for products and banners.

### Step 7: Start the Server (instant!)

```bash
php artisan serve
```

You'll see:
```
Laravel development server started: http://127.0.0.1:8000
```

## 🎉 You're Done!

Open your browser and visit: **`http://localhost:8000`**

## Login Credentials

### Admin Account (Full Access)
- **Email**: `admin@gmail.com`
- **Password**: `admin123`

### Test Customer Account
- Go to `/register` and create a new customer account
- Set password and login

### Test Seller Account
- Go to `/register` and create a new seller account
- Select "Seller" during registration

## What to Do Next

### As Admin
1. Login with admin account
2. Go to `/admin/dashboard`
3. Explore categories, banners, and fee configuration

### As Seller
1. Register as Seller at `/register`
2. Go to `/seller/dashboard`
3. Create a product at `/seller/products/create`
4. Set price and discount

### As Customer
1. Register as Customer at `/register`
2. Browse products on home page
3. Add product to cart
4. Checkout with COD payment
5. View order status in `/orders`

## Useful Commands

```bash
# View all routes
php artisan route:list

# Clear cache if something is stuck
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Reset database (WARNING: deletes all data!)
php artisan migrate:refresh --seed

# View Laravel logs
tail -f storage/logs/laravel.log
```

## Common Issues & Solutions

### "Connection refused" error when running migrations

**Solution**: Make sure MySQL is running in Laragon
1. Open Laragon control panel
2. Click "Start All"
3. Try migration again: `php artisan migrate`

### "SQLSTATE[HY000]: General error: 1030" error

**Solution**: Database doesn't exist
1. Open phpMyAdmin
2. Create database: `DB-ecommerce`
3. Run migration again

### "Class not found" errors

**Solution**: Run these commands
```bash
composer install
php artisan config:clear
php artisan cache:clear
php artisan serve
```

### Images/uploads not working

**Solution**: Create storage symlink
```bash
php artisan storage:link
```

### Port 8000 already in use

**Solution**: Use a different port
```bash
php artisan serve --port=8001
```

Then visit: `http://localhost:8001`

## Project Structure Overview

```
C:\laragon\www\E-commerce2026\
├── app/
│   ├── Http/Controllers/     # Application logic
│   ├── Models/               # Database models
│   └── Http/Middleware/      # Security & role checking
│
├── database/
│   ├── migrations/           # Database schema
│   └── seeders/              # Initial data
│
├── resources/views/          # HTML templates
│   ├── auth/                 # Login & register
│   ├── products/             # Product pages
│   ├── checkout/             # Shopping cart & checkout
│   ├── seller/               # Seller dashboard
│   └── admin/                # Admin dashboard
│
├── routes/web.php            # URL routes
├── .env                      # Configuration
└── public/index.php          # Entry point
```

## Key Features to Try

### 👤 Customer Features
- [x] User registration & login
- [x] Browse products by category
- [x] Search products
- [x] Add to shopping cart
- [x] Checkout with address selection
- [x] Place order (COD payment)
- [x] View order history
- [x] Manage wishlist
- [x] Update profile

### 🏪 Seller Features
- [x] Seller registration & login
- [x] Create/edit products
- [x] Set product pricing
- [x] Apply time-based discounts
- [x] View customer orders
- [x] Confirm/cancel orders
- [x] View earnings in wallet

### 👨‍💼 Admin Features
- [x] Login with admin account
- [x] View dashboard statistics
- [x] Manage categories
- [x] Create promotional banners
- [x] Configure platform fees
- [x] Monitor wallet

## Troubleshooting Checklist

- [ ] MySQL is running (Laragon "Start All" button)
- [ ] Database `DB-ecommerce` created in phpMyAdmin
- [ ] `composer install` completed without errors
- [ ] `php artisan key:generate` ran successfully
- [ ] `php artisan migrate` completed without errors
- [ ] `php artisan db:seed` created admin account
- [ ] `php artisan storage:link` created storage symlink
- [ ] Server running with `php artisan serve`
- [ ] Browser can access `http://localhost:8000`

## Getting Help

1. **Check Laravel Logs**: `storage/logs/laravel.log`
2. **Run Clear Commands**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```
3. **Restart Server**: Stop with `Ctrl+C`, then `php artisan serve` again

## Next Steps for Development

### Customize the Platform
- Change logo and colors
- Update email templates
- Customize dashboard layouts

### Add Features
- Email notifications for orders
- SMS notifications
- Advanced product search
- Customer reviews moderation
- Product recommendations

### Integration
- VNPay payment gateway
- Shipping provider APIs
- Email service (SMTP)
- Analytics

### Security
- Set up HTTPS/SSL
- Configure rate limiting
- Enable CSRF protection (already enabled)
- Set up backups

## Production Deployment

When ready to go live:

1. Update `.env` with production database
2. Set `APP_ENV=production`
3. Set `APP_DEBUG=false`
4. Generate new `APP_KEY`
5. Set up HTTPS
6. Configure backups
7. Set up monitoring
8. Deploy with proper web server (Apache/Nginx)

---

## Quick Reference

| Command | Purpose |
|---------|---------|
| `php artisan serve` | Start development server |
| `php artisan migrate` | Create database tables |
| `php artisan db:seed` | Insert initial data |
| `php artisan cache:clear` | Clear application cache |
| `php artisan route:list` | List all routes |

---

**Ready to go!** 🚀 Visit `http://localhost:8000` and explore the platform.

For detailed documentation, see [INSTALLATION.md](INSTALLATION.md) or [README.md](README.md)
