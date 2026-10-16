<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Financement;
use App\Models\Incident;
use App\Models\Intervention;
use App\Models\MesureQualite;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function budget(): View
    {
        $projets = Projet::with('financements')->orderBy('nom')->get();
        $financements = Financement::with('projet')->latest('date_financement')->get();
        $totalBudget = (float) $projets->sum('budget');
        $totalFinance = (float) $financements->sum('montant');

        return view('admin.consultation.budget', compact('projets', 'financements', 'totalBudget', 'totalFinance'));
    }

    public function projets(Request $request): View
    {
        $projets = Projet::with('financements')->when($request->filled('q'), function ($query) use ($request): void {
            $query->where('nom', 'like', '%' . $request->string('q') . '%');
        })->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.consultation.projets', compact('projets'));
    }

    public function qualite(Request $request): View
    {
        $mesures = MesureQualite::with('pointMesure')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = (string) $request->string('q');
                $query->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('pointMesure', fn ($point) => $point->where('nom', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%"));
            })
            ->latest('date_mesure')->paginate(25)->withQueryString();
        $totalMesures = MesureQualite::count();
        $totalPoints = \App\Models\PointMesure::count();

        return view('admin.consultation.qualite', compact('mesures', 'totalMesures', 'totalPoints'));
    }

    public function incidents(): View
    {
        $incidents = Incident::with(['zone', 'infrastructure', 'user'])
            ->latest('date_signalement')->paginate(25);

        return view('admin.consultation.incidents', compact('incidents'));
    }

    public function interventions(): View
    {
        $interventions = Intervention::with(['technicien', 'infrastructure.zone'])
            ->orderByDesc('date')->paginate(25);

        return view('admin.consultation.interventions', compact('interventions'));
    }
}
