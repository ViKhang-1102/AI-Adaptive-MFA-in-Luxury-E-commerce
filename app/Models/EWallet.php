<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EWallet extends Model
{
    use HasFactory;

    protected $table = 'e_wallets';

    protected $fillable = [
        'user_id',
        'balance',
        'total_received',
        'total_spent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id');
    }

    /**
     * Adjust the wallet balance atomically by delta (positive or negative)
     * Ensures balance never goes below zero.
     */
    public function adjustBalance(float $delta)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($delta) {
            $this->refresh();
            $new = $this->balance + $delta;
            if ($new < 0) {
                $new = 0;
            }
            $this->balance = $new;
            $this->save();
            return $this->balance;
        });
    }
}

