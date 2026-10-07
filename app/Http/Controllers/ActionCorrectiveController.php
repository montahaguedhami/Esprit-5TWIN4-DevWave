<?php

namespace App\Http\Controllers;

use App\Models\ActionCorrective;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActionCorrectiveController extends Controller
{
    public function index(Incident $incident)
    {
        $actions = $incident->actions()->latest()->paginate(10);

        return view('manager.actions.index', compact('incident', 'actions'));
    }

    public function create(Incident $incident)
    {
        return view('manager.actions.create', compact('incident'));
    }

    public function store(Request $request, Incident $incident)
    {
        $validated = $this->validatedData($request);

        $incident->actions()->create($validated);

        return redirect()->route('manager.incidents.show', $incident)->with('success', 'Action corrective ajoutée');
    }

    public function show(ActionCorrective $action)
    {
        $action->load('incident');

        return view('manager.actions.show', compact('action'));
    }

    public function edit(ActionCorrective $action)
    {
        return view('manager.actions.edit', compact('action'));
    }

    public function update(Request $request, ActionCorrective $action)
    {
        $validated = $this->validatedData($request);

        $action->update($validated);
        return redirect()->route('manager.incidents.show', $action->incident_id)->with('success', 'Action mise à jour');
    }

    public function destroy(ActionCorrective $action)
    {
        $incidentId = $action->incident_id;
        $action->delete();
        return redirect()->route('manager.incidents.show', $incidentId)->with('success', 'Action supprimée');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'responsable' => 'required|string|max:255',
            'date_prevue' => 'required|date',
            'date_realisation' => 'required|date',
            'statut' => ['required', Rule::in(['a_faire', 'en_cours', 'terminee'])],
            'resultat' => 'required|string|max:5000',
        ], [
            'required' => 'Le champ :attribute est obligatoire.',
            'string' => 'Le champ :attribute doit etre un texte.',
            'max' => 'Le champ :attribute ne doit pas depasser :max caracteres.',
            'in' => 'La valeur du champ :attribute est invalide.',
            'date' => 'Le champ :attribute doit contenir une date valide.',
        ], [
            'titre' => 'titre',
            'description' => 'description',
            'responsable' => 'responsable',
            'date_prevue' => 'date prevue',
            'date_realisation' => 'date de realisation',
            'statut' => 'statut',
            'resultat' => 'resultat',
        ]);
    }
}
