<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePointMesureRequest;
use App\Http\Requests\UpdatePointMesureRequest;
use App\Models\AuditLog;
use App\Models\Infrastructure;
use App\Models\PointMesure;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PointMesureController extends Controller
{
    public function index(Request $request): View
    {
        $query = PointMesure::with(['derniereMesure', 'infrastructure.zone']);

        // Search by keyword in name, code, zone, address
        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('zone', 'like', "%{$search}%")
                  ->orWhere('adresse', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($statut = $request->input('statut')) {
            if (in_array($statut, ['actif', 'inactif'], true)) {
                $query->where('statut', $statut);
            }
        }

        // Filter by type
        if ($type = $request->input('type')) {
            if (in_array($type, ['Station', 'Puits', 'Réservoir', 'Réseau', 'Autre'], true)) {
                $query->where('type', $type);
            }
        }

        // Sorting
        $sort = $request->input('sort', 'nom_asc');
        match ($sort) {
            'nom_desc'  => $query->orderByDesc('nom'),
            'code_asc'  => $query->orderBy('code'),
            'code_desc' => $query->orderByDesc('code'),
            'recent'    => $query->latest('id'),
            default     => $query->orderBy('nom'),
        };

        $points = $query->paginate(15)->withQueryString();

        return view('manager.points-mesure.index', compact('points'));
    }

    public function create(): View
    {
        $zones     = Zone::orderBy('nom')->pluck('nom')->all();
        $nextCode  = $this->generateUniqueCode(null);
        $pointForm = [
            'zones'   => $zones,
            'nextCode' => $nextCode,
            'infrastructures' => Infrastructure::with('zone')->orderBy('nom')->get(),
        ];

        return view('manager.points-mesure.create', $pointForm);
    }

    public function store(StorePointMesureRequest $request): RedirectResponse
    {
        $point = DB::transaction(function () use ($request) {
            $data             = $request->validated();
            $data             = $this->syncInfrastructureZone($data);
            $data['code']     = $this->generateUniqueCode($data['code'] ?? null);
            $point = PointMesure::create($data);

            AuditLog::record('point_mesure_created', 'PointMesure', $point->id, [
                'code' => $point->code,
                'nom'  => $point->nom,
            ], auth()->user());

            return $point;
        });

        return redirect()->route('manager.points-mesure.show', $point)->with('success', 'Le point de mesure a été créé avec succès.');
    }

    public function show(PointMesure $point_mesure): View
    {
        $point_mesure->load([
            'infrastructure.zone',
            'mesuresQualites' => fn ($query) => $query->latest('date_mesure'),
        ]);

        return view('manager.points-mesure.show', ['point' => $point_mesure]);
    }

    public function edit(PointMesure $point_mesure): View
    {
        $zones = Zone::orderBy('nom')->pluck('nom')->all();

        return view('manager.points-mesure.edit', [
            'point' => $point_mesure,
            'zones' => $zones,
            'infrastructures' => Infrastructure::with('zone')->orderBy('nom')->get(),
        ]);
    }

    public function update(UpdatePointMesureRequest $request, PointMesure $point_mesure): RedirectResponse
    {
        DB::transaction(function () use ($request, $point_mesure) {
            $data           = $request->validated();
            $data           = $this->syncInfrastructureZone($data);
            $data['code']   = $this->resolveExistingCode($request, $point_mesure);
            $point_mesure->update($data);

            AuditLog::record('point_mesure_updated', 'PointMesure', $point_mesure->id, [
                'code' => $point_mesure->code,
                'nom'  => $point_mesure->nom,
            ], auth()->user());
        });

        return redirect()->route('manager.points-mesure.show', $point_mesure)->with('success', 'Le point de mesure a été mis à jour avec succès.');
    }

    public function destroy(PointMesure $point_mesure): RedirectResponse
    {
        return DB::transaction(function () use ($point_mesure) {
            if ($point_mesure->mesuresQualites()->exists()) {
                $point_mesure->update(['statut' => 'inactif']);

                AuditLog::record('point_mesure_deactivated', 'PointMesure', $point_mesure->id, [
                    'code'   => $point_mesure->code,
                    'reason' => 'has_historical_measurements',
                ], auth()->user());

                return redirect()->route('manager.points-mesure.index')
                    ->with('success', 'Le point possède un historique de mesures : il a été désactivé pour préserver l’intégrité des données.');
            }

            $code = $point_mesure->code;
            $point_mesure->delete();

            AuditLog::record('point_mesure_deleted', 'PointMesure', null, [
                'code' => $code,
            ], auth()->user());

            return redirect()->route('manager.points-mesure.index')->with('success', 'Le point de mesure a été supprimé.');
        });
    }

    /**
     * Keep an explicitly supplied code, otherwise keep the existing one.
     *
     * The "update" form no longer asks for the code: it is only kept
     * when the request still carries one (backward compatibility).
     */
    private function resolveExistingCode(UpdatePointMesureRequest $request, PointMesure $point_mesure): string
    {
        $code = $request->input('code');

        return $code !== null && $code !== ''
            ? $code
            : $point_mesure->code;
    }

    /**
     * Keep the point's zone in sync with its linked network infrastructure.
     *
     * External sampling stations may remain unlinked, so a null infrastructure
     * keeps the legacy zone label while clearing the relational zone foreign key.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function syncInfrastructureZone(array $data): array
    {
        if (! array_key_exists('infrastructure_id', $data)) {
            return $data;
        }

        if (empty($data['infrastructure_id'])) {
            $data['zone_id'] = null;

            return $data;
        }

        $infrastructure = Infrastructure::with('zone')->findOrFail($data['infrastructure_id']);
        $data['zone_id'] = $infrastructure->zone_id;

        if ($infrastructure->zone) {
            $data['zone'] = $infrastructure->zone->nom;
        }

        return $data;
    }

    /**
     * Generate the next unique point code (e.g. PMS-001).
     *
     * Used when the client did not send a code (the create form leaves the
     * code field empty and readonly); an explicit code always wins.
     */
    private function generateUniqueCode(?string $code): string
    {
        if ($code !== null && $code !== '') {
            return $code;
        }

        $max = 0;
        foreach (PointMesure::pluck('code') as $existingCode) {
            if (preg_match('/(\d+)$/', (string) $existingCode, $matches) === 1) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return sprintf('PMS-%04d', $max + 1);
    }
}
