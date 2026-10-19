<?php

namespace App\Services\Recommandation;

use App\Models\ConsultationProjet;
use App\Models\Role;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Records that a citizen really opened a project detail page.
 *
 * A consultation is an IMPLICIT signal: it means the citizen looked at the
 * project, nothing more. It is deliberately the weakest input of the ranking.
 *
 * PRIVACY: only (citoyen, projet, horodatage) is stored. No IP address, no
 * geolocation, no user agent, no device fingerprint — none of it is needed to
 * rank projects, so none of it is collected.
 *
 * The recorder is FIRE-AND-FORGET: it never breaks the page it sits in. A
 * failure is logged and swallowed, because a missing bookmark in a ranking is
 * never worth a 500.
 */
class EnregistreurConsultation
{
    /**
     * Record the view unless it falls inside an already-recorded window.
     *
     * @return bool true when a new row was written
     */
    public function enregistrer(Request $request, int $projetId): bool
    {
        $user = $request->user();

        // Only a real, active citizen generates a signal. Robots, technical
        // probes and any request without a session are simply ignored.
        if (! $user instanceof \App\Models\User || ! $user->hasAnyRole([Role::CITOYEN])) {
            return false;
        }

        try {
            // The unique index on (user_id, projet_id, bucket) is what actually
            // enforces the grouping window — not this check. A refresh, a
            // re-rendered icon or a repeated AJAX call inside the window hits
            // the index and produces no second row.
            $existe = ConsultationProjet::query()
                ->where('user_id', $user->id)
                ->where('projet_id', $projetId)
                ->where('bucket', ConsultationProjet::bucketCourant())
                ->exists();

            if ($existe) {
                return false;
            }

            ConsultationProjet::create([
                'user_id'   => $user->id,
                'projet_id' => $projetId,
                'bucket'    => ConsultationProjet::bucketCourant(),
            ]);

            return true;
        } catch (QueryException $e) {
            // A race between two simultaneous views: the loser hits the unique
            // index. That is the constraint doing its job, not an error.
            if ($e->getCode() === '23000') {
                return false;
            }

            Log::warning('Consultation de projet non enregistrée.', [
                'projet_id' => $projetId,
                'erreur'    => $e->getMessage(),
            ]);

            return false;
        } catch (\Throwable $e) {
            // Never let logging break the project page.
            Log::warning('Consultation de projet non enregistrée.', [
                'projet_id' => $projetId,
                'erreur'    => $e->getMessage(),
            ]);

            return false;
        }
    }
}
