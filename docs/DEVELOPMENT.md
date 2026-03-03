# Development Guide

Guide for developers working on the E-Commerce Platform.

## 🏗️ Project Architecture

### MVC Pattern
```
Model  → Database layer (Eloquent ORM)
View   → Blade templates (resources/views/)
Control→ HTTP controllers (app/Http/Controllers/)
```

### Directory Structure
```
app/
├── Http/
│   ├── Controllers/          # Request handlers
│   │   ├── AuthController.php
│   │   ├── HomeController.php
│   │   ├── ProductController.php
│   │   ├── CartController.php
│   │   ├── OrderController.php
│   │   ├── Admin/            # Admin controllers
│   │   └── Seller/           # Seller controllers
│   ├── Middleware/           # Custom middleware
│   ├── Kernel.php            # Middleware registration
│   └── Exceptions/
│
├── Models/                   # Eloquent models (16 total)
│   ├── User.php
│   ├── Product.php
│   ├── Order.php
│   ├── Cart.php
│   ├── Category.php
│   └── ... (11 more models)
│
├── Console/                  # Artisan commands
└── Exceptions/              # Exception handling

database/
├── migrations/              # Schema definitions (5 files)
└── seeders/                # Initial data

resources/
├── views/                  # Blade templates (57 files)
│   ├── layouts/
│   ├── auth/
│   ├── products/
│   ├── checkout/
│   ├── orders/
│   ├── seller/
│   └── admin/
└── css/                    # Stylesheets (if any)

routes/
└── web.php                # Route definitions (40+ routes)
```

## 🗄️ Database Schema

### Entity Relationship Overview
```
Users
├── Products (1:many) - seller_id
├── Orders (1:many) - customer_id or seller_id
├── Cart (1:1)
├── EWallet (1:1)
├── CustomerAddresses (1:many)
├── ProductReviews (1:many)
└── WalletTransactions (1:many)

Products
├── Category (many:1)
├── ProductImages (1:many)
├── ProductReviews (1:many)
├── CartItems (1:many)
└── OrderItems (1:many)

Orders
├── OrderItems (1:many)
├── Payments (1:1)
└── Customer/Seller (many:1 to Users)

Cart
├── CartItems (1:many)
└── Customer (1:1 to User)
```

### Key Tables & Relationships

#### Users Table
```php
- id (PK)
- name, email, password
- role (admin, seller, customer)
- phone, avatar
- last_login, created_at, updated_at
```

#### Products Table
```php
- id (PK)
- seller_id (FK → users)
- category_id (FK → categories)
- name, description
- price, stock
- discount_percent, discount_start, discount_end
- is_active
```

#### Orders Table
```php
- id (PK)
- order_number (unique, generated)
- customer_id (FK → users)
- seller_id (FK → users)
- status (pending, confirmed, processing, shipped, delivered)
- total_amount
- created_at
```

## 🎯 Key Models & Methods

### User Model
```php
class User extends Model {
    // Relationships
    public function products()              // Seller's products
    public function orders()                // Customer's orders
    public function cart()                  // Shopping cart
    public function addresses()             // Delivery addresses
    public function wallet()                // E-wallet
    public function reviews()               // Product reviews
    
    // Scopes
    public function scopeAdmins()
    public function scopeSellers()
    public function scopeCustomers()
    
    // Methods
    public function isAdmin()               // Check role
    public function isSeller()
    public function isCustomer()
    public function hasRole($role)
}
```

### Product Model
```php
class Product extends Model {
    // Relationships
    public function seller()                // FK to seller
    public function category()              // FK to category
    public function images()                // Related images
    public function reviews()               // Customer reviews
    public function cartItems()             // In carts
    public function orderItems()            // In orders
    
    // Scopes
    public function scopeOnDiscount()       // Currently on sale
    public function scopeInStock()          // Has stock
    public function scopeActive()           // Is active
    
    // Methods
    public function hasDiscount()           // Check if discounted
    public function getDiscountedPrice()    // Calculate discounted price
    public function getAverageRating()      // Average review rating
}
```

### Order Model
```php
class Order extends Model {
    // Relationships
    public function customer()              // FK to customer
    public function seller()                // FK to seller
    public function items()                 // Order items
    public function payment()               // Payment record
    
    // Scopes
    public function scopePending()          // Filter pending
    public function scopeConfirmed()        // Filter confirmed
    
    // Methods
    public function canBeCancelled()        // Check if can cancel
    public function generateOrderNumber()   // Generate unique order number
    public function calculateTotal()        // Calculate order total
}
```

