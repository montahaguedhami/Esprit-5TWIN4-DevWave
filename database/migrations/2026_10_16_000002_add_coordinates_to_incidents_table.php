<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            if (! Schema::hasColumn('incidents', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('incidents', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            if (Schema::hasColumn('incidents', 'latitude')) {
                $table->dropColumn('latitude');
            }
            if (Schema::hasColumn('incidents', 'longitude')) {
                $table->dropColumn('longitude');
            }
        });
    }
};
