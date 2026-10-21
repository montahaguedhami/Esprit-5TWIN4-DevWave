@extends(request()->routeIs('admin.*') ? 'layouts.admin' : 'layouts.manager')

@section('title', 'Créer une infrastructure — AquaSecure')

@php
    $inventoryRoutePrefix = request()->routeIs('admin.*') ? 'admin' : 'manager';
@endphp

@section(request()->routeIs('admin.*') ? 'admin-content' : 'manager-content')
<div style="max-width:800px;margin:0 auto;">

    {{-- Page header --}}
    <div style="margin-bottom:20px;">
        <a href="{{ route($inventoryRoutePrefix . '.infrastructures.index') }}"
           style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--role-accent,#0891b2);
                  text-decoration:none;margin-bottom:10px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Retour aux infrastructures
        </a>
        <h1 class="page-title">Nouvelle infrastructure</h1>
        <p style="font-size:13px;color:var(--text-muted,#94a3b8);margin-top:4px;">
            Ajoutez un nouvel ouvrage au réseau de votre zone.
        </p>
    </div>

    @include('manager.shared.flash')

    {{-- Form card --}}
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route($inventoryRoutePrefix . '.infrastructures.store') }}" novalidate>
                @include('manager.infrastructures._form', [
                    'infrastructure'     => null,
                    'inventoryRoutePrefix' => $inventoryRoutePrefix,
                ])
            </form>
        </div>
    </div>

</div>
@endsection
