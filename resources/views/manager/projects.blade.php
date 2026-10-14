@extends('layouts.app')

@section('title', 'AquaSecure — Projets et maintenance du réseau')

@php
    use App\Data\PlaceholderData;
    $user     = session('user', ['name' => 'Ines Mansouri', 'role' => 'manager']);
    $projects = PlaceholderData::projects();
@endphp

@section('content')
<div class="min-h-screen bg-[#070e17] text-slate-100 flex flex-col font-sans">

    {{-- Top Navigation --}}
    <header class="sticky top-0 z-50 bg-[#0c1624]/90 backdrop-blur-md border-b border-slate-800/80 px-4 sm:px-6 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('manager.dashboard') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 hover:text-white">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center">
                        <i data-lucide="briefcase" class="w-4 h-4 text-amber-400"></i>
                    </div>
                    <span class="font-display font-bold text-white text-base">Projets de rénovation des infrastructures</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="showToast('Create new project modal opening...', 'info')" class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-cyan-500/20">
                    <i data-lucide="plus" class="w-4 h-4"></i> Nouveau projet
                </button>
                <x-notification-center />
                <x-user-menu />
            </div>
        </div>
    </header>

    {{-- Content Body --}}
    <div class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 space-y-6">
        
        {{-- Header & Filters --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-display font-bold text-white tracking-tight">Rénovation et dépollution des infrastructures</h1>
                <p class="text-slate-400 text-xs mt-1">Track capital investment, contractor progress, milestone deadlines, and budget expenditure.</p>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" placeholder="Filter projects by keyword or zone..." class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-white placeholder-slate-400 outline-none">
            </div>
        </div>

        {{-- SUMMARY METRICS --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold">
                    <i data-lucide="wrench" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="text-2xl font-display font-bold text-white">3 Capital Projects</div>
                    <div class="text-xs text-slate-400">2 In Progress, 1 Completed</div>
                </div>
            </div>
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold">
                    <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="text-2xl font-display font-bold text-white">$4,850,000</div>
                    <div class="text-xs text-slate-400">Budget total prévu</div>
                </div>
            </div>
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center font-bold">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="text-2xl font-display font-bold text-white">77.6%</div>
                    <div class="text-xs text-slate-400">Avancement moyen des étapes</div>
                </div>
            </div>
        </div>

        {{-- PROJECTS CARDS & MILESTONES (Requirement 7) --}}
        <div class="space-y-6">
            @foreach($projects as $prj)
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-xl hover:border-slate-700 transition-all">
                    
                    {{-- Top Row: Project Header --}}
                    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-800/80 pb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-mono text-xs font-bold text-cyan-400 bg-cyan-500/10 px-2.5 py-0.5 rounded border border-cyan-500/20">{{ $prj['code'] }}</span>
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold {{ $prj['status'] === 'Completed' ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border border-amber-500/30' }}">
                                    {{ $prj['status'] }}
                                </span>
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-slate-800 text-slate-300">
                                    {{ $prj['category'] }}
                                </span>
                            </div>
                            <h2 class="text-xl font-display font-bold text-white">{{ $prj['title'] }}</h2>
                            <p class="text-xs text-slate-400 mt-1 max-w-3xl">{{ $prj['description'] }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-400">Planned vs Actual Spent</div>
                            <div class="text-lg font-display font-bold text-white">${{ number_format($prj['actual_spent']) }} / <span class="text-slate-400 text-sm">${{ number_format($prj['planned_budget']) }}</span></div>
                            <div class="text-[11px] text-cyan-300 mt-0.5">Manager: {{ $prj['project_manager'] }}</div>
                        </div>
                    </div>

                    {{-- Progress Bar & Sources de financement --}}
                    <div class="grid lg:grid-cols-3 gap-6 items-center">
                        <div class="lg:col-span-2 space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-300">Avancement global</span>
                                <span class="text-cyan-400">{{ $prj['completion_pct'] }}% Complete</span>
                            </div>
                            <div class="w-full bg-slate-950 h-3 rounded-full overflow-hidden p-0.5 border border-slate-800">
                                <div class="bg-gradient-to-r from-cyan-500 to-blue-500 h-full rounded-full transition-all duration-1000" style="width: {{ $prj['completion_pct'] }}%"></div>
                            </div>
                            <div class="flex justify-between text-[11px] text-slate-400 pt-1">
                                <span>Start: {{ $prj['start_date'] }}</span>
                                <span>Contractor: <strong class="text-slate-200">{{ $prj['contractor'] }}</strong></span>
                                <span>Fin prévue : {{ $prj['end_date'] }}</span>
                            </div>
                        </div>

                        {{-- Funding Source Badges --}}
                        <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-3 text-xs space-y-1.5">
                            <div class="font-bold text-slate-400 text-[10px] uppercase tracking-wider">Contributions financières</div>
                            @foreach($prj['funding_sources'] as $source)
                                <div class="flex items-center gap-1.5 text-slate-300">
                                    <i data-lucide="circle-dot" class="w-3 h-3 text-cyan-400"></i>
                                    <span>{{ $source }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Milestones Checklist & Attached Documents --}}
                    <div class="grid lg:grid-cols-2 gap-4 pt-2 border-t border-slate-800/60 text-xs">
                        
                        {{-- Milestones --}}
                        <div>
                            <h4 class="font-bold text-white mb-2 flex items-center gap-2">
                                <i data-lucide="check-square" class="w-4 h-4 text-cyan-400"></i> Étapes et calendrier du projet
                            </h4>
                            <div class="space-y-1.5">
                                @foreach($prj['milestones'] as $m)
                                    <div class="flex items-center justify-between bg-slate-950/40 px-3 py-1.5 rounded-lg border border-slate-800/80">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="{{ $m['done'] ? 'check-circle-2' : 'clock' }}" class="w-4 h-4 {{ $m['done'] ? 'text-emerald-400' : 'text-slate-500' }}"></i>
                                            <span class="{{ $m['done'] ? 'text-slate-200 font-medium' : 'text-slate-400' }}">{{ $m['name'] }}</span>
                                        </div>
                                        <span class="text-[10px] font-mono text-slate-400">{{ $m['date'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Attached Documents --}}
                        <div>
                            <h4 class="font-bold text-white mb-2 flex items-center gap-2">
                                <i data-lucide="paperclip" class="w-4 h-4 text-blue-400"></i> Documents et rapports de suivi
                            </h4>
                            <div class="space-y-1.5">
                                @foreach($prj['documents'] as $doc)
                                    <div class="flex items-center justify-between bg-slate-950/40 px-3 py-1.5 rounded-lg border border-slate-800/80">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="file-text" class="w-4 h-4 text-cyan-400"></i>
                                            <span class="text-slate-200 font-medium">{{ $doc['name'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] text-slate-400">{{ $doc['size'] }}</span>
                                            <button onclick="showToast('Downloading {{ $doc['name'] }}...', 'info')" class="text-cyan-400 hover:text-cyan-300 font-bold text-[11px]">Download</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>

                </div>
            @endforeach
        </div>

    </div>
</div>
@endsection
