<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holdings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trading_pair_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 18, 6)->default(0);
            $table->bigInteger('avg_buy_price_cents')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'trading_pair_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holdings');
    }
};
