<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holding extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trading_pair_id',
        'quantity',
        'avg_buy_price_cents',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:6',
            'avg_buy_price_cents' => 'integer',
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

    public function getCurrentValueCentsAttribute(): int
    {
        return (int) round(((float) $this->quantity) * ($this->tradingPair->current_price_cents));
    }
}
