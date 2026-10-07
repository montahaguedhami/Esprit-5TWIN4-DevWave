{{-- Bouton de suppression avec confirmation (formulaire DELETE) --}}
@props([
    'action',
    'confirm' => 'Confirmer la suppression ?',
    'label' => 'Supprimer',
    'size' => 'md',
])

<form method="POST" action="{{ $action }}" onsubmit="return confirm(@js($confirm))" {{ $attributes->merge(['class' => 'inline']) }}>
    @csrf
    @method('DELETE')
    <x-ui.button type="submit" variant="danger" :size="$size" icon="trash-2" title="Supprimer" aria-label="Supprimer">{{ $label }}</x-ui.button>
</form>
