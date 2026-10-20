@props(['status'])

@php
    [$label, $classes] = match ($status) {
        'a_faire' => ['À faire', 'border-red-200 bg-red-100 text-red-800'],
        'en_cours' => ['En cours', 'border-blue-200 bg-blue-100 text-blue-800'],
        'terminee' => ['Terminée', 'border-green-200 bg-green-100 text-green-800'],
        default => [ucfirst(str_replace('_', ' ', (string) $status)), 'border-slate-200 bg-slate-100 text-slate-700'],
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-md border px-2 py-1 text-xs font-bold', $classes]) }}>
    {{ $label }}
</span>
