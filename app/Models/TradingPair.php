<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TradingPair extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'name',
        'base_asset',
        'quote_asset',
        'current_price_cents',
        'change_24h',
        'high_price_cents',
        'low_price_cents',
        'volume_cents',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'current_price_cents' => 'integer',
            'change_24h' => 'decimal:2',
            'high_price_cents' => 'integer',
            'low_price_cents' => 'integer',
            'volume_cents' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function holdings(): HasMany
    {
        return $this->hasMany(Holding::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->current_price_cents / 100, 2);
    }
}
