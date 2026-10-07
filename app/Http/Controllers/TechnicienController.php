<?php

namespace App\Http\Controllers;

use App\Http\Requests\TechnicienRequest;
use App\Models\Technicien;
use Illuminate\Http\Request;

class TechnicienController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $techniciens = Technicien::query()
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
        return view('manager.techniciens.create', ['technicien' => new Technicien()]);
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
        $interventions = $technicien->interventions()->latest('date')->get();

        return view('manager.techniciens.show', compact('technicien', 'interventions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Technicien $technicien)
    {
        return view('manager.techniciens.edit', compact('technicien'));
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
}
