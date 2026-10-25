<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trading_pair_id',
        'side',
        'type',
        'price_cents',
        'quantity',
        'total_cents',
        'status',
        'filled_at',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'quantity' => 'decimal:6',
            'total_cents' => 'integer',
            'filled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tradingPair(): BelongsTo
    {
        return $this->belongsTo(TradingPair::class);
    }
}
