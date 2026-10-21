<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBeachHistoricalAdvisoryPointRequest;
use App\Models\Beach;
use App\Models\BeachBathingStatus;
use App\Models\BeachHistoricalAdvisoryPoint;
use App\Models\Governorate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BeachManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'governorate_id' => ['nullable', 'integer', 'exists:tunibeach_governorates,id'],
        ]);

        $selectedGovernorate = isset($filters['governorate_id'])
            ? Governorate::find($filters['governorate_id'])
            : null;

        return view('manager.beaches.index', [
            'allGovernorates' => Governorate::orderBy('name')->get(),
            'search' => trim($filters['q'] ?? ''),
            'selectedGovernorateId' => $selectedGovernorate?->id,
            'advisoryPoints' => BeachHistoricalAdvisoryPoint::query()
                ->when(($filters['q'] ?? '') !== '', fn ($query) => $query->where('point_name', 'like', '%'.trim($filters['q']).'%'))
                ->when($selectedGovernorate, fn ($query) => $query->where('governorate_name', $selectedGovernorate->name))
                ->orderBy('governorate_name')
                ->orderBy('point_name')
                ->paginate(20, ['*'], 'advisory_page')
                ->withQueryString(),
        ]);
    }

    public function storeHistoricalPoint(StoreBeachHistoricalAdvisoryPointRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $governorate = Governorate::findOrFail($data['governorate_id']);
        $duplicate = BeachHistoricalAdvisoryPoint::query()
            ->whereDate('reported_at', $data['reported_at'])
            ->where('governorate_name', $governorate->name)
            ->where('point_name', trim($data['point_name']))
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors(['point_name' => 'Ce point existe déjà pour cette date et ce gouvernorat.']);
        }

        BeachHistoricalAdvisoryPoint::create([
            'reported_at' => $data['reported_at'],
            'governorate_name' => $governorate->name,
            'point_name' => trim($data['point_name']),
            'reported_status' => $data['reported_status'],
            'source_name' => trim($data['source_name']),
            'source_url' => trim($data['source_url']),
        ]);

        return redirect()->route('manager.beaches.index')
            ->with('success', 'Le point a été ajouté au relevé partagé. Les points restreints apparaissent dans la recherche citoyenne.');
    }

    public function edit(Beach $beach): View
    {
        $beach->load(['governorate', 'bathingStatuses' => fn ($q) => $q->latest('valid_from')]);
        $governorateSlug = Str::slug($beach->governorate->name);

        return view('manager.beaches.edit', [
            'beach' => $beach,
            'governorates' => Governorate::orderBy('name')->get(),
            'historicalPoints' => BeachHistoricalAdvisoryPoint::query()
                ->whereDate('reported_at', '2026-06-20')
                ->get()
                ->filter(fn (BeachHistoricalAdvisoryPoint $point): bool => Str::slug($point->governorate_name) === $governorateSlug)
                ->values(),
        ]);
    }

    public function editHistoricalPoint(BeachHistoricalAdvisoryPoint $point): View
    {
        return view('manager.beaches.advisory-edit', [
            'point' => $point,
            'governorates' => Governorate::orderBy('name')->get(),
        ]);
    }

    public function updateHistoricalPoint(Request $request, BeachHistoricalAdvisoryPoint $point): RedirectResponse
    {
        $data = $request->validate([
            'point_name' => ['required', 'string', 'min:2', 'max:255'],
            'governorate_id' => ['required', 'integer', 'exists:tunibeach_governorates,id'],
            'reported_status' => ['required', 'in:restricted,unknown'],
            'reported_at' => ['required', 'date', 'before_or_equal:today'],
            'source_name' => ['required', 'string', 'min:2', 'max:180'],
            'source_url' => ['required', 'url', 'max:2000'],
        ]);

        $governorate = Governorate::findOrFail($data['governorate_id']);
        $pointName = trim($data['point_name']);
        $duplicate = BeachHistoricalAdvisoryPoint::query()
            ->whereDate('reported_at', $data['reported_at'])
            ->where('governorate_name', $governorate->name)
            ->where('point_name', $pointName)
            ->whereKeyNot($point->getKey())
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors(['point_name' => 'Un signalement portant ce nom existe déjà pour cette date et ce gouvernorat.']);
        }

        $point->update([
            'point_name' => $pointName,
            'governorate_name' => $governorate->name,
            'reported_status' => $data['reported_status'],
            'reported_at' => $data['reported_at'],
            'source_name' => trim($data['source_name']),
            'source_url' => trim($data['source_url']),
        ]);

        return redirect()->route('manager.beaches.index', $request->only('q', 'governorate_id'))
            ->with('success', 'Le signalement a été modifié. La liste citoyenne est synchronisée avec ces données.');
    }

    public function update(Request $request, Beach $beach): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'governorate_id' => ['required', 'exists:tunibeach_governorates,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'coordinates_verified' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
        ]);
        $data['coordinates_verified'] = $request->boolean('coordinates_verified') && ($data['latitude'] ?? null) !== null && ($data['longitude'] ?? null) !== null;
        $data['is_published'] = $request->boolean('is_published');
        $beach->update($data);
        return redirect()->route('manager.beaches.edit', $beach)->with(
            'success',
            'Fiche mise à jour dans le catalogue partagé. La liste du gestionnaire et celle du citoyen sont alimentées par les mêmes données.',
        );
    }

    public function storeStatus(Request $request, Beach $beach): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:safe,restricted,prohibited'],
            'source_name' => ['required', 'string', 'max:180'],
            'source_url' => ['nullable', 'url', 'max:2000'],
            'source_reference' => ['nullable', 'string', 'max:120'],
            'issued_at' => ['nullable', 'date'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after:valid_from'],
            'is_verified' => ['sometimes', 'boolean'],
        ]);
        $data['is_verified'] = $request->boolean('is_verified');
        $data['verified_at'] = $data['is_verified'] ? now() : null;
        $beach->bathingStatuses()->create($data);

        return redirect()->route('manager.beaches.edit', $beach)->with('success', 'La nouvelle décision de baignade a été enregistrée.');
    }
}
