<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncidentController extends Controller
{
    public function managerIndex()
    {
        $incidents = Incident::with('user')->withCount('actions')->latest()->paginate(10);
        return view('manager.incidents.index', compact('incidents'));
    }

    public function managerShow(Incident $incident)
    {
        $incident->load(['user', 'actions']);
        return view('manager.incidents.show', compact('incident'));
    }

    public function index(Request $request)
    {
        $incidents = Incident::where('user_id', $request->attributes->get('incident_user_id'))->latest()->paginate(10);
        return view('front.incidents.index', compact('incidents'));
    }

    public function create()
    {
        return view('front.incidents.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        $validated['user_id'] = $request->attributes->get('incident_user_id');
        $validated['statut'] = 'signale';

        Incident::create($validated);

        return redirect()->route('incidents.index')->with('success', 'Incident créé');
    }

    public function show(Request $request, Incident $incident)
    {
        $this->authorizeOwner($request, $incident);
        $incident->load('actions');
        return view('front.incidents.show', compact('incident'));
    }

    public function edit(Request $request, Incident $incident)
    {
        $this->authorizeOwner($request, $incident);
        return view('front.incidents.edit', compact('incident'));
    }

    public function update(Request $request, Incident $incident)
    {
        $this->authorizeOwner($request, $incident);
        $validated = $this->validatedData($request, true);

        $incident->update($validated);
        return redirect()->route('incidents.show', $incident)->with('success', 'Incident mis à jour');
    }

    public function destroy(Request $request, Incident $incident)
    {
        $this->authorizeOwner($request, $incident);
        $incident->delete();
        return redirect()->route('incidents.index')->with('success', 'Incident supprimé');
    }

    private function validatedData(Request $request, bool $updating = false): array
    {
        $rules = [
            'titre' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'type' => ['required', Rule::in(['fuite', 'pollution', 'panne', 'rupture', 'contamination'])],
            'gravite' => ['required', Rule::in(['faible', 'moyenne', 'elevee', 'critique'])],
            'date_signalement' => 'required|date',
            'localisation' => 'required|string|max:255',
        ];

        if ($updating) {
            $rules['statut'] = ['required', Rule::in(['signale', 'en_cours', 'resolu', 'cloture'])];
        }

        return $request->validate($rules, [
            'required' => 'Le champ :attribute est obligatoire.',
            'string' => 'Le champ :attribute doit etre un texte.',
            'max' => 'Le champ :attribute ne doit pas depasser :max caracteres.',
            'in' => 'La valeur du champ :attribute est invalide.',
            'date' => 'Le champ :attribute doit contenir une date valide.',
        ], [
            'titre' => 'titre',
            'description' => 'description',
            'type' => "type d'incident",
            'gravite' => 'gravite',
            'statut' => 'statut',
            'date_signalement' => 'date de signalement',
            'localisation' => 'localisation',
        ]);
    }

    private function authorizeOwner(Request $request, Incident $incident): void
    {
        abort_unless((int) $incident->user_id === (int) $request->attributes->get('incident_user_id'), 404);
    }
}
