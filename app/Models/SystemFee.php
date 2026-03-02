<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'fee_type',
        'fee_value',
        'description',
        'is_platform_commission',
        'platform_fee_percent',
        'transaction_fee_percent',
        'shipping_fee_default',
    ];

    protected $casts = [
        'is_platform_commission' => 'boolean',
    ];

    /**
     * Get platform commission percentage
     */
    public static function getPlatformCommission()
    {
        $fee = static::where('is_platform_commission', true)->first();
        return $fee?->fee_value ?? 10; // Default 10%
    }

    /**
     * Get seller percentage (100 - admin %)
     */
    public static function getSellerPercentage()
    {
        return 100 - static::getPlatformCommission();
    }

    public $timestamps = true;
}
