<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'discount_price',
        'discount_start_date',
        'discount_end_date',
        'discount_percent',
        'stock',
        'views',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'discount_start_date' => 'datetime',
        'discount_end_date' => 'datetime',
    ];

    // Relationships
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlistItems()
    {
        return $this->hasMany(Wishlist::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOnDiscount($query)
    {
        return $query->whereNotNull('discount_percent')
            ->where('discount_start_date', '<=', now())
            ->where('discount_end_date', '>=', now());
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    // Methods
    public function hasDiscount()
    {
        return !is_null($this->discount_percent) &&
               $this->discount_start_date <= now() &&
               $this->discount_end_date >= now();
    }

    public function getDiscountedPrice()
    {
        if ($this->hasDiscount()) {
            return $this->price - ($this->price * ($this->discount_percent / 100));
        }
        return $this->price;
    }

    public function getAverageRating()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getReviewCount()
    {
        return $this->reviews()->count();
    }
}
