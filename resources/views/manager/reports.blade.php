@extends('layouts.app')

@section('title', 'AquaSecure — Rapports et conformité')

@section('content')
<div class="min-h-screen bg-[#070e17] text-slate-100 flex flex-col font-sans">
    <header class="sticky top-0 z-50 bg-[#0c1624]/90 backdrop-blur-md border-b border-slate-800/80 px-4 sm:px-6 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('manager.dashboard') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 hover:text-white">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-purple-500/20 border border-purple-500/30 flex items-center justify-center">
                        <i data-lucide="file-text" class="w-4 h-4 text-purple-400"></i>
                    </div>
                    <span class="font-display font-bold text-white text-base">Rapports de gestion et rapports réglementaires</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <x-notification-center />
                <x-user-menu />
            </div>
        </div>
    </header>

    <div class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 space-y-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-white tracking-tight">Rapports de conformité et du réseau d’eau</h1>
            <p class="text-slate-400 text-xs mt-1">Download official EPA/INNORPI compliance audits, water loss reports, and budget expenditure logs.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            
            {{-- Report 1 --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 space-y-4 hover:border-slate-700 transition-all">
                <div class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center">
                    <i data-lucide="flask-conical" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Audit de conformité de la qualité de l’eau</h3>
                    <p class="text-xs text-slate-400 mt-1">Synthèse des 30 derniers jours : pH, turbidité, chlore et analyses de plomb.</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="showToast('Generating Water Quality Audit PDF...', 'success')" class="flex-1 px-3 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs">
                        Download PDF
                    </button>
                    <button onclick="showToast('Exporting Water Quality CSV...', 'info')" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold">
                        CSV
                    </button>
                </div>
            </div>

            {{-- Report 2 --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 space-y-4 hover:border-slate-700 transition-all">
                <div class="w-10 h-10 rounded-xl bg-red-500/10 text-red-400 flex items-center justify-center">
                    <i data-lucide="droplet-off" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Rapport sur les pertes d’eau et délais de réparation</h3>
                    <p class="text-xs text-slate-400 mt-1">Analysis of distribution main leaks, mean time to repair, and volume loss metrics.</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="showToast('Generating Water Loss PDF Report...', 'success')" class="flex-1 px-3 py-2 rounded-xl bg-red-500 hover:bg-red-400 text-white font-bold text-xs">
                        Download PDF
                    </button>
                    <button onclick="showToast('Exporting Water Loss CSV...', 'info')" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold">
                        CSV
                    </button>
                </div>
            </div>

            {{-- Report 3 --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 space-y-4 hover:border-slate-700 transition-all">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Transparence financière et dépenses</h3>
                    <p class="text-xs text-slate-400 mt-1">Audit log of capital renovation projects, vendor transactions, and grant utilization.</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="showToast('Generating Financial Audit PDF...', 'success')" class="flex-1 px-3 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs">
                        Download PDF
                    </button>
                    <button onclick="showToast('Exporting Financial CSV...', 'info')" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold">
                        CSV
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
