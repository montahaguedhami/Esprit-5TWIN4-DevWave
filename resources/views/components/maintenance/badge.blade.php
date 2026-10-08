{{-- Badge coloré pour le statut d'une intervention ou la disponibilité d'un technicien --}}
@props(['value'])

@php
    $status = match ($value) {
        'Disponible', 'Terminée' => 'normal',
        'En intervention', 'En cours' => 'alert',
        'Planifiée' => 'info',
        'En congé', 'Annulée' => 'pending',
        default => 'info',
    };
@endphp

<x-ui.status-badge :status="$status" size="sm" :dot="in_array($value, ['En intervention', 'En cours'])" {{ $attributes }}>
    {{ $value }}
</x-ui.status-badge>
