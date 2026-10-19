<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('point_mesures', 'infrastructure_id')) {
            Schema::table('point_mesures', function (Blueprint $table): void {
                $table->unsignedBigInteger('infrastructure_id')->nullable()->after('zone_id');
            });
        }

        $hasInfrastructureForeignKey = collect(Schema::getForeignKeys('point_mesures'))
            ->contains(fn (array $foreignKey): bool => ($foreignKey['columns'] ?? []) === ['infrastructure_id']);

        if (! $hasInfrastructureForeignKey) {
            Schema::table('point_mesures', function (Blueprint $table): void {
                $table->foreign('infrastructure_id')
                    ->references('id')
                    ->on('infrastructures')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('point_mesures', 'infrastructure_id')) {
            return;
        }

        Schema::table('point_mesures', function (Blueprint $table): void {
            $table->dropForeign(['infrastructure_id']);
            $table->dropColumn('infrastructure_id');
        });
    }
};
