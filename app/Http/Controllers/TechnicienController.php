<?php

namespace App\Http\Controllers;

use App\Http\Requests\TechnicienRequest;
use App\Models\Role;
use App\Models\Technicien;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TechnicienController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $techniciens = Technicien::query()
            ->with('user')
            ->withCount('interventions')
            ->when($request->filled('q'), fn ($query) => $query->where('nom', 'like', '%'.$request->q.'%'))
            ->when($request->filled('specialite'), fn ($query) => $query->where('specialite', $request->specialite))
            ->when($request->filled('disponibilite'), fn ($query) => $query->where('disponibilite', $request->disponibilite))
            ->orderBy('nom')
            ->paginate(10)
            ->withQueryString();

        return view('manager.techniciens.index', compact('techniciens'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('manager.techniciens.create', [
            'technicien' => new Technicien(),
            'comptes' => $this->comptesDisponibles(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TechnicienRequest $request)
    {
        $technicien = Technicien::create($request->validated());

        return redirect()
            ->route('manager.techniciens.show', $technicien)
            ->with('success', 'Le technicien « '.$technicien->nom.' » a été ajouté.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Technicien $technicien)
    {
        $technicien->load('user');
        $interventions = $technicien->interventions()->latest('date')->get();

        return view('manager.techniciens.show', compact('technicien', 'interventions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Technicien $technicien)
    {
        return view('manager.techniciens.edit', [
            'technicien' => $technicien,
            'comptes' => $this->comptesDisponibles($technicien),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TechnicienRequest $request, Technicien $technicien)
    {
        $technicien->update($request->validated());

        return redirect()
            ->route('manager.techniciens.show', $technicien)
            ->with('success', 'Le technicien « '.$technicien->nom.' » a été modifié.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Technicien $technicien)
    {
        $nombre = $technicien->interventions()->count();
        $technicien->delete();

        return redirect()
            ->route('manager.techniciens.index')
            ->with('success', 'Le technicien « '.$technicien->nom.' » et ses '.$nombre.' intervention(s) ont été supprimés.');
    }

    /**
     * Comptes au rôle technicien non encore rattachés (plus celui de la fiche en cours d'édition).
     */
    private function comptesDisponibles(?Technicien $technicien = null): Collection
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('slug', Role::TECHNICIEN))
            ->where(function ($query) use ($technicien) {
                $query->whereDoesntHave('technicien');
                if ($technicien?->user_id) {
                    $query->orWhere('id', $technicien->user_id);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
