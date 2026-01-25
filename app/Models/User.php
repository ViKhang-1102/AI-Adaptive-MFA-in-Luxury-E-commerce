<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role',
        'name',
        'email',
        'password',
        'phone',
        'address',
        'avatar',
        'bio',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function ordersAsCustomer()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function ordersAsSeller()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function cart()
    {
        return $this->hasOne(Cart::class, 'customer_id');
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class, 'customer_id');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'customer_id');
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }

    public function wallet()
    {
        return $this->hasOne(EWallet::class);
    }

    public function sellerCategories()
    {
        return $this->hasMany(SellerCategory::class, 'seller_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeSellers($query)
    {
        return $query->where('role', 'seller');
    }

    public function scopeCustomers($query)
    {
        return $query->where('role', 'customer');
    }

    // Methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isSeller()
    {
        return $this->role === 'seller';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }
}
