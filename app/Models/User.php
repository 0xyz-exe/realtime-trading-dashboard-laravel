<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'cash_balance_cents'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function holdings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Holding::class);
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TradingTransaction::class);
    }

    public function watchlists(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    public function hasSufficientCash(int $cents): bool
    {
        return $this->cash_balance_cents >= $cents;
    }

    public function getHolding(int $tradingPairId): ?Holding
    {
        return $this->holdings()->where('trading_pair_id', $tradingPairId)->first();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'cash_balance_cents' => 'integer',
        ];
    }



    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
}
