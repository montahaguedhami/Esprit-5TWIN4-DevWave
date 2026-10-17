@extends('layouts.manager')

@section('title', 'Tableau de bord — Technicien')

@php
    use App\Data\PlaceholderData;
    use App\Services\TechnicianScopeService;
    $user          = session('user', ['name' => 'Amira Ben Ali', 'role' => 'technician']);
    $technicien    = TechnicianScopeService::currentTechnicien();
    $dbZones       = $technicien ? TechnicianScopeService::zonesQuery($technicien)->take(4)->get() : collect();
    $equipment     = PlaceholderData::technicianEquipment();
    $zones         = PlaceholderData::technicianZones();

    // Interventions réelles du technicien connecté (en cours, puis planifiées, plus celles du jour)
    $mesInterventions = $technicien
        ? \App\Models\Intervention::with('infrastructure.zone')
            ->where('technicien_id', $technicien->id)
            ->where(fn ($q) => $q->whereIn('statut', ['Planifiée', 'En cours'])->orWhereDate('date', today()))
            ->orderByRaw("CASE statut WHEN 'En cours' THEN 0 WHEN 'Planifiée' THEN 1 ELSE 2 END")
            ->orderBy('date')
            ->get()
        : collect();

    // Priorité : en retard ou équipement critique → urgent ; criticité moyenne → moyen ; sinon faible
    $priorite = function ($i) {
        $actif = in_array($i->statut, ['Planifiée', 'En cours'], true);
        if (($actif && $i->date->lt(today())) || $i->infrastructure?->niveau_criticite?->value === 'critique') {
            return 'high';
        }

        return $i->infrastructure?->niveau_criticite?->value === 'faible' ? 'low' : 'medium';
    };

    $interventions = $mesInterventions->take(6)->map(fn ($i) => [
        'id'             => $i->id,
        'type'           => $i->infrastructure ? $i->type.' — '.$i->infrastructure->nom : $i->type.' — '.\Illuminate\Support\Str::limit(strtok($i->description, "\n"), 50),
        'status'         => ['En cours' => 'in_progress', 'Planifiée' => 'scheduled', 'Terminée' => 'completed'][$i->statut] ?? 'scheduled',
        'priority'       => $priorite($i),
        'zone'           => $i->infrastructure?->zone?->nom ?? 'Zone non précisée',
        'address'        => $i->infrastructure?->localisation ?? '—',
        'scheduled_time' => $i->date->format('d/m/Y').($i->date->lt(today()) && $i->statut !== 'Terminée' ? ' (en retard)' : ''),
    ])->all();

    $firstName     = explode(' ', $user['name'])[0];
    $today         = $mesInterventions->filter(fn ($i) => $i->date->isToday())->count();
    $inProgress    = $mesInterventions->where('statut', 'En cours')->count();
    $scheduled     = $mesInterventions->where('statut', 'Planifiée')->count();
    $completed     = $technicien
        ? $technicien->interventions()->where('statut', 'Terminée')->whereDate('date', '>=', today()->subDays(30)->toDateString())->count()
        : 0;
    $urgent        = $mesInterventions->filter(fn ($i) => in_array($i->statut, ['Planifiée', 'En cours'], true) && $priorite($i) === 'high')->count();

    $statusStyle = [
        'in_progress' => ['bg'=>'bg-amber-50 text-amber-700 border border-amber-200','label'=>'En cours'],
        'scheduled'   => ['bg'=>'bg-blue-50 text-blue-700 border border-blue-200',  'label'=>'Planifiée'],
        'completed'   => ['bg'=>'bg-emerald-50 text-emerald-700 border border-emerald-200', 'label'=>'Terminée'],
    ];
    $priorityStyle = [
        'high'   => ['bg'=>'bg-rose-50 text-rose-700 border border-rose-200', 'label'=>'Urgent'],
        'medium' => ['bg'=>'bg-amber-50 text-amber-700 border border-amber-200', 'label'=>'Moyen'],
        'low'    => ['bg'=>'bg-slate-100 text-slate-700 border border-slate-200', 'label'=>'Faible'],
    ];
@endphp