## 🔌 Key Controllers & Actions

### ProductController
```php
public function index()              // List products with filters
public function show($product)       // Product detail page
public function search($query)       // Search products
public function wishlist($product)   // Add/remove wishlist
```

### CartController
```php
public function index()              // View cart
public function add($product)        // Add to cart
public function update($item)        // Update quantity
public function remove($item)        // Remove item
```

### OrderController
```php
public function checkout()           // Checkout page
public function store()              // Place order
public function show($order)         // Order details
public function cancel($order)       // Cancel order
```

### Admin/DashboardController
```php
public function index()              // Admin dashboard
                                    // Statistics, charts, quick actions
```

### Seller/ProductController
```php
public function index()              // List seller's products
public function create()             // Create form
public function store()              // Save product
public function edit($product)       // Edit form
public function update($product)     // Update product
public function destroy($product)    // Delete product
```

## 🛣️ Routing Pattern

### Route Organization
```php
// Public routes (no auth required)
Route::get('/', [HomeController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Customer routes (auth + role check)
Route::middleware('auth', 'role:customer')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
});

// Seller routes
Route::middleware('auth', 'role:seller')->prefix('seller')->group(function () {
    Route::resource('products', 'ProductController');
    Route::resource('orders', 'OrderController');
});

// Admin routes
Route::middleware('auth', 'role:admin')->prefix('admin')->group(function () {
    Route::resource('customers', 'CustomerController');
    Route::resource('sellers', 'SellerController');
});
```

## 🔐 Middleware Implementation

### Role Checking Middleware
```php
class CheckRole {
    public function handle($request, Closure $next, $role) {
        if (auth()->user()->role !== $role) {
            return redirect('/')->with('error', 'Unauthorized');
        }
        return $next($request);
    }
}
```

### Register in Kernel.php
```php
protected $routeMiddleware = [
    'auth' => \Middleware\Authenticate::class,
    'role' => \Middleware\CheckRole::class,
];
```

## 📝 Blade Templates

### Template Structure
```blade
@extends('layouts.app')

@section('title', 'Page Title')

@section('content')
<div class="container">
    <h1>{{ $variable }}</h1>
    
    @if ($condition)
        <!-- Content -->
    @endif
    
    @foreach ($items as $item)
        {{ $item->name }}
    @endforeach
</div>
@endsection
```

### Common Components
```blade
<!-- Include header -->
@include('layouts.header')

<!-- Include footer -->
@include('layouts.footer')

<!-- Flash messages -->
@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
```

## 🗄️ Working with Eloquent ORM

### Querying Data
```php
// Get all products
$products = Product::all();

// Get products with filters
$products = Product::where('category_id', 5)
                   ->where('price', '<', 1000000)
                   ->get();

// Get single product
$product = Product::find($id);
$product = Product::where('slug', $slug)->first();

// Get with relationships (eager loading)
$products = Product::with('seller', 'category', 'images')->get();

// Paginate results
$products = Product::paginate(12);
```

### Creating & Updating
```php
// Create new
$product = Product::create([
    'name' => 'Product Name',
    'price' => 100000,
    'seller_id' => auth()->id(),
]);

// Update
$product->update([
    'price' => 120000,
    'stock' => 50,
]);

// Delete
$product->delete();
```

### Relationships
```php
// Access relationships
$product->seller;          // Get seller user
$product->category;        // Get category
$product->images;          // Get all images
$product->reviews;         // Get reviews

// Add related items
$product->images()->create(['url' => $url]);
$product->reviews()->create(['rating' => 5, ...]);

// Count related items
$product->images()->count();
```

## ✅ Validation

### Validation Rules
```php
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users',
    'password' => 'required|min:8|confirmed',
    'price' => 'required|numeric|min:0',
    'stock' => 'required|integer|min:0',
    'description' => 'required|string',
]);
```

### Custom Validation Messages
```php
$request->validate([
    'email' => 'required|email',
], [
    'email.required' => 'Email address is required.',
    'email.email' => 'Please enter a valid email.',
]);
```

## 📦 Common Tasks

### Add New Feature
1. Create database table (migration)
2. Create model with relationships
3. Create controller with actions
4. Define routes in routes/web.php
5. Create Blade templates
6. Test functionality

### Create New Controller
```bash
php artisan make:controller ProductController
```

### Create New Model with Migration
```bash
php artisan make:model Product -m
```

### Create New Migration
```bash
php artisan make:migration create_products_table
```

