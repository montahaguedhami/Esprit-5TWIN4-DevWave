<?php

namespace App\Http\Controllers\Technician;

use App\Enums\InfrastructureEtat;
use App\Http\Controllers\Controller;
use App\Models\InfrastructureAlerte;
use App\Models\Intervention;
use App\Services\TechnicianScopeService;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    /**
     * Liste des interventions du technicien connecté.
     */
    public function index(Request $request)
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        abort_unless($technicien, 403, 'Compte technicien introuvable.');

        $query = Intervention::query()
            ->where('technicien_id', $technicien->id)
            ->with(['infrastructure.zone'])
            ->latest('date');

        // Filtre par statut
        $selectedStatus = $request->query('status');
        if ($selectedStatus && $selectedStatus !== 'all') {
            $mappedStatus = match($selectedStatus) {
                'scheduled' => 'Planifiée',
                'in_progress' => 'En cours',
                'completed' => 'Terminée',
                default => $selectedStatus,
            };
            $query->where('statut', $mappedStatus);
        }

        $interventions = $query->paginate(15)->withQueryString();

        // Totaux pour les KPI
        $all = Intervention::where('technicien_id', $technicien->id)->get();
        $counts = [
            'total' => $all->count(),
            'scheduled' => $all->where('statut', 'Planifiée')->count(),
            'in_progress' => $all->where('statut', 'En cours')->count(),
            'completed' => $all->where('statut', 'Terminée')->count(),
        ];

        return view('technician.interventions.index', compact('interventions', 'technicien', 'counts', 'selectedStatus'));
    }

    /**
     * Fiche d'une intervention pour le technicien.
     */
    public function show($id)
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        abort_unless($technicien, 403, 'Compte technicien introuvable.');

        $intervention = Intervention::with(['infrastructure.zone', 'infrastructure.alertesActives'])->findOrFail($id);

        // Sécurité : l'intervention doit appartenir à ce technicien
        abort_unless(
            $intervention->technicien_id === $technicien->id,
            403,
            "Vous n'êtes pas assigné à cette intervention."
        );

        return view('technician.interventions.show', compact('intervention', 'technicien'));
    }

    /**
     * Démarrer une intervention planifiée.
     */
    public function start($id)
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        abort_unless($technicien, 403, 'Compte technicien introuvable.');

        $intervention = Intervention::findOrFail($id);
        abort_unless($intervention->technicien_id === $technicien->id, 403);

        $intervention->statut = 'En cours';
        $intervention->save();

        // Mettre à jour l'infrastructure en maintenance si applicable
        if ($intervention->infrastructure && $intervention->infrastructure->etat !== InfrastructureEtat::EnMaintenance) {
            $infra = $intervention->infrastructure;
            $infra->etat_changement_commentaire = "[Terrain - {$technicien->nom}] Démarrage intervention #{$intervention->id}";
            $infra->etat = InfrastructureEtat::EnMaintenance;
            $infra->save();
        }

        return redirect()
            ->route('technician.interventions.show', $intervention)
            ->with('success', "L'intervention #{$intervention->id} est désormais En cours.");
    }

    /**
     * Formulaire de rapport d'intervention.
     */
    public function report($id)
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        abort_unless($technicien, 403, 'Compte technicien introuvable.');

        $intervention = Intervention::with(['infrastructure.zone'])->findOrFail($id);
        abort_unless($intervention->technicien_id === $technicien->id, 403);

        $etats = InfrastructureEtat::cases();

        return view('technician.interventions.report', compact('intervention', 'technicien', 'etats'));
    }

    /**
     * Soumission du rapport d'intervention terrain.
     * RÈGLES MÉTIER :
     * 1. Intervention passée à 'Terminée'.
     * 2. Infrastructure : mise à jour de l'état, de la date de dernière maintenance, et consignation dans l'historique.
     * 3. Clôture automatique de toutes les alertes opérationnelles actives de l'ouvrage.
     */
    public function storeReport(Request $request, $id)
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        abort_unless($technicien, 403, 'Compte technicien introuvable.');

        $intervention = Intervention::with(['infrastructure'])->findOrFail($id);
        abort_unless($intervention->technicien_id === $technicien->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:resolved,partial,failed,followup',
            'work_performed' => 'required|string|min:5|max:3000',
            'cause' => 'nullable|string|max:1000',
            'observations' => 'nullable|string|max:1000',
            'nouvel_etat' => 'nullable|string',
            'parts' => 'nullable|array',
        ], [
            'status.required' => 'Veuillez préciser le résultat de l’intervention.',
            'work_performed.required' => 'Le descriptif des travaux réalisés sur le terrain est obligatoire.',
            'work_performed.min' => 'Le descriptif des travaux doit comporter au moins :min caractères.',
        ]);

        // 1. Clôture de l'intervention
        $intervention->statut = 'Terminée';

        $piecesTxt = '';
        if (! empty($validated['parts']) && is_array($validated['parts'])) {
            $validParts = array_filter($validated['parts'], fn ($p) => ! empty($p['name']));
            if (! empty($validParts)) {
                $piecesTxt = ' | Pièces : ' . implode(', ', array_map(fn ($p) => ($p['qty'] ?? 1) . 'x ' . $p['name'], $validParts));
            }
        }

        $rapportSummary = "\n\n[Rapport du " . now()->format('d/m/Y H:i') . " par " . $technicien->nom . "]\n"
            . "Résultat : " . ucfirst($validated['status']) . "\n"
            . (! empty($validated['cause']) ? "Cause : " . $validated['cause'] . "\n" : '')
            . "Travaux réalisés : " . $validated['work_performed'] . "\n"
            . (! empty($validated['observations']) ? "Observations : " . $validated['observations'] . "\n" : '')
            . $piecesTxt;

        $intervention->description = trim(($intervention->description ?? '') . $rapportSummary);
        $intervention->save();

        // 2. Mise à jour de l'infrastructure liée
        $infra = $intervention->infrastructure;
        if ($infra) {
            // Mise à jour de la date de dernière maintenance
            $infra->date_derniere_maintenance = now()->toDateString();

            // Calcul du nouvel état opérationnel
            $nouvelEtat = $validated['nouvel_etat'] ?? null;
            if (! $nouvelEtat) {
                $nouvelEtat = match($validated['status']) {
                    'resolved' => InfrastructureEtat::EnService->value,
                    'partial' => InfrastructureEtat::EnMaintenance->value,
                    'failed' => InfrastructureEtat::HorsService->value,
                    'followup' => InfrastructureEtat::EnMaintenance->value,
                    default => InfrastructureEtat::EnService->value,
                };
            }

            $infra->etat_changement_commentaire = "[Clôture Intervention #{$intervention->id} par {$technicien->nom}] " . $validated['work_performed'];
            $infra->etat = $nouvelEtat;
            $infra->save(); // Déclenche InfrastructureObserver -> consigne dans InfrastructureHistorique

            // 3. Clôturer l'alerte opérationnelle liée
            InfrastructureAlerte::where('infrastructure_id', $infra->id)
                ->where('resolue', false)
                ->update(['resolue' => true]);
        }

        return redirect()
            ->route('technician.interventions.show', $intervention)
            ->with('success', "Le rapport d'intervention a été enregistré avec succès. L'infrastructure a été actualisée et les alertes opérationnelles associées ont été clôturées.");
    }
}
