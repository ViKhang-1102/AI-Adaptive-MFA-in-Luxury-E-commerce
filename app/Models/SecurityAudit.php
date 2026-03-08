<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'amount',
        'risk_score',
        'level',
        'suggestion',
        'result',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'risk_score' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
