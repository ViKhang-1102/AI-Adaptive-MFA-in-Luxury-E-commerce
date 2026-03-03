<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'product_id',
        'message',
        'read',
    ];

    protected $casts = [
        'read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeForConversation($query, $user1Id, $user2Id, $productId)
    {
        return $query->where('product_id', $productId)
            ->where(function ($q) use ($user1Id, $user2Id) {
                $q->where(function ($q) use ($user1Id, $user2Id) {
                    $q->where('sender_id', $user1Id)
                      ->where('receiver_id', $user2Id);
                })->orWhere(function ($q) use ($user1Id, $user2Id) {
                    $q->where('sender_id', $user2Id)
                      ->where('receiver_id', $user1Id);
                });
            })
            ->orderBy('created_at', 'asc');
    }
}
