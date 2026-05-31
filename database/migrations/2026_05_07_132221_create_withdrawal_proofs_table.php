<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_proofs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->string('destination_type');
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('wallet_address')->nullable();
            $table->string('network')->nullable();
            $table->string('proof')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_proofs');
    }
};
