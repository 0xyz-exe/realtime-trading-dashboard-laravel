<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trading_pairs', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique(); // e.g. BTC/USD
            $table->string('name'); // e.g. Bitcoin
            $table->string('base_asset'); // e.g. BTC
            $table->string('quote_asset')->default('USD');
            $table->bigInteger('current_price_cents');
            $table->decimal('change_24h', 6, 2)->default(0.00);
            $table->bigInteger('high_price_cents')->default(0);
            $table->bigInteger('low_price_cents')->default(0);
            $table->bigInteger('volume_cents')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trading_pairs');
    }
};
