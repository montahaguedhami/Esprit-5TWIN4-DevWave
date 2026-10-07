{{--
    Flèche de retour : revient à la page précédente du site (comme le bouton retour du navigateur).
    Si on arrive d'ailleurs (lien direct, nouvel onglet), on va vers $fallback.
--}}
@props([
    'fallback',
    'iconClass' => 'w-4 h-4',
])

<a href="{{ $fallback }}"
   onclick="if (document.referrer.startsWith(window.location.origin + '/') && window.history.length > 1) { window.history.back(); return false; }"
   title="Retour" aria-label="Retour"
   {{ $attributes->merge(['class' => 'liquid-tool text-white/80 hover:text-white']) }}>
    <i data-lucide="arrow-left" class="{{ $iconClass }}"></i>
</a>
