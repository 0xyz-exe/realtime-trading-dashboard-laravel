<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watchlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trading_pair_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'trading_pair_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlists');
    }
};
