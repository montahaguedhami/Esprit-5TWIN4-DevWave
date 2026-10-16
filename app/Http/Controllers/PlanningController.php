<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Models\Technicien;
use App\Services\InterventionPlanningService;
use App\Services\PreventiveMaintenanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanningController extends Controller
{
    /**
     * Couleur de chaque statut dans le calendrier.
     */
    private const COULEURS = [
        'Planifiée' => '#0ea5e9',
        'En cours' => '#f59e0b',
        'Terminée' => '#10b981',
        'Annulée' => '#64748b',
    ];

    public function __construct(private InterventionPlanningService $planning)
    {
    }

    /**
     * Calendrier des interventions.
     */
    public function index()
    {
        return view('manager.planning.index', [
            'techniciens' => Technicien::orderBy('nom')->get(['id', 'nom', 'disponibilite']),
            'conflits' => $this->planning->conflitsExistants(today()),
            'enConge' => $this->planning->affectationsEnConge(),
            'couleurs' => self::COULEURS,
        ]);
    }

    /**
     * Flux JSON des interventions pour FullCalendar (plage start/end, filtre technicien).
     */
    public function evenements(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
            'technicien_id' => ['nullable', 'integer', 'exists:techniciens,id'],
        ]);

        $interventions = Intervention::query()
            ->with(['technicien:id,nom', 'infrastructure:id,nom'])
            ->whereDate('date', '>=', Carbon::parse($validated['start'])->toDateString())
            ->whereDate('date', '<', Carbon::parse($validated['end'])->toDateString())
            ->when($validated['technicien_id'] ?? null, fn ($query, $id) => $query->where('technicien_id', $id))
            ->get();

        // Interventions actives partageant technicien + jour = conflit
        $enConflit = $interventions
            ->whereIn('statut', InterventionPlanningService::STATUTS_ACTIFS)
            ->groupBy(fn (Intervention $i) => $i->technicien_id.'|'.$i->date->toDateString())
            ->filter(fn ($groupe) => $groupe->count() > 1)
            ->flatten()
            ->pluck('id')
            ->all();

        return response()->json($interventions->map(function (Intervention $intervention) use ($enConflit) {
            $conflit = in_array($intervention->id, $enConflit, true);
            $couleur = self::COULEURS[$intervention->statut] ?? '#0ea5e9';

            return [
                'id' => $intervention->id,
                'title' => ($intervention->estPreventive() ? '⟳ ' : '').($intervention->technicien?->nom ?? 'Sans technicien').' — '.str($intervention->description)->limit(40),
                'start' => $intervention->date->toDateString(),
                'allDay' => true,
                'url' => route('manager.interventions.show', $intervention),
                'backgroundColor' => $couleur,
                'borderColor' => $conflit ? '#f43f5e' : $couleur,
                'classNames' => $conflit ? ['fc-conflit'] : [],
                // Seules les interventions actives peuvent être déplacées
                'editable' => in_array($intervention->statut, InterventionPlanningService::STATUTS_ACTIFS, true),
                'extendedProps' => [
                    'statut' => $intervention->statut,
                    'type' => $intervention->type,
                    'technicien' => $intervention->technicien?->nom,
                    'infrastructure' => $intervention->infrastructure?->nom,
                    'conflit' => $conflit,
                ],
            ];
        })->values());
    }

    /**
     * Lance (ou simule) la planification préventive depuis le calendrier.
     */
    public function preventive(Request $request, PreventiveMaintenanceService $preventive)
    {
        $simulation = $request->boolean('simulation');
        $lignes = $preventive->planifier(null, $simulation);
        $planifiees = $lignes->where('resultat', PreventiveMaintenanceService::PLANIFIEE)->count();

        $rapport = $lignes->map(fn (array $ligne) => [
            'infrastructure' => $ligne['infrastructure']->nom,
            'echeance' => $ligne['echeance']->format('d/m/Y'),
            'resultat' => $ligne['resultat'],
            'date' => $ligne['date']?->format('d/m/Y'),
            'technicien' => $ligne['technicien']?->nom,
            'hors_specialite' => $ligne['hors_specialite'],
            'intervention_id' => $ligne['intervention']?->id,
        ])->all();

        $message = match (true) {
            $lignes->isEmpty() => 'Aucune maintenance préventive n\'arrive à échéance.',
            $simulation => "Simulation : {$planifiees} intervention(s) préventive(s) seraient créées.",
            default => "{$planifiees} intervention(s) préventive(s) créée(s).",
        };

        return redirect()
            ->route('manager.planning.index')
            ->with('success', $message)
            ->with('rapport_preventif', ['simulation' => $simulation, 'lignes' => $rapport]);
    }

    /**
     * Replanifie une intervention (glisser-déposer) après vérification des conflits.
     */
    public function deplacer(Request $request, Intervention $intervention): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', Rule::when($intervention->statut === 'Terminée', 'before_or_equal:today')],
        ], [
            'date.before_or_equal' => 'Une intervention terminée ne peut pas avoir une date future.',
        ]);

        if (! in_array($intervention->statut, InterventionPlanningService::STATUTS_ACTIFS, true)) {
            return response()->json(['message' => 'Seules les interventions planifiées ou en cours peuvent être déplacées.'], 422);
        }

        $message = $this->planning->verifier(
            $intervention->technicien_id,
            $validated['date'],
            $intervention->statut,
            $intervention->id
        );

        if ($message) {
            return response()->json(['message' => $message], 422);
        }

        $intervention->update(['date' => $validated['date']]);

        return response()->json([
            'message' => 'Intervention #'.$intervention->id.' replanifiée au '.$intervention->date->format('d/m/Y').'.',
        ]);
    }
}
