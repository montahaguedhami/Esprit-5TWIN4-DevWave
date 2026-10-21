@props(['risk'])
@php($levelColor = match ($risk['level'] ?? 'Faible') { 'Critique' => 'border-rose-300 bg-rose-50 text-rose-900', 'Élevé' => 'border-orange-300 bg-orange-50 text-orange-900', 'Moyen' => 'border-amber-300 bg-amber-50 text-amber-900', default => 'border-emerald-300 bg-emerald-50 text-emerald-900' })
<section class="rounded-xl border p-4 {{ $levelColor }}">
    <h2 class="font-semibold">Risque de panne · Estimation indicative</h2>
    <p class="mt-2 text-sm">Niveau : <strong>{{ $risk['level'] ?? 'Inconnu' }}</strong> — {{ number_format($risk['probability'] ?? 0, 0) }} % sur 30 jours</p>
</section>
