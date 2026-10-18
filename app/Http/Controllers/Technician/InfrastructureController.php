<?php

namespace App\Http\Controllers\Technician;

use App\Enums\InfrastructureEtat;
use App\Enums\InfrastructureType;
use App\Http\Controllers\Controller;
use App\Models\Infrastructure;
use App\Services\QrCodeService;
use App\Services\TechnicianScopeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class InfrastructureController extends Controller
{
    /**
     * Liste des infrastructures liées UNIQUEMENT aux interventions du technicien connecté.
     */
    public function index(Request $request)
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        abort_unless($technicien, 403, 'Compte technicien introuvable.');

        $query = TechnicianScopeService::infrastructuresQuery($technicien)
            ->with('latestRiskPrediction');

        // Recherche par mot-clé
        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->deType($request->input('type'));
        }

        // Filtre par état
        if ($request->filled('etat')) {
            $query->deEtat($request->input('etat'));
        }

        // Filtre optionnel : interventions ouvertes uniquement
        if ($request->boolean('open_only')) {
            $query->whereHas('interventions', function ($q) use ($technicien): void {
                $q->where('technicien_id', $technicien->id)
                    ->whereIn('statut', ['Planifiée', 'En cours']);
            });
        }

        $infrastructures = $query->paginate(10)->withQueryString();

        // Statistiques du périmètre d'intervention du technicien
        $allMyInfras = TechnicianScopeService::infrastructuresQuery($technicien)->get();
        $stats = [
            'total' => $allMyInfras->count(),
            'en_service' => $allMyInfras->where('etat', InfrastructureEtat::EnService)->count(),
            'en_maintenance' => $allMyInfras->where('etat', InfrastructureEtat::EnMaintenance)->count(),
            'hors_service' => $allMyInfras->where('etat', InfrastructureEtat::HorsService)->count(),
        ];

        $types = InfrastructureType::cases();
        $etats = InfrastructureEtat::cases();

        return view('technician.infrastructures.index', compact(
            'infrastructures',
            'technicien',
            'stats',
            'types',
            'etats'
        ));
    }

    /**
     * Fiche technique de l'infrastructure (accès restreint au périmètre du technicien).
     * 403 Forbidden si l'ouvrage n'est pas dans ses interventions.
     */
    public function show(Infrastructure $infrastructure)
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        abort_unless($technicien, 403, 'Compte technicien introuvable.');

        // RÈGLE MÉTIER : 403 si l'ouvrage n'est pas rattaché à une intervention du technicien
        abort_unless(
            TechnicianScopeService::canAccessInfrastructure($technicien, $infrastructure),
            403,
            "Accès refusé : cette infrastructure n'est rattachée à aucune de vos interventions."
        );

        $infrastructure->load(['zone', 'historiques', 'alertesActives', 'latestRiskPrediction']);
        $riskPrediction = $infrastructure->latestRiskPrediction;
        $risk = $riskPrediction
            ? [
                'probability' => $riskPrediction->probability,
                'level' => $riskPrediction->level,
                'factors' => $riskPrediction->factors ?? [],
            ]
            : app(\App\Services\FailureRiskService::class)->predict($infrastructure);
        $risk['recommendation'] = app(\App\Services\FailureRiskService::class)->recommendationFor($risk['factors'], $risk['level'], null, $infrastructure->id);

        // Récupérer UNIQUEMENT les interventions de ce technicien sur cet ouvrage (sans les coûts)
        $interventions = $infrastructure->interventions()
            ->where('technicien_id', $technicien->id)
            ->orderByDesc('date')
            ->get();

        $hasOpenIntervention = TechnicianScopeService::hasOpenIntervention($technicien, $infrastructure);
        $openIntervention = TechnicianScopeService::getOpenIntervention($technicien, $infrastructure);

        // Générer le QR Code vectoriel pour l'accès mobile rapide
        $qrCodeSvg = QrCodeService::svg(route('technician.infrastructures.show', $infrastructure), 160);

        $etats = InfrastructureEtat::cases();

        return view('technician.infrastructures.show', compact(
            'infrastructure',
            'technicien',
            'interventions',
            'hasOpenIntervention',
            'openIntervention',
            'qrCodeSvg',
            'etats',
            'risk',
        ));
    }

    /**
     * Changement d'état rapide par le technicien.
     * RÈGLE MÉTIER : Possible UNIQUEMENT s'il a une intervention OUVERTE (Planifiée ou En cours) sur l'infrastructure.
     * Le commentaire est strictement obligatoire et consigné dans l'historique.
     */
    public function updateEtat(Request $request, Infrastructure $infrastructure)
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        abort_unless($technicien, 403, 'Compte technicien introuvable.');

        // 1. Vérification de périmètre
        abort_unless(
            TechnicianScopeService::canAccessInfrastructure($technicien, $infrastructure),
            403,
            "Accès refusé : vous n'intervenez pas sur cette infrastructure."
        );

        // 2. Vérification d'intervention ouverte
        abort_unless(
            TechnicianScopeService::hasOpenIntervention($technicien, $infrastructure),
            403,
            "Vous devez avoir une intervention ouverte (Planifiée ou En cours) sur cette infrastructure pour pouvoir modifier son état opérationnel."
        );

        // 3. Validation stricte du formulaire terrain
        $validated = $request->validate([
            'etat' => ['required', new Enum(InfrastructureEtat::class)],
            'commentaire' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'etat.required' => "Veuillez sélectionner le nouvel état opérationnel de l'ouvrage.",
            'commentaire.required' => 'Le commentaire explicatif est obligatoire pour justifier ce changement d’état sur le terrain.',
            'commentaire.min' => 'Le commentaire doit comporter au moins :min caractères.',
            'commentaire.max' => 'Le commentaire ne peut pas dépasser :max caractères.',
        ]);

        // Mise à jour avec déclenchement de l'Observer pour inscription automatique dans l'historique
        $infrastructure->etat_changement_commentaire = "[Terrain - {$technicien->nom}] " . $validated['commentaire'];
        $infrastructure->etat = $validated['etat'];
        $infrastructure->save();

        return redirect()
            ->route('technician.infrastructures.show', $infrastructure)
            ->with('success', "L'état de l'infrastructure {$infrastructure->nom} a été mis à jour avec succès.");
    }

    /**
     * Affichage et scan de QR Code mobile.
     */
    public function scan()
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        $myInfras = TechnicianScopeService::infrastructuresQuery($technicien)->orderBy('nom')->get(['id', 'nom', 'reference']);

        return view('technician.scan', compact('technicien', 'myInfras'));
    }

    /**
     * Traitement de la recherche ou du scan QR Code.
     */
    public function processScan(Request $request)
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        abort_unless($technicien, 403, 'Compte technicien introuvable.');

        $query = trim((string) $request->input('code'));

        if ($query === '') {
            return back()->with('error', 'Veuillez saisir ou scanner un code QR / référence.');
        }

        // Tenter de retrouver par ID direct ou référence
        $infra = null;
        if (is_numeric($query)) {
            $infra = Infrastructure::find((int) $query);
        }

        if (! $infra) {
            $infra = Infrastructure::where('reference', $query)
                ->orWhere('nom', 'like', "%{$query}%")
                ->first();
        }

        if (! $infra) {
            return back()->with('error', "Aucune infrastructure trouvée pour le code \"{$query}\".");
        }

        // Vérification des droits d'accès du technicien
        if (! TechnicianScopeService::canAccessInfrastructure($technicien, $infra)) {
            return back()->with('error', "L'ouvrage \"{$infra->nom}\" existe mais n'est rattaché à aucune de vos interventions (Accès 403).");
        }

        return redirect()->route('technician.infrastructures.show', $infra);
    }
}
