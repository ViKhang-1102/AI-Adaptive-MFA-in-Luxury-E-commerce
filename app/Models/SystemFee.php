<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform_fee_percent',
        'transaction_fee_percent',
        'shipping_fee_default',
        'description',
    ];

    public $timestamps = true;
}
