<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'description',
        'order_id',
        'reference_type',
        'reference_id',
        'status',
        'payout_approved_at',
        'payout_rejected_at',
    ];

    protected $casts = [
        'payout_approved_at' => 'datetime',
        'payout_rejected_at' => 'datetime',
    ];

    public function wallet()
    {
        return $this->belongsTo(EWallet::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
