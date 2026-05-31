<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignUuid('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignUuid('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();

            // KYC status lifecycle: pending → submitted → under_review → verified | rejected
            $table->string('kyc_status')->default('pending');
            $table->timestamp('kyc_submitted_at')->nullable();
            $table->timestamp('kyc_reviewed_at')->nullable();
            $table->foreignUuid('kyc_reviewed_by')->nullable()->references('id')->on('users');
            $table->text('kyc_rejection_reason')->nullable();

            // Document storage paths (uploaded by user)
            $table->string('id_document_front')->nullable();  // passport/NIN front
            $table->string('id_document_back')->nullable();   // where applicable
            $table->string('selfie_with_id')->nullable();     // liveness check
            $table->string('proof_of_address')->nullable();   // utility bill etc.
            $table->string('id_document_type')->nullable();   // passport|national_id|drivers_license

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
