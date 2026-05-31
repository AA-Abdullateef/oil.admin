<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('earnings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignUuid('schedule_id')->nullable()->constrained('earning_schedules')->nullOnDelete();
            $table->string('type'); // daily|weekly|monthly
            $table->decimal('amount', 20, 8);
            $table->string('status')->default('processed'); // processed|cancelled
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'asset_id', 'status']);
            $table->index(['schedule_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earnings');
    }
};