## 🐛 Debugging

### Enable Debug Mode
Update `.env`:
```
APP_DEBUG=true
```

### View Errors
Check: `storage/logs/laravel.log`

### Use dd() Function
```php
dd($variable);  // Dump and die - prints variable and stops execution
dd($product->toArray());  // Convert model to array for inspection
```

### Use Log
```php
use Illuminate\Support\Facades\Log;

Log::debug('Debug message', ['variable' => $value]);
Log::error('Error message');
```

## 🧪 Testing (Ready for Implementation)

### Unit Test Example
```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Product;

class ProductTest extends TestCase {
    public function test_product_has_discount() {
        $product = Product::find(1);
        $this->assertTrue($product->hasDiscount());
    }
}
```

### Feature Test Example
```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase {
    use RefreshDatabase;
    
    public function test_user_can_add_to_cart() {
        $response = $this->post('/cart/add', [
            'product_id' => 1,
            'quantity' => 1,
        ]);
        
        $this->assertDatabaseHas('cart_items', [
            'product_id' => 1,
        ]);
    }
}
```

## 🔄 Common Workflows

### Adding a New Product Field
1. Create migration: `php artisan make:migration add_field_to_products_table`
2. Update migration file with schema changes
3. Run migration: `php artisan migrate`
4. Update Product model (add to fillable array)
5. Update product forms (create/edit views)
6. Update controller validation

### Implementing New Feature
Example: Add product reviews
1. Create migration for reviews table
2. Create Review model with relationships
3. Create ReviewController with store/show methods
4. Add routes: `Route::resource('reviews', ReviewController::class)`
5. Create review form in product detail view
6. Display reviews in product detail page

### Database Rollback
```bash
# Rollback last migration
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Refresh database (reset + migrate)
php artisan migrate:refresh

# Refresh and seed
php artisan migrate:refresh --seed
```

## 📊 Performance Tips

### Eager Loading
```php
// Bad: N+1 query problem
$products = Product::all();
foreach ($products as $product) {
    echo $product->seller->name;  // Query for each product
}

// Good: Use eager loading
$products = Product::with('seller')->get();
foreach ($products as $product) {
    echo $product->seller->name;  // No additional queries
}
```

### Caching
```php
// Cache query results
$products = Cache::remember('products.all', 3600, function () {
    return Product::all();
});

// Clear cache when data changes
Cache::forget('products.all');
```

### Pagination
```php
// Don't use all() then filter
$products = Product::all();
$filtered = $products->where('category_id', 5);  // In memory

// Use database filtering
$products = Product::where('category_id', 5)->paginate(12);
```

## 🚀 Deployment Considerations

### Environment Configuration
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
DB_PASSWORD=secure_password
MAIL_DRIVER=smtp
MAIL_FROM_ADDRESS=noreply@ecommerce.com
```

### Performance Optimization
```bash
# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### Security Checklist
- [ ] Update all dependencies: `composer update`
- [ ] Set secure file permissions
- [ ] Enable HTTPS/SSL
- [ ] Configure CSRF protection (enabled by default)
- [ ] Set up environment variables securely
- [ ] Enable query caching
- [ ] Set up database backups
- [ ] Configure error logging
- [ ] Hide Laravel debug information

## 📚 Resources

- **Laravel Documentation**: https://laravel.com/docs
- **Eloquent ORM**: https://laravel.com/docs/11.x/eloquent
- **Blade Templating**: https://laravel.com/docs/11.x/blade
- **Routing**: https://laravel.com/docs/11.x/routing
- **Database Migrations**: https://laravel.com/docs/11.x/migrations

## 📝 Code Style Guidelines

### PSR-12 Coding Standards
```php
// Class names: PascalCase
class UserController {}

// Method names: camelCase
public function getUserProducts() {}

// Variable names: camelCase
$userName = 'John';

// Constants: UPPER_SNAKE_CASE
const MAX_ATTEMPTS = 5;

// Indent with 4 spaces
public function example() {
    if ($condition) {
        // Code
    }
}
```

## 🔗 Important Files Reference

| File | Purpose |
|------|---------|
| `.env` | Environment variables |
| `routes/web.php` | URL route definitions |
| `app/Http/Kernel.php` | Middleware registration |
| `app/Models/User.php` | User model |
| `database/migrations/` | Database schema |
| `resources/views/` | Blade templates |
| `public/index.php` | Application entry point |

---

**Development Guide Last Updated**: January 2026
**Laravel Version**: 11.0
**Status**: Production Ready for Development
