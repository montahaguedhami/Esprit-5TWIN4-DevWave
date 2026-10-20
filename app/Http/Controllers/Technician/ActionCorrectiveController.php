<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\ActionCorrective;
use App\Services\ActionCorrectiveNotificationService;
use App\Services\TechnicianScopeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActionCorrectiveController extends Controller
{
    public function index()
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        abort_unless($technicien, 403, 'Compte technicien introuvable.');

        $actions = ActionCorrective::query()
            ->where('technicien_id', $technicien->id)
            ->with(['incident.user', 'technicien'])
            ->latest()
            ->paginate(15);

        return view('technician.incidents.index', compact('technicien', 'actions'));
    }

    public function updateStatus(Request $request, ActionCorrective $action)
    {
        $technicien = TechnicianScopeService::currentTechnicien();
        abort_unless($technicien, 403, 'Compte technicien introuvable.');
        abort_unless((int) $action->technicien_id === (int) $technicien->id, 403);

        $validated = $request->validate([
            'statut' => ['required', Rule::in(['a_faire', 'en_cours', 'terminee'])],
        ], [
            'required' => 'Sélectionne un statut.',
            'in' => 'Le statut sélectionné est invalide.',
        ]);

        $statusChanged = $action->statut !== $validated['statut'];
        $updates = [
            'statut' => $validated['statut'],
            'date_realisation' => $validated['statut'] === 'terminee' ? now() : null,
        ];

        $action->update($updates);

        if ($statusChanged) {
            $action->load(['incident.user', 'technicien']);
            app(ActionCorrectiveNotificationService::class)
                ->notifyCitizen($action->incident, $action, true);
        }

        return redirect()->route('technician.incidents.index')->with('success', 'Statut de l’action mis à jour.');
    }
}
