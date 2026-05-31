<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignUuid('method_id')->nullable()->constrained('methods')->nullOnDelete();
            $table->foreignUuid('sub_method_id')->nullable()->constrained('sub_methods')->nullOnDelete();
            $table->string('type');       // deposit|withdrawal|buy|sell
            $table->string('direction');  // credit|debit
            $table->decimal('amount', 20, 8);
            $table->string('reference')->nullable();
            $table->string('status')->default('pending');
            $table->foreignUuid('updated_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'asset_id', 'status']);
            $table->index(['type', 'direction', 'status']);
            $table->index(['method_id', 'sub_method_id']);
            $table->index(['updated_by', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
