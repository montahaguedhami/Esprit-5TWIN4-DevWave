<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Dedicated table for the OTP based password recovery flow.
     * Only hashed secrets are stored — never the OTP nor the reset token in clear text.
     */
    public function up(): void
    {
        Schema::create('password_reset_otps', function (Blueprint $table) {
            $table->id();

            // Account targeted by the recovery request.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email')->index();

            // Step 1 secret: the 6-digit OTP, stored hashed only.
            $table->string('otp_hash');
            $table->timestamp('otp_expires_at');

            // Attempt tracking: the OTP is invalidated past the allowed number of tries.
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(5);

            // Step 2 -> 3 proof: random token issued only after a successful OTP validation.
            $table->string('reset_token_hash')->nullable();
            $table->timestamp('reset_token_expires_at')->nullable();

            // Lifecycle timestamps.
            $table->timestamp('verified_at')->nullable();   // OTP validated successfully
            $table->timestamp('consumed_at')->nullable();   // password already reset with this request

            // Forensic metadata (useful for the audit trail).
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            $table->index(['email', 'consumed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_otps');
    }
};