@section('manager-content')
<div class="space-y-6">

    {{-- ══ HEADER COMPACT ══════════════════════════════════════ --}}
    <div class="glass rounded-2xl p-5 border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-cyan-50 border border-cyan-200 text-cyan-800 text-[11px] font-semibold mb-1.5">
                    <i data-lucide="wrench" class="w-3.5 h-3.5 text-cyan-600"></i>
                    Terrain & Maintenance Opérationnelle
                </div>
                <h1 class="text-xl sm:text-2xl font-display font-bold text-slate-900 tracking-tight">
                    Bonjour, {{ $firstName }} 👋
                </h1>
                <p class="text-slate-500 text-xs mt-1">
                    Vous avez <strong class="text-slate-800 font-semibold">{{ $today }}</strong> interventions prévues
                    @if($inProgress > 0)
                        · <strong class="text-amber-600 font-semibold">{{ $inProgress }}</strong> en cours
                    @endif
                    @if($urgent > 0)
                        · <strong class="text-rose-600 font-semibold">{{ $urgent }}</strong> urgente(s)
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('technician.tournee') }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-cyan-600 hover:bg-cyan-700 text-white shadow-sm flex items-center gap-1.5 transition-all">
                    <i data-lucide="map" class="w-4 h-4"></i>
                    <span>Tournée du jour</span>
                </a>
                <a href="{{ route('technician.scan') }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 shadow-sm flex items-center gap-1.5 transition-all">
                    <i data-lucide="qr-code" class="w-4 h-4 text-slate-500"></i>
                    <span>Scanner</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ══ 4 KPI CARDS (Normal ergonomic size) ════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        @foreach([
            ['val'=>$today,     'label'=>'Aujourd\'hui',  'icon'=>'calendar',      'bg'=>'bg-cyan-50',  'ic'=>'text-cyan-600',  'border'=>'border-cyan-100'],
            ['val'=>$inProgress,'label'=>'En cours',       'icon'=>'loader',        'bg'=>'bg-amber-50', 'ic'=>'text-amber-600', 'border'=>'border-amber-100'],
            ['val'=>$completed, 'label'=>'Terminées (30 j)', 'icon'=>'check-circle',  'bg'=>'bg-emerald-50','ic'=>'text-emerald-600','border'=>'border-emerald-100'],
            ['val'=>$urgent,    'label'=>'Urgentes',       'icon'=>'alert-triangle','bg'=>'bg-rose-50',   'ic'=>'text-rose-600',   'border'=>'border-rose-100'],
        ] as $kpi)
        <div class="glass rounded-xl p-4 border border-slate-200 bg-white shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-500 font-medium">{{ $kpi['label'] }}</p>
                <p class="text-2xl font-display font-bold text-slate-900 mt-1">{{ $kpi['val'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl {{ $kpi['bg'] }} {{ $kpi['border'] }} border flex items-center justify-center shrink-0">
                <i data-lucide="{{ $kpi['icon'] }}" class="w-5 h-5 {{ $kpi['ic'] }}"></i>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ══ INTERVENTION URGENTE EN TÊTE ═══════════════════════ --}}
    @php $urgentInt = array_values(array_filter($interventions, fn($i)=>$i['priority']==='high' && $i['status']==='in_progress'))[0] ?? null; @endphp
    @if($urgentInt)
    <div class="rounded-xl p-4 bg-rose-50 border border-rose-200 flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-lg bg-rose-100 flex items-center justify-center shrink-0 text-rose-600">
                <i data-lucide="alert-triangle" class="w-5 h-5 animate-pulse"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-[10px] font-mono font-bold text-rose-700 bg-rose-200/60 px-1.5 py-0.5 rounded">{{ $urgentInt['id'] }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-600 text-white">URGENT</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">En cours</span>
                </div>
                <p class="text-slate-900 font-bold text-sm">{{ $urgentInt['type'] }}</p>
                <p class="text-slate-600 text-xs mt-0.5">
                    <i data-lucide="map-pin" class="w-3 h-3 inline text-slate-400"></i>
                    {{ $urgentInt['zone'] }} — {{ $urgentInt['address'] }}
                </p>
            </div>
        </div>
        <a href="{{ route('technician.interventions.show', $urgentInt['id']) }}"
           class="shrink-0 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-all shadow-sm">
            Voir →
        </a>
    </div>
    @endif

    {{-- ══ INTERVENTIONS DU JOUR ═══════════════════════════════ --}}
    <div class="glass rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h2 class="text-slate-900 font-display font-bold text-sm">Mes interventions</h2>
                <p class="text-slate-500 text-xs mt-0.5">{{ $today }} assignées · {{ $scheduled }} planifiées</p>
            </div>
            <a href="{{ route('technician.interventions.index') }}"
               class="text-xs text-cyan-600 hover:text-cyan-700 font-semibold transition-colors">
                Tout voir →
            </a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($interventions as $int)
            @php
                $ss = $statusStyle[$int['status']] ?? $statusStyle['scheduled'];
                $ps = $priorityStyle[$int['priority']] ?? $priorityStyle['low'];
            @endphp
            <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50/75 transition-colors">
                <div class="w-8 h-8 rounded-lg {{ $ps['bg'] }} flex items-center justify-center shrink-0">
                    @if($int['priority']==='high')
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i>
                    @elseif($int['status']==='in_progress')
                    <i data-lucide="loader" class="w-4 h-4 text-amber-600"></i>
                    @else
                    <i data-lucide="wrench" class="w-4 h-4 text-slate-500"></i>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                        <span class="text-[10px] font-mono font-bold text-cyan-700">{{ $int['id'] }}</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $ps['bg'] }}">{{ $ps['label'] }}</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $ss['bg'] }}">{{ $ss['label'] }}</span>
                    </div>
                    <p class="text-slate-900 text-xs sm:text-sm font-semibold truncate">{{ $int['type'] }}</p>
                    <p class="text-slate-500 text-xs truncate">
                        <i data-lucide="map-pin" class="w-3 h-3 inline text-slate-400"></i>
                        {{ $int['zone'] }} · {{ $int['scheduled_time'] }}
                    </p>
                </div>
                <div class="flex gap-1.5 shrink-0">
                    @if($int['status']==='in_progress')
                    <a href="{{ route('technician.interventions.report', $int['id']) }}"
                       class="p-2 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 transition-colors text-xs font-semibold flex items-center gap-1"
                       title="Remplir le rapport">
                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">Rapport</span>
                    </a>
                    @endif
                    <a href="{{ route('technician.interventions.show', $int['id']) }}"
                       class="p-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors"
                       title="Détails">
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-sm text-slate-500">Aucune intervention planifiée ou en cours. Bonne journée !</div>
            @endforelse
        </div>
    </div>

    {{-- ══ ÉQUIPEMENT + ZONES côte à côte ═══════════════════ --}}
    <div class="grid sm:grid-cols-2 gap-4">

        {{-- Équipement --}}
        <div class="glass rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h2 class="text-slate-900 font-display font-bold text-sm">Mon équipement</h2>
                <a href="{{ route('technician.equipment') }}"
                   class="text-xs text-cyan-600 hover:text-cyan-700 font-semibold transition-colors">
                    Tout voir →
                </a>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach(array_slice($equipment, 0, 5) as $eq)
                @php
                    $eqStyle = [
                        'available' => ['dot'=>'bg-emerald-500','text'=>'text-emerald-700 bg-emerald-50','label'=>'OK'],
                        'in_use'    => ['dot'=>'bg-amber-500','text'=>'text-amber-700 bg-amber-50','label'=>'Utilisé'],
                        'low_stock' => ['dot'=>'bg-rose-500 animate-pulse','text'=>'text-rose-700 bg-rose-50','label'=>'Stock bas'],
                        'maintenance'=>['dot'=>'bg-blue-500','text'=>'text-blue-700 bg-blue-50','label'=>'Maintenance'],
                    ][$eq['status']] ?? ['dot'=>'bg-slate-400','text'=>'text-slate-700 bg-slate-50','label'=>$eq['status']];
                @endphp
                <div class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-50/75 transition-colors">
                    <span class="w-2 h-2 rounded-full {{ $eqStyle['dot'] }} shrink-0"></span>
                    <span class="text-xs sm:text-sm text-slate-700 flex-1 truncate">{{ $eq['name'] }}</span>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $eqStyle['text'] }} shrink-0">{{ $eqStyle['label'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Zones assignées --}}
        <div class="glass rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h2 class="text-slate-900 font-display font-bold text-sm">Mes zones</h2>
                    <p class="text-slate-500 text-[11px] mt-0.5">Secteurs sous surveillance terrain</p>
                </div>
                <a href="{{ route('technician.zones.index') }}"
                   class="text-xs text-cyan-600 hover:text-cyan-700 font-semibold transition-colors">
                    Tout voir →
                </a>
            </div>
            <div class="divide-y divide-slate-100">
                @if($dbZones->isNotEmpty())
                    @foreach($dbZones as $z)
                    <a href="{{ route('technician.zones.show', $z) }}" class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-50/75 transition-colors block group">
                        <div class="w-7 h-7 rounded-lg bg-cyan-50 border border-cyan-100 flex items-center justify-center text-cyan-600 shrink-0 group-hover:scale-105 transition-transform">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-slate-900 text-xs sm:text-sm font-semibold truncate group-hover:text-cyan-600 transition-colors">{{ $z->nom }}</p>
                            <p class="text-slate-500 text-[11px]">{{ $z->code }} · {{ $z->infrastructures_count }} ouvrage(s)</p>
                        </div>
                        <span class="w-2 h-2 rounded-full shrink-0 {{ $z->statut === 'active' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    </a>
                    @endforeach
                @else
                    @foreach($zones as $zone)
                    <div class="flex items-center gap-3 px-5 py-2.5 hover:bg-slate-50/75 transition-colors">
                        <span class="text-base shrink-0">{{ $zone['emoji'] }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-slate-900 text-xs sm:text-sm font-semibold">{{ $zone['name'] }}</p>
                            <p class="text-slate-500 text-[11px]">Qualité : {{ $zone['quality'] }}%</p>
                        </div>
                        <span class="w-2 h-2 rounded-full shrink-0 {{ $zone['status']==='normal' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
