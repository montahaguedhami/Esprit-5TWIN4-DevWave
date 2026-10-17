@extends('layouts.admin')

@section('title', 'Consultation de l’infrastructure')
@section('page-title', 'Infrastructures')

@section('admin-content')
<div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6">
    <a href="{{ route('admin.infrastructures.index') }}" class="text-sm font-semibold text-violet-700 hover:underline">← Retour aux infrastructures</a>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold text-violet-600">Fiche consultative</p><h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $infrastructure->nom }}</h1>
        <p class="mt-2 text-slate-600">{{ $infrastructure->reference ?? 'Sans référence' }} · {{ $infrastructure->type?->label() ?? 'Type non précisé' }}</p>
        <dl class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">Zone</dt><dd class="mt-1 font-semibold"><a class="text-violet-700 hover:underline" href="{{ route('admin.zones.show', $infrastructure->zone) }}">{{ $infrastructure->zone?->nom ?? '—' }}</a></dd></div>
            <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">État opérationnel</dt><dd class="mt-1 font-semibold text-slate-900">{{ $infrastructure->etat?->label() ?? $infrastructure->etat ?? '—' }}</dd></div>
            <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">Criticité</dt><dd class="mt-1 font-semibold text-slate-900">{{ $infrastructure->niveau_criticite?->label() ?? '—' }}</dd></div>
            <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">Installation</dt><dd class="mt-1 font-semibold text-slate-900">{{ $infrastructure->date_installation?->format('d/m/Y') ?? '—' }}</dd></div>
            <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">Capacité</dt><dd class="mt-1 font-semibold text-slate-900">{{ $infrastructure->capacite ? number_format($infrastructure->capacite, 0, ',', ' ').' '.($infrastructure->unite_capacite ?? '') : '—' }}</dd></div>
            <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">Fabricant</dt><dd class="mt-1 font-semibold text-slate-900">{{ $infrastructure->fabricant ?? '—' }}</dd></div>
            <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">Dernière maintenance</dt><dd class="mt-1 font-semibold text-slate-900">{{ $infrastructure->date_derniere_maintenance?->format('d/m/Y') ?? '—' }}</dd></div>
            <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs text-slate-500">Prochaine maintenance</dt><dd class="mt-1 font-semibold text-slate-900">{{ $infrastructure->date_prochaine_maintenance?->format('d/m/Y') ?? '—' }}</dd></div>
        </dl>
        @if($infrastructure->description)<p class="mt-5 text-sm leading-6 text-slate-600">{{ $infrastructure->description }}</p>@endif
    </section>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-100 px-5 py-4"><h2 class="font-bold text-slate-900">Historique des changements d’état</h2></div><div class="divide-y divide-slate-100">@forelse($infrastructure->historiques as $item)<div class="px-5 py-4"><p class="font-semibold text-slate-800">{{ $item->ancien_etat?->label() ?? '—' }} → {{ $item->nouvel_etat?->label() ?? '—' }}</p><p class="mt-1 text-xs text-slate-500">{{ $item->created_at?->format('d/m/Y H:i') }} · {{ $item->user_name ?? 'Système' }}</p>@if($item->commentaire)<p class="mt-2 text-sm text-slate-600">{{ $item->commentaire }}</p>@endif</div>@empty<p class="px-5 py-8 text-center text-sm text-slate-500">Aucun changement enregistré.</p>@endforelse</div></section>
    <div class="grid gap-6 lg:grid-cols-3">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-100 px-5 py-4"><h2 class="font-bold text-slate-900">Alertes actives</h2></div><div class="divide-y divide-slate-100">@forelse($infrastructure->alertes as $alerte)<div class="px-5 py-3"><p class="font-semibold text-slate-800">{{ $alerte->type }}</p><p class="mt-1 text-xs text-slate-500">{{ $alerte->created_at?->format('d/m/Y H:i') }} · {{ $alerte->message }}</p></div>@empty<p class="px-5 py-6 text-sm text-slate-500">Aucune alerte.</p>@endforelse</div></section>
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-100 px-5 py-4"><h2 class="font-bold text-slate-900">Interventions</h2></div><div class="divide-y divide-slate-100">@forelse($infrastructure->interventions as $intervention)<div class="px-5 py-3"><p class="font-semibold text-slate-800">{{ $intervention->description }}</p><p class="mt-1 text-xs text-slate-500">{{ $intervention->date?->format('d/m/Y') }} · {{ $intervention->statut }} · {{ $intervention->technicien?->nom ?? 'Technicien non renseigné' }}</p></div>@empty<p class="px-5 py-6 text-sm text-slate-500">Aucune intervention.</p>@endforelse</div></section>
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-100 px-5 py-4"><h2 class="font-bold text-slate-900">Incidents ouverts</h2></div><div class="divide-y divide-slate-100">@forelse($infrastructure->incidents as $incident)<div class="px-5 py-3"><p class="font-semibold text-slate-800">{{ $incident->titre }}</p><p class="mt-1 text-xs text-slate-500">{{ $incident->date_signalement?->format('d/m/Y H:i') }} · {{ $incident->statut }}</p></div>@empty<p class="px-5 py-6 text-sm text-slate-500">Aucun incident ouvert.</p>@endforelse</div></section>
    </div>
</div>
@endsection
