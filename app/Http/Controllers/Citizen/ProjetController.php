<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use App\Models\Role;
use App\Services\Recommandation\EnregistreurConsultation;
use Illuminate\Http\Request;

class ProjetController extends Controller
{
    public function __construct(
        private readonly EnregistreurConsultation $consultations,
    ) {
    }

    /**
     * Display a listing of the resource (citizen view — read only).
     */
    public function index(Request $request)
    {
        if (! \App\Models\Role::sessionMatches(\App\Models\Role::CITOYEN)) {
            return redirect()->route('auth.login');
        }

        $query = Projet::with('financements');

        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $projets = $query->latest()->paginate(12);

        // Favourites of the connected citizen, fetched once for the whole
        // page so each card does not run its own EXISTS query.
        $favoris = $request->user()
            ? $request->user()->favoris()->pluck('projet_id')->flip()
            : collect();

        return view('citizen.projets.index', compact('projets', 'favoris'));
    }

    /**
     * Display the specified resource (citizen view — read only).
     */
    public function show(Request $request, Projet $projet)
    {
        if (! \App\Models\Role::sessionMatches(\App\Models\Role::CITOYEN)) {
            return redirect()->route('auth.login');
        }

        // A LISTING is not a view: only the detail page records a consultation.
        //
        // FIRE-AND-FORGET, guaranteed HERE rather than delegated: the page
        // must render even if the recorder itself misbehaves. A missing
        // bookmark in a ranking is never worth a 500, so the call is wrapped
        // independently of what the recorder does internally.
        try {
            $this->consultations->enregistrer($request, (int) $projet->id);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Consultation non enregistrée (page projet intacte).', [
                'projet_id' => $projet->id,
                'erreur'    => $e->getMessage(),
            ]);
        }

        $projet->load('financements');

        return view('citizen.projets.show', [
            'projet'    => $projet,
            'estFavori' => $projet->estFavoriDe($request->user()),
        ]);
    }
}
