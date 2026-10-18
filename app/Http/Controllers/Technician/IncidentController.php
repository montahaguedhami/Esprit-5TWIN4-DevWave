<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Infrastructure;
use App\Models\Zone;
use App\Services\TechnicianScopeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncidentController extends Controller
{
    /**
     * Formulaire de signalement de problème terrain par le technicien.
     */
    public function create(Request $request)
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        abort_unless($technicien, 403, 'Compte technicien introuvable.');

        $selectedInfra = null;
        if ($request->filled('infrastructure_id')) {
            $selectedInfra = Infrastructure::with('zone')->find($request->input('infrastructure_id'));
        }

        // Récupérer uniquement les infrastructures du périmètre du technicien
        $infrastructures = TechnicianScopeService::infrastructuresQuery($technicien)
            ->orderBy('nom')
            ->get(['id', 'nom', 'zone_id', 'localisation']);

        $zones = Zone::orderBy('nom')->get(['id', 'nom']);

        return view('technician.incidents.create', compact('technicien', 'selectedInfra', 'infrastructures', 'zones'));
    }

    /**
     * Enregistrement du problème technique signalé par le technicien.
     */
    public function store(Request $request)
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        abort_unless($technicien, 403, 'Compte technicien introuvable.');

        $validated = $request->validate([
            'titre' => 'required|string|min:3|max:255',
            'type' => ['required', Rule::in(['fuite', 'pollution', 'panne', 'rupture', 'contamination'])],
            'gravite' => ['required', Rule::in(['faible', 'moyenne', 'elevee', 'critique'])],
            'description' => 'required|string|min:5|max:5000',
            'localisation' => 'required|string|max:255',
            'infrastructure_id' => 'nullable|exists:infrastructures,id',
            'zone_id' => 'nullable|exists:zones,id',
        ], [
            'titre.required' => 'Le titre du problème est obligatoire.',
            'description.required' => 'La description détaillée de l’anomalie est obligatoire.',
            'localisation.required' => 'La localisation de l’incident est obligatoire.',
            'type.in' => 'Le type d’incident sélectionné est invalide.',
            'gravite.in' => 'Le niveau de gravité est invalide.',
        ]);

        // Auto-compléter la zone si l'infrastructure est spécifiée et sans zone
        if (! empty($validated['infrastructure_id']) && empty($validated['zone_id'])) {
            $infra = Infrastructure::find($validated['infrastructure_id']);
            if ($infra && $infra->zone_id) {
                $validated['zone_id'] = $infra->zone_id;
            }
        }

        $user = \App\Models\User::where('email', session('user.email'))->first()
            ?? \App\Models\User::firstOrCreate(
                ['email' => session('user.email', 'amira@aquasecure.tn')],
                ['name' => $technicien->nom, 'password' => bcrypt('password')]
            );

        $incident = Incident::create([
            'user_id' => $user->id,
            'titre' => $validated['titre'],
            'type' => $validated['type'],
            'gravite' => $validated['gravite'],
            'description' => "[Signalement Terrain — {$technicien->nom}]\n" . $validated['description'],
            'date_signalement' => now(),
            'statut' => 'signale',
            'localisation' => $validated['localisation'],
            'zone_id' => $validated['zone_id'] ?? null,
            'infrastructure_id' => $validated['infrastructure_id'] ?? null,
        ]);

        if (! empty($validated['infrastructure_id'])) {
            return redirect()
                ->route('technician.infrastructures.show', $validated['infrastructure_id'])
                ->with('success', "Le signalement d'anomalie #{$incident->id} a bien été transmis et rattaché à l'ouvrage.");
        }

        return redirect()
            ->route('technician.dashboard')
            ->with('success', "Le signalement d'anomalie #{$incident->id} a été enregistré avec succès.");
    }
}
