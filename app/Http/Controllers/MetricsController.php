<?php

namespace App\Http\Controllers;

use App\Services\Monitoring\MetricsCollector;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Point de collecte Prometheus (supervision).
 *
 * Désactivé (404) tant que METRICS_TOKEN n'est pas défini ; sinon le jeton est exigé
 * en en-tête « Authorization: Bearer … » (comparaison à temps constant).
 */
class MetricsController extends Controller
{
    public function __invoke(Request $request, MetricsCollector $collector): Response
    {
        $jeton = (string) config('services.metrics.token');
        abort_if($jeton === '', 404);
        abort_unless(hash_equals($jeton, (string) $request->bearerToken()), 401);

        return response($collector->render(), 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }
}
