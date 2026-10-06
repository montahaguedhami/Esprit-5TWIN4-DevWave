@extends('layouts.manager')

@section('manager-content')
<div class="flex items-center justify-center min-h-[60vh]">
    <div class="text-center space-y-4">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-cyan-500/10 mb-4">
            <i data-lucide="arrow-right" class="w-8 h-8 text-cyan-400"></i>
        </div>
        <h2 class="text-2xl font-display font-bold text-white">Redirection vers le module Projets...</h2>
        <p class="text-cyan-100/60">Vous allez être redirigé vers la gestion des projets et financements.</p>
    </div>
</div>

<script>
// Redirection automatique vers le vrai module de projets
window.location.href = "{{ route('manager.projets.index') }}";
</script>
@endsection
