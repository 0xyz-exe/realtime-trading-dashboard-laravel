<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trading_pair_id')->constrained()->cascadeOnDelete();
            $table->string('side'); // buy, sell
            $table->string('type'); // market, limit
            $table->bigInteger('price_cents');
            $table->decimal('quantity', 18, 6);
            $table->bigInteger('total_cents');
            $table->string('status')->default('open'); // open, filled, cancelled
            $table->dateTime('filled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
