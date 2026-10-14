@extends('layouts.app')

@section('title', 'AquaSecure — Gestion des incidents')

@php
    use App\Data\PlaceholderData;
    $user         = session('user', ['name' => 'Ines Mansouri', 'role' => 'manager']);
    $reclamations = PlaceholderData::adminAllReclamations();
    $technicians  = PlaceholderData::adminTechnicians();
@endphp

@section('content')
<div class="min-h-screen bg-[#070e17] text-slate-100 flex flex-col font-sans">

    {{-- Top Header --}}
    <header class="sticky top-0 z-50 bg-[#0c1624]/90 backdrop-blur-md border-b border-slate-800/80 px-4 sm:px-6 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('manager.dashboard') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 hover:text-white">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-red-500/20 border border-red-500/30 flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-red-400"></i>
                    </div>
                    <span class="font-display font-bold text-white text-base">Centre de gestion des incidents</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('citizen.reports.create') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tester un signalement citoyen
                </a>
                <x-notification-center />
                <x-user-menu />
            </div>
        </div>
    </header>

    {{-- Main Container --}}
    <div class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 space-y-6">
        
        {{-- Section Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-display font-bold text-white tracking-tight">Gestion des incidents et affectation des techniciens</h1>
                <p class="text-slate-400 text-xs mt-1">Review incoming citizen reports, dispatch field repair crews, update resolution status, and notify residents.</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="showToast('Bulk notifying affected residents via SMS & Email...', 'info')" class="px-3.5 py-2 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 hover:bg-red-500/20 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="bell" class="w-4 h-4"></i> Alerter les habitants en cas de coupure
                </button>
            </div>
        </div>

        {{-- INCIDENTS DISPATCH LIST & ASSIGNMENT --}}
        <div class="grid lg:grid-cols-3 gap-6">
            
            {{-- Incidents List (2/3) --}}
            <div class="lg:col-span-2 bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <i data-lucide="inbox" class="w-4 h-4 text-cyan-400"></i> Signalements citoyens et anomalies signalées
                    </h3>
                    <span class="text-xs text-slate-400">{{ count($reclamations) }} signalements au total</span>
                </div>
                <div class="divide-y divide-slate-800">
                    @foreach($reclamations as $rec)
                        <div class="p-4 hover:bg-slate-800/40 transition-colors space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-mono text-xs font-bold text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded border border-cyan-500/20">{{ $rec['id'] }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $rec['priority'] === 'critical' ? 'bg-red-500/20 text-red-300 border border-red-500/30' : 'bg-amber-500/20 text-amber-300' }}">
                                            {{ strtoupper($rec['priority']) }}
                                        </span>
                                        <span class="text-xs text-slate-400">• {{ $rec['created_at'] }}</span>
                                    </div>
                                    <h4 class="text-base font-bold text-white">{{ $rec['type'] }}</h4>
                                    <p class="text-xs text-slate-300 mt-0.5">{{ $rec['description'] }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $rec['status'] === 'resolved' ? 'bg-teal-500/20 text-teal-300 border border-teal-500/30' : ($rec['status'] === 'in_progress' ? 'bg-amber-500/20 text-amber-300' : 'bg-red-500/20 text-red-300') }}">
                                        {{ ucfirst(str_replace('_', ' ', $rec['status'])) }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2 text-xs pt-2 border-t border-slate-800/60">
                                <div class="text-slate-400">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 inline text-slate-500"></i> {{ $rec['address'] }} ({{ $rec['zone'] }})
                                </div>
                                <div class="flex items-center gap-2">
                                    <button onclick="dispatchTechnician('{{ $rec['id'] }}')" class="px-3 py-1 rounded-lg bg-cyan-500/15 hover:bg-cyan-500/25 text-cyan-300 border border-cyan-500/30 font-semibold text-xs">
                                        Affecter un technicien
                                    </button>
                                    <button onclick="updateStatus('{{ $rec['id'] }}')" class="px-3 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold text-xs">
                                        Mettre à jour le statut
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Available Technicians & Dispatch Desk (1/3) --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 space-y-4 shadow-xl">
                <h3 class="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-800 pb-3">
                    <i data-lucide="wrench" class="w-4 h-4 text-cyan-400"></i> Techniciens de terrain disponibles
                </h3>
                
                <div class="space-y-3 text-xs">
                    @foreach($technicians as $tech)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                            <div>
                                <div class="font-bold text-white">{{ $tech['name'] }}</div>
                                <div class="text-[11px] text-slate-400">{{ $tech['zone'] }}</div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $tech['status'] === 'on_mission' ? 'bg-amber-500/15 text-amber-300' : 'bg-teal-500/15 text-teal-300' }}">
                                {{ $tech['status'] === 'on_mission' ? 'On Mission' : 'Available' }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-4 text-xs space-y-2">
                    <div class="font-bold text-cyan-300">Notification automatique des habitants</div>
                    <p class="text-slate-400 text-[11px]">When an incident status changes to "Dispatched" or "Resolved", residents receive automated SMS and email notifications.</p>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
function dispatchTechnician(id) {
    showToast('Dispatched Field Technician Amira Ben Ali to ' + id, 'success');
}
function updateStatus(id) {
    showToast('Updated status for ' + id + ' to IN PROGRESS', 'info');
}
</script>
@endsection
