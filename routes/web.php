<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Protected Routes for All Authenticated Users
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Cart Routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{item}', [CartController::class, 'remove'])->name('cart.remove');

    // Order Routes
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// Customer Routes
Route::middleware(['auth', \App\Http\Middleware\CustomerMiddleware::class])->group(function () {
    // PayPal initiation - customer triggers payment
    Route::get('paypal/create/{order}', [\App\Http\Controllers\PayPalController::class, 'createPayment'])
        ->name('paypal.create');

    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::post('/orders/{order}/payment', [OrderController::class, 'payment'])->name('orders.payment');
    Route::post('/orders/{order}/buy-again', [OrderController::class, 'buyAgain'])->name('orders.buyAgain');

    // Wishlist Routes
    Route::get('/wishlist', [ProductController::class, 'wishlist'])->name('wishlist');
    Route::post('/wishlist/add/{product}', [ProductController::class, 'addWishlist'])->name('wishlist.add');
    Route::delete('/wishlist/remove/{product}', [ProductController::class, 'removeWishlist'])->name('wishlist.remove');

    // Addresses Routes
    Route::get('/addresses', [ProfileController::class, 'addresses'])->name('addresses.index');
    Route::post('/addresses', [ProfileController::class, 'storeAddress'])->name('addresses.store');
    Route::put('/addresses/{address}', [ProfileController::class, 'updateAddress'])->name('addresses.update');
    Route::delete('/addresses/{address}', [ProfileController::class, 'destroyAddress'])->name('addresses.destroy');

    // Review Routes
    Route::post('/products/{product}/reviews', [App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [App\Http\Controllers\ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Message Routes
    Route::get('/messages', [App\Http\Controllers\MessageController::class, 'customerInbox'])->name('customer.messages.index');
    Route::get('/messages/{product}/{other}', [App\Http\Controllers\MessageController::class, 'customerConversation'])->name('customer.messages.conversation');
    Route::get('/products/{product}/messages', [App\Http\Controllers\MessageController::class, 'getMessages'])->name('messages.get');
    Route::post('/products/{product}/messages', [App\Http\Controllers\MessageController::class, 'sendMessage'])->name('messages.send');
    Route::post('/messages/{message}/read', [App\Http\Controllers\MessageController::class, 'markAsRead'])->name('messages.read');
    Route::get('/messages/unread/count', [App\Http\Controllers\MessageController::class, 'getUnreadCount'])->name('messages.unread-count');
});

// Seller Routes
Route::middleware(['auth', \App\Http\Middleware\SellerMiddleware::class])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Seller\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('/products', App\Http\Controllers\Seller\ProductController::class);
    Route::resource('/categories', App\Http\Controllers\Seller\CategoryController::class);
    Route::get('/orders', [App\Http\Controllers\Seller\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [App\Http\Controllers\Seller\OrderController::class, 'show'])->name('orders.show');
    
    // Seller message inbox & conversation
    Route::get('/messages', [App\Http\Controllers\MessageController::class, 'sellerInbox'])->name('messages.index');
    Route::get('/messages/api/customers', [App\Http\Controllers\MessageController::class, 'getCustomersList'])->name('messages.api.customers');
    Route::get('/messages/api/customers/{customerId}/products', [App\Http\Controllers\MessageController::class, 'getCustomerProducts'])->name('messages.api.customer-products');
    Route::get('/messages/{product}/{other}', [App\Http\Controllers\MessageController::class, 'sellerConversation'])->name('messages.conversation');
    Route::post('/orders/{order}/confirm', [App\Http\Controllers\Seller\OrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('/orders/{order}/cancel', [App\Http\Controllers\Seller\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::delete('/orders/{order}', [App\Http\Controllers\Seller\OrderController::class, 'destroy'])->name('orders.destroy');
    Route::post('/orders/{order}/ship', [App\Http\Controllers\Seller\OrderController::class, 'ship'])->name('orders.ship');
    Route::post('/orders/{order}/deliver', [App\Http\Controllers\Seller\OrderController::class, 'deliver'])->name('orders.deliver');
    Route::delete('/products/image/{productImage}', [App\Http\Controllers\Seller\ProductController::class, 'deleteImage'])->name('products.deleteImage');
    Route::get('/wallet', [App\Http\Controllers\Seller\WalletController::class, 'index'])->name('wallet');
});

// Admin Routes
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('/customers', App\Http\Controllers\Admin\CustomerController::class, ['except' => ['show']]);
    Route::resource('/sellers', App\Http\Controllers\Admin\SellerController::class, ['except' => ['show']]);
    Route::resource('/categories', App\Http\Controllers\Admin\CategoryController::class, ['except' => ['show']]);
    Route::resource('/banners', App\Http\Controllers\Admin\BannerController::class, ['except' => ['show']]);
    Route::resource('/fees', App\Http\Controllers\Admin\FeeController::class);
    Route::post('/fees/commission/update', [App\Http\Controllers\Admin\FeeController::class, 'updatePlatformCommission'])->name('fees.commission.update');
    Route::get('/wallet', [App\Http\Controllers\Admin\WalletController::class, 'index'])->name('wallet');
    Route::post('/transactions/{transaction}/approve', [App\Http\Controllers\Admin\TransactionController::class, 'approve'])->name('transaction.approve');
    Route::post('/transactions/{transaction}/reject', [App\Http\Controllers\Admin\TransactionController::class, 'reject'])->name('transaction.reject');
    Route::get('/orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
});

// PayPal marketplace callbacks (accessible to anyone after redirect)
Route::get('paypal/success', [App\Http\Controllers\PayPalController::class, 'paymentSuccess'])->name('paypal.success');
Route::get('paypal/cancel', [App\Http\Controllers\PayPalController::class, 'paymentCancel'])->name('paypal.cancel');
