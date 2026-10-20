<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * computed_at passe de TIMESTAMP à DATETIME.
     *
     * Sous MariaDB/MySQL avec explicit_defaults_for_timestamp = 0 (défaut de XAMPP), la première colonne
     * TIMESTAMP reçoit implicitement « ON UPDATE CURRENT_TIMESTAMP » : chaque mise à jour d'une prédiction
     * remplaçait sa date de calcul par l'heure courante, faussant l'historique et provoquant des doublons
     * sur la clé unique (infrastructure_id, computed_at).
     */
    public function up(): void
    {
        Schema::table('infrastructure_risk_predictions', function (Blueprint $table) {
            $table->dateTime('computed_at')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('infrastructure_risk_predictions', function (Blueprint $table) {
            $table->timestamp('computed_at')->change();
        });
    }
};
