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
}
