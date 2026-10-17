<style>
.aqua-manager-standalone {
    color: #152b42;
    background: #f3f7fb;
}
.aqua-manager-standalone [class~="text-white"],
.aqua-manager-standalone [class~="text-white/50"],
.aqua-manager-standalone [class~="text-white/70"],
.aqua-manager-standalone [class~="text-white/80"],
.aqua-manager-standalone [class~="text-white/90"],
.aqua-manager-standalone [class~="text-slate-100"],
.aqua-manager-standalone [class~="text-slate-200"],
.aqua-manager-standalone [class~="text-slate-300"] { color: #152b42; }
.aqua-manager-standalone [class~="text-slate-400"],
.aqua-manager-standalone [class~="text-slate-500"],
.aqua-manager-standalone [class*="text-white/"],
.aqua-manager-standalone [class*="text-cyan-100/"] { color: #52677d; }
.aqua-manager-standalone [class~="text-cyan-50"],
.aqua-manager-standalone [class~="text-cyan-100"] { color: #52677d; }
.aqua-manager-standalone [class*="text-cyan-"] { color: #087e9b; }
.aqua-manager-standalone [class*="text-red-"],
.aqua-manager-standalone [class*="text-rose-"] { color: #b42332; }
.aqua-manager-standalone [class*="text-teal-"],
.aqua-manager-standalone [class*="text-emerald-"],
.aqua-manager-standalone [class*="text-green-"] { color: #167448; }
.aqua-manager-standalone [class*="text-amber-"],
.aqua-manager-standalone [class*="text-yellow-"] { color: #976000; }
.aqua-manager-standalone [class*="text-blue-"],
.aqua-manager-standalone [class*="text-sky-"] { color: #2459a6; }
.aqua-manager-standalone [class*="text-purple-"],
.aqua-manager-standalone [class*="text-violet-"] { color: #6941a5; }
.aqua-manager-standalone [class~="bg-[#070e17]"],
.aqua-manager-standalone [class~="bg-[#0c1624]/90"],
.aqua-manager-standalone [class*="bg-slate-950"],
.aqua-manager-standalone [class*="bg-slate-900"],
.aqua-manager-standalone [class*="bg-slate-800"] { background-color: #fff; }
.aqua-manager-standalone [class~="bg-white/5"],
.aqua-manager-standalone [class~="bg-white/10"],
.aqua-manager-standalone [class~="bg-white/15"] { background-color: #f3f7fb; }
.aqua-manager-standalone [class~="border-white/5"],
.aqua-manager-standalone [class~="border-white/10"],
.aqua-manager-standalone [class~="border-white/15"],
.aqua-manager-standalone [class~="border-white/20"],
.aqua-manager-standalone [class*="border-slate-800"],
.aqua-manager-standalone [class*="border-slate-700"] { border-color: #dce6ef; }
.aqua-manager-standalone [class~="bg-cyan-500/10"],
.aqua-manager-standalone [class~="bg-teal-500/10"],
.aqua-manager-standalone [class~="bg-emerald-500/10"],
.aqua-manager-standalone [class~="bg-green-500/10"] { background-color: #e8f6f7; }
.aqua-manager-standalone [class~="bg-amber-500/10"],
.aqua-manager-standalone [class~="bg-amber-500/15"] { background-color: #fff5df; }
.aqua-manager-standalone [class~="bg-red-500/10"],
.aqua-manager-standalone [class~="bg-red-500/15"],
.aqua-manager-standalone [class~="bg-rose-500/10"],
.aqua-manager-standalone [class~="bg-rose-500/15"] { background-color: #fff0ef; }
.aqua-manager-standalone [class~="bg-blue-500/10"],
.aqua-manager-standalone [class~="bg-blue-500/15"] { background-color: #edf4ff; }
.aqua-manager-standalone .glass,
.aqua-manager-standalone .glass-strong {
    color: #152b42;
    background: rgb(255 255 255 / 95%);
    border-color: #dce6ef;
    box-shadow: 0 8px 24px rgb(16 42 67 / 6%);
    backdrop-filter: blur(14px);
}
.aqua-manager-standalone input:not([type="checkbox"]):not([type="radio"]),
.aqua-manager-standalone select,
.aqua-manager-standalone textarea {
    color: #152b42;
    background-color: #fff;
    border-color: #cbd8e4;
}
.aqua-manager-standalone input::placeholder,
.aqua-manager-standalone textarea::placeholder { color: #718397; opacity: 1; }
.aqua-manager-standalone table { color: #152b42; }
.aqua-manager-standalone thead { background-color: #f3f7fb; }
.aqua-manager-standalone tbody tr:hover { background-color: #f4f9fb; }
.aqua-manager-standalone header {
    background-color: rgb(255 255 255 / 96%);
    border-color: #dce6ef;
    box-shadow: 0 2px 8px rgb(16 42 67 / 5%);
}
.aqua-manager-standalone header .glass,
.aqua-manager-standalone header .glass-strong {
    background: #fff;
    border-color: #dce6ef;
}
.aqua-manager-standalone [class~="text-white"][class*="from-cyan-"],
.aqua-manager-standalone [class~="text-white"][class*="from-blue-"] { color: #fff; }
.aqua-manager-standalone [class~="text-white"][class~="bg-cyan-500"],
.aqua-manager-standalone [class~="text-white"][class~="bg-cyan-600"],
.aqua-manager-standalone [class~="text-white"][class~="bg-blue-600"] { color: #fff; }
[data-theme="dark"] .aqua-manager-standalone {
    color: var(--text-primary);
    background: var(--bg-app);
}
[data-theme="dark"] .aqua-manager-standalone [class~="text-white"],
[data-theme="dark"] .aqua-manager-standalone [class~="text-slate-100"],
[data-theme="dark"] .aqua-manager-standalone [class~="text-slate-200"],
[data-theme="dark"] .aqua-manager-standalone [class~="text-slate-300"] { color: var(--text-primary); }
[data-theme="dark"] .aqua-manager-standalone [class~="text-slate-400"],
[data-theme="dark"] .aqua-manager-standalone [class~="text-slate-500"],
[data-theme="dark"] .aqua-manager-standalone [class*="text-white/"],
[data-theme="dark"] .aqua-manager-standalone [class*="text-cyan-100/"] { color: var(--text-secondary); }
[data-theme="dark"] .aqua-manager-standalone [class*="text-cyan-"] { color: #67e8f9; }
[data-theme="dark"] .aqua-manager-standalone [class*="text-red-"],
[data-theme="dark"] .aqua-manager-standalone [class*="text-rose-"] { color: #fca5a5; }
[data-theme="dark"] .aqua-manager-standalone [class*="text-teal-"],
[data-theme="dark"] .aqua-manager-standalone [class*="text-emerald-"],
[data-theme="dark"] .aqua-manager-standalone [class*="text-green-"] { color: #6ee7b7; }
[data-theme="dark"] .aqua-manager-standalone [class*="text-amber-"],
[data-theme="dark"] .aqua-manager-standalone [class*="text-yellow-"] { color: #fcd34d; }
[data-theme="dark"] .aqua-manager-standalone [class*="text-blue-"],
[data-theme="dark"] .aqua-manager-standalone [class*="text-sky-"] { color: #93c5fd; }
[data-theme="dark"] .aqua-manager-standalone [class*="text-purple-"],
[data-theme="dark"] .aqua-manager-standalone [class*="text-violet-"] { color: #c4b5fd; }
[data-theme="dark"] .aqua-manager-standalone [class~="bg-[#070e17]"],
[data-theme="dark"] .aqua-manager-standalone [class~="bg-[#0c1624]/90"],
[data-theme="dark"] .aqua-manager-standalone [class*="bg-slate-950"],
[data-theme="dark"] .aqua-manager-standalone [class*="bg-slate-900"],
[data-theme="dark"] .aqua-manager-standalone [class*="bg-slate-800"] { background-color: var(--bg-card); }
[data-theme="dark"] .aqua-manager-standalone [class~="bg-white/5"],
[data-theme="dark"] .aqua-manager-standalone [class~="bg-white/10"],
[data-theme="dark"] .aqua-manager-standalone [class~="bg-white/15"] { background-color: var(--bg-muted); }
[data-theme="dark"] .aqua-manager-standalone [class~="border-white/5"],
[data-theme="dark"] .aqua-manager-standalone [class~="border-white/10"],
[data-theme="dark"] .aqua-manager-standalone [class~="border-white/15"],
[data-theme="dark"] .aqua-manager-standalone [class~="border-white/20"],
[data-theme="dark"] .aqua-manager-standalone [class*="border-slate-800"],
[data-theme="dark"] .aqua-manager-standalone [class*="border-slate-700"] { border-color: var(--border); }
[data-theme="dark"] .aqua-manager-standalone [class~="bg-cyan-500/10"],
[data-theme="dark"] .aqua-manager-standalone [class~="bg-teal-500/10"],
[data-theme="dark"] .aqua-manager-standalone [class~="bg-emerald-500/10"],
[data-theme="dark"] .aqua-manager-standalone [class~="bg-green-500/10"] { background-color: var(--success-light); }
[data-theme="dark"] .aqua-manager-standalone [class~="bg-amber-500/10"],
[data-theme="dark"] .aqua-manager-standalone [class~="bg-amber-500/15"] { background-color: var(--warning-light); }
[data-theme="dark"] .aqua-manager-standalone [class~="bg-red-500/10"],
[data-theme="dark"] .aqua-manager-standalone [class~="bg-red-500/15"],
[data-theme="dark"] .aqua-manager-standalone [class~="bg-rose-500/10"],
[data-theme="dark"] .aqua-manager-standalone [class~="bg-rose-500/15"] { background-color: var(--danger-light); }
[data-theme="dark"] .aqua-manager-standalone [class~="bg-blue-500/10"],
[data-theme="dark"] .aqua-manager-standalone [class~="bg-blue-500/15"] { background-color: var(--info-light); }
[data-theme="dark"] .aqua-manager-standalone .glass,
[data-theme="dark"] .aqua-manager-standalone .glass-strong {
    color: var(--text-primary);
    background: var(--bg-card);
    border-color: var(--border);
    box-shadow: var(--shadow-sm);
}
[data-theme="dark"] .aqua-manager-standalone input:not([type="checkbox"]):not([type="radio"]),
[data-theme="dark"] .aqua-manager-standalone select,
[data-theme="dark"] .aqua-manager-standalone textarea {
    color: var(--text-primary);
    background-color: var(--bg-input);
    border-color: var(--border);
}
[data-theme="dark"] .aqua-manager-standalone input::placeholder,
[data-theme="dark"] .aqua-manager-standalone textarea::placeholder { color: var(--text-muted); }
[data-theme="dark"] .aqua-manager-standalone table { color: var(--text-secondary); }
[data-theme="dark"] .aqua-manager-standalone thead { background-color: var(--bg-muted); }
[data-theme="dark"] .aqua-manager-standalone tbody tr:hover { background-color: var(--bg-hover); }
[data-theme="dark"] .aqua-manager-standalone header,
[data-theme="dark"] .aqua-manager-standalone header .glass,
[data-theme="dark"] .aqua-manager-standalone header .glass-strong {
    background-color: var(--bg-topbar);
    border-color: var(--border);
    box-shadow: var(--shadow-sm);
}
[data-theme="dark"] .aqua-manager-standalone [class~="text-white"][class*="from-"],
[data-theme="dark"] .aqua-manager-standalone [class~="text-white"][class*="bg-cyan-"],
[data-theme="dark"] .aqua-manager-standalone [class~="text-white"][class*="bg-blue-"] { color: #fff; }
@media (max-width: 640px) {
    .aqua-manager-standalone header .max-w-7xl { flex-wrap: wrap; gap: .75rem; }
    .aqua-manager-standalone table th,
    .aqua-manager-standalone table td { padding-left: .75rem; padding-right: .75rem; }
}
</style>
