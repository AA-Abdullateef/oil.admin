<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('symbol')->unique();
            $table->string('name');
            $table->string('type'); // currency|crypto|share|commodity
            $table->decimal('current_price', 20, 8)->default(0);
            $table->string('price_source')->nullable();
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
