<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_price',      // Snapshot of product price at time of order creation
        'quantity',
        'subtotal',            // product_price * quantity
    ];

    /**
     * The "product_price" field stores the price snapshot at the time the order was created.
     * This is important because product prices can change after an order is placed.
     * Customers always see the price they paid, not the current product price.
     */

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

