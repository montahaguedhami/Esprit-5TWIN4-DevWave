<?php

namespace App\Http\Controllers;

use App\Models\ActionCorrective;
use App\Models\Incident;
use App\Notifications\ActionCorrectiveCreated;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActionCorrectiveController extends Controller
{
    public function index(Request $request, Incident $incident)
    {
        $query = $incident->actions();

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('responsable', 'like', "%{$search}%");
            });
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '') {
            $query->where('statut', $status);
        }

        $responsable = trim((string) $request->query('responsable', ''));
        if ($responsable !== '') {
            $query->where('responsable', 'like', "%{$responsable}%");
        }

        $from = $request->query('date_from');
        if ($from) {
            $query->whereDate('date_prevue', '>=', $from);
        }

        $to = $request->query('date_to');
        if ($to) {
            $query->whereDate('date_prevue', '<=', $to);
        }

        $sortBy = in_array($request->query('sort_by'), ['created_at', 'date_prevue', 'date_realisation', 'statut', 'titre'], true)
            ? $request->query('sort_by')
            : 'created_at';
        $sortDir = strtolower((string) $request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $actions = $query->orderBy($sortBy, $sortDir)->paginate(10)->appends($request->query());

        return view('manager.actions.index', compact('incident', 'actions'));
    }

    public function create(Incident $incident)
    {
        return view('manager.actions.create', compact('incident'));
    }

    public function store(Request $request, Incident $incident)
    {
        $validated = $this->validatedData($request);

        $action = $incident->actions()->create($validated);

        if ($incident->user) {
            $incident->user->notify(new ActionCorrectiveCreated($incident, $action));
        }

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
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'responsable' => ['required', 'string', 'max:255'],
            'date_prevue' => ['required', 'date'],
            'date_realisation' => ['required', 'date', 'after_or_equal:date_prevue'],
            'statut' => ['required', Rule::in(['a_faire', 'en_cours', 'terminee'])],
            'resultat' => ['required', 'string', 'max:5000'],
        ], [
            'required' => 'Le champ :attribute est obligatoire.',
            'string' => 'Le champ :attribute doit etre un texte.',
            'titre.max' => 'Le champ :attribute ne doit pas depasser :max caracteres.',
            'description.max' => 'Le champ :attribute ne doit pas depasser :max caracteres.',
            'responsable.max' => 'Le champ :attribute ne doit pas depasser :max caracteres.',
            'resultat.max' => 'Le champ :attribute ne doit pas depasser :max caracteres.',
            'in' => 'La valeur du champ :attribute est invalide.',
            'date' => 'Le champ :attribute doit contenir une date valide.',
            'after_or_equal' => 'La date de realisation doit etre egale ou posterieure a la date prevue.',
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
