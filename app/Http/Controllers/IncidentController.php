<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class IncidentController extends Controller
{
    public function managerIndex(Request $request)
    {
        $query = Incident::query()->with('user')->withCount('actions');
        $this->applyFilters($query, $request);

        $sortBy = $this->allowedSortColumn($request->query('sort_by'), 'created_at');
        $sortDir = strtolower((string) $request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $incidents = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(10)
            ->appends($request->query());

        return view('manager.incidents.index', compact('incidents'));
    }

    public function managerShow(Incident $incident)
    {
        $incident->load(['user', 'actions', 'zone', 'infrastructure']);
        return view('manager.incidents.show', compact('incident'));
    }

    public function index(Request $request)
    {
        $query = Incident::query()
            ->where('user_id', (int) $request->attributes->get('incident_user_id'))
            ->with(['user', 'zone', 'infrastructure', 'actions']);

        $this->applyFilters($query, $request);

        $sortBy = $this->allowedSortColumn($request->query('sort_by'), 'created_at');
        $sortDir = strtolower((string) $request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $incidents = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(10)
            ->appends($request->query());

        return view('front.incidents.index', compact('incidents'));
    }

    public function create()
    {
        $zones = \App\Models\Zone::orderBy('nom')->get(['id', 'nom']);
        $infrastructures = \App\Models\Infrastructure::orderBy('nom')->get(['id', 'nom', 'zone_id']);

        return view('front.incidents.create', compact('zones', 'infrastructures'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        $validated['user_id'] = $request->attributes->get('incident_user_id');
        $validated['statut'] = 'signale';

        if ($request->hasFile('preuve')) {
            $validated['preuve_path'] = $this->storeAttachment($request, $validated['titre'] ?? 'incident');
        }

        Incident::create($validated);

        return redirect()->route('incidents.index')->with('success', 'Incident créé');
    }

    public function show(Request $request, Incident $incident)
    {
        $this->authorizeOwner($request, $incident);
        $incident->load(['actions', 'zone', 'infrastructure']);
        return view('front.incidents.show', compact('incident'));
    }

    public function edit(Request $request, Incident $incident)
    {
        $this->authorizeOwner($request, $incident);
        $zones = \App\Models\Zone::orderBy('nom')->get(['id', 'nom']);
        $infrastructures = \App\Models\Infrastructure::orderBy('nom')->get(['id', 'nom', 'zone_id']);

        return view('front.incidents.edit', compact('incident', 'zones', 'infrastructures'));
    }

    public function update(Request $request, Incident $incident)
    {
        $this->authorizeOwner($request, $incident);
        $validated = $this->validatedData($request);

        if ($request->hasFile('preuve')) {
            $validated['preuve_path'] = $this->storeAttachment($request, $validated['titre'] ?? $incident->titre, $incident->preuve_path);
        }

        $incident->update($validated);
        return redirect()->route('incidents.show', $incident)->with('success', 'Incident mis à jour');
    }

    public function destroy(Request $request, Incident $incident)
    {
        $this->authorizeOwner($request, $incident);
        if ($incident->preuve_path && Storage::disk('local')->exists($incident->preuve_path)) {
            Storage::disk('local')->delete($incident->preuve_path);
        }
        $incident->delete();
        return redirect()->route('incidents.index')->with('success', 'Incident supprimé');
    }

    public function attachment(Request $request, Incident $incident)
    {
        $sessionRole = $request->session()->get('user.role');

        if ($sessionRole === 'citoyen') {
            abort_unless((int) $incident->user_id === (int) $request->attributes->get('incident_user_id'), 403);
        } elseif (! in_array($sessionRole, ['manager', 'administrateur'], true)) {
            abort(403);
        }

        if (! $incident->preuve_path || ! Storage::disk('local')->exists($incident->preuve_path)) {
            abort(404);
        }

        $path = Storage::disk('local')->path($incident->preuve_path);
        $mime = mime_content_type($path) ?: 'application/octet-stream';

        if (str_starts_with($mime, 'image/')) {
            return response()->file($path, ['Content-Type' => $mime]);
        }

        return response()->download($path, basename($incident->preuve_path));
    }

    private function validatedData(Request $request): array
    {
        $rules = [
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'type' => ['required', Rule::in(['fuite', 'pollution', 'panne', 'rupture', 'contamination'])],
            'gravite' => ['required', Rule::in(['faible', 'moyenne', 'elevee', 'critique'])],
            'date_signalement' => ['required', 'date', 'before_or_equal:now'],
            'localisation' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'zone_id' => ['nullable', 'integer', 'exists:zones,id'],
            'infrastructure_id' => ['nullable', 'integer', 'exists:infrastructures,id'],
            'preuve' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ];

        $validated = $request->validate($rules, [
            'required' => 'Le champ :attribute est obligatoire.',
            'string' => 'Le champ :attribute doit etre un texte.',
            'titre.max' => 'Le champ :attribute ne doit pas depasser :max caracteres.',
            'description.max' => 'Le champ :attribute ne doit pas depasser :max caracteres.',
            'localisation.max' => 'Le champ :attribute ne doit pas depasser :max caracteres.',
            'preuve.max' => 'Le fichier joint ne doit pas depasser :max Ko.',
            'in' => 'La valeur du champ :attribute est invalide.',
            'date' => 'Le champ :attribute doit contenir une date valide.',
            'before_or_equal' => 'La date de signalement ne peut pas etre dans le futur.',
            'integer' => 'La valeur du champ :attribute est invalide.',
            'exists' => 'La valeur du champ :attribute est invalide.',
            'file' => 'Le fichier joint est invalide.',
            'mimes' => 'Le fichier doit être une image JPG, JPEG, PNG, WEBP ou un PDF.',
        ], [
            'titre' => 'titre',
            'description' => 'description',
            'type' => "type d'incident",
            'gravite' => 'gravite',
            'date_signalement' => 'date de signalement',
            'localisation' => 'adresse',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'preuve' => 'preuve jointe',
        ]);

        unset($validated['preuve']);

        return $validated;
    }

    private function applyFilters($query, Request $request): void
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(['fuite', 'pollution', 'panne', 'rupture', 'contamination'])],
            'gravite' => ['nullable', Rule::in(['faible', 'moyenne', 'elevee', 'critique'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('localisation', 'like', "%{$search}%");
            });
        }

        $type = $filters['type'] ?? '';
        if ($type !== '') {
            $query->where('type', $type);
        }

        $gravite = $filters['gravite'] ?? '';
        if ($gravite !== '') {
            $query->where('gravite', $gravite);
        }

        $from = $filters['date_from'] ?? null;
        if ($from) {
            $query->whereDate('date_signalement', '>=', $from);
        }

        $to = $filters['date_to'] ?? null;
        if ($to) {
            $query->whereDate('date_signalement', '<=', $to);
        }
    }

    private function allowedSortColumn(?string $sortBy, string $default): string
    {
        $allowed = ['created_at', 'date_signalement', 'titre', 'type', 'gravite'];

        return in_array($sortBy, $allowed, true) ? $sortBy : $default;
    }

    private function storeAttachment(Request $request, string $incidentTitle, ?string $existingPath = null): string
    {
        $file = $request->file('preuve');
        $mime = $file->getMimeType();
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];

        if (! in_array($mime, $allowedMime, true)) {
            abort(422, 'Type de fichier non autorisé.');
        }

        if ($existingPath && Storage::disk('local')->exists($existingPath)) {
            Storage::disk('local')->delete($existingPath);
        }

        $safeName = preg_replace('/[^A-Za-z0-9._-]+/', '-', $incidentTitle);
        $safeName = trim($safeName, '-_ .');
        $safeName = $safeName !== '' ? $safeName : 'incident';

        return $file->storeAs('incidents', $safeName . '-' . now()->timestamp . '.' . strtolower($file->getClientOriginalExtension()), 'local');
    }

    private function authorizeOwner(Request $request, Incident $incident): void
    {
        abort_unless((int) $incident->user_id === (int) $request->attributes->get('incident_user_id'), 404);
    }
}
