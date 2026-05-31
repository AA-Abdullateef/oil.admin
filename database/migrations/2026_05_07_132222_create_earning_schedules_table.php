<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('earning_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->decimal('percentage', 10, 4);
            $table->string('frequency'); // daily|weekly|monthly
            $table->timestamp('start_date');
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->string('status')->default('active'); // active|paused
            $table->foreignUuid('created_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'next_run_at']);
            $table->index(['asset_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earning_schedules');
    }
};
