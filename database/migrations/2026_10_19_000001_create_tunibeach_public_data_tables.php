<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tunibeach_governorates', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('tunibeach_beaches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('governorate_id')->constrained('tunibeach_governorates')->restrictOnDelete();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('coordinates_verified')->default(false);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
            $table->index(['governorate_id', 'is_published', 'name'], 'tunibeach_beach_public_lookup_idx');
        });

        Schema::create('tunibeach_measurements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('beach_id')->constrained('tunibeach_beaches')->cascadeOnDelete();
            $table->string('reference', 120)->nullable();
            $table->dateTime('sampled_at');
            $table->json('parameters')->nullable();
            $table->string('source_name', 180)->nullable();
            $table->text('source_url')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            $table->index(['beach_id', 'is_public', 'sampled_at'], 'tunibeach_measurement_public_date_idx');
        });

        Schema::create('tunibeach_bathing_statuses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('beach_id')->constrained('tunibeach_beaches')->cascadeOnDelete();
            $table->string('status', 20);
            $table->string('source_name', 180);
            $table->text('source_url')->nullable();
            $table->string('source_reference', 120)->nullable();
            $table->dateTime('issued_at')->nullable();
            $table->dateTime('valid_from');
            $table->dateTime('valid_until');
            $table->dateTime('verified_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->index(['beach_id', 'is_verified', 'valid_from', 'valid_until'], 'tunibeach_status_validity_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tunibeach_bathing_statuses');
        Schema::dropIfExists('tunibeach_measurements');
        Schema::dropIfExists('tunibeach_beaches');
        Schema::dropIfExists('tunibeach_governorates');
    }
};
