@extends('layouts.admin')

@section('admin-content')
<div class="aqua-admin-page admin-audit-page">
    <div class="container mx-auto px-4 py-8">
        <header class="admin-audit-header">
            <div class="admin-audit-heading-icon" aria-hidden="true">
                <i data-lucide="scroll-text"></i>
            </div>
            <div>
                <p class="admin-audit-eyebrow">Sécurité et traçabilité</p>
                <h1>Journaux d’audit</h1>
                <p class="admin-audit-description">Consultez les connexions et les opérations sensibles effectuées sur la plateforme.</p>
            </div>
        </header>

        <section class="admin-audit-stats" aria-label="Résumé des journaux">
            @foreach([
                ['label' => 'Événements enregistrés', 'value' => $stats->total ?? 0, 'icon' => 'activity', 'tone' => 'blue'],
                ['label' => 'Actions sur les rôles', 'value' => $stats->roles ?? 0, 'icon' => 'shield-check', 'tone' => 'violet'],
                ['label' => 'Actions utilisateurs', 'value' => $stats->users ?? 0, 'icon' => 'users', 'tone' => 'cyan'],
                ['label' => 'Mesures et validations', 'value' => $stats->measures ?? 0, 'icon' => 'flask-conical', 'tone' => 'emerald'],
            ] as $stat)
                <article class="admin-audit-stat admin-audit-stat--{{ $stat['tone'] }}">
                    <span class="admin-audit-stat-icon"><i data-lucide="{{ $stat['icon'] }}"></i></span>
                    <span class="admin-audit-stat-copy">
                        <span>{{ $stat['label'] }}</span>
                        <strong>{{ number_format((int) $stat['value'], 0, ',', ' ') }}</strong>
                    </span>
                </article>
            @endforeach
        </section>

        <section class="admin-audit-panel admin-audit-filters" aria-label="Filtres des journaux">
            <form method="GET" action="{{ route('admin.logs') }}">
                <label class="admin-audit-search">
                    <i data-lucide="search" aria-hidden="true"></i>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Rechercher par email, action ou adresse IP…" aria-label="Rechercher dans les journaux">
                </label>

                <label class="sr-only" for="audit-action">Filtrer par action</label>
                <select id="audit-action" name="action">
                    <option value="">Toutes les actions</option>
                    <option value="user_login" @selected(request('action') === 'user_login')>Connexion utilisateur</option>
                    <option value="user_created" @selected(request('action') === 'user_created')>Création utilisateur</option>
                    <option value="user_updated" @selected(request('action') === 'user_updated')>Modification utilisateur</option>
                    <option value="user_status_toggled" @selected(request('action') === 'user_status_toggled')>Activation ou désactivation</option>
                    <option value="user_deleted" @selected(request('action') === 'user_deleted')>Suppression utilisateur</option>
                    <option value="role_assigned" @selected(request('action') === 'role_assigned')>Attribution de rôle</option>
                    <option value="role_permissions_updated" @selected(request('action') === 'role_permissions_updated')>Mise à jour des permissions</option>
                    <option value="measurement_created" @selected(request('action') === 'measurement_created')>Saisie d’une mesure</option>
                    <option value="measurement_validated" @selected(request('action') === 'measurement_validated')>Validation d’une mesure</option>
                </select>

                <button type="submit" class="admin-audit-filter-button">
                    <i data-lucide="list-filter" aria-hidden="true"></i>
                    <span>Filtrer</span>
                </button>
                @if(request()->filled('search') || request()->filled('action'))
                    <a href="{{ route('admin.logs') }}" class="admin-audit-reset">Réinitialiser</a>
                @endif
            </form>
        </section>

        <section class="admin-audit-panel admin-audit-list" aria-label="Événements d’audit">
            <div class="admin-audit-list-heading">
                <div>
                    <h2>Activité récente</h2>
                    <p>{{ $logs->total() }} événement(s) au total</p>
                </div>
                <span class="admin-audit-live"><i></i> Journal système</span>
            </div>

            @forelse($logs as $log)
                @php
                    $action = (string) $log->action;
                    $isSecurity = str_contains($action, 'role') || str_contains($action, 'delete') || str_contains($action, 'status');
                    $tone = $isSecurity ? 'violet' : (str_contains($action, 'validat') ? 'emerald' : (str_contains($action, 'login') ? 'cyan' : 'blue'));
                    $icon = $isSecurity ? 'shield-alert' : (str_contains($action, 'login') ? 'log-in' : (str_contains($action, 'measure') ? 'activity' : 'file-text'));
                    $actionLabel = [
                        'user_login' => 'Connexion utilisateur',
                        'user_created' => 'Création utilisateur',
                        'user_updated' => 'Modification utilisateur',
                        'user_status_toggled' => 'Statut utilisateur modifié',
                        'user_deleted' => 'Suppression utilisateur',
                        'role_assigned' => 'Rôle attribué',
                        'role_permissions_updated' => 'Permissions modifiées',
                        'measurement_created' => 'Mesure enregistrée',
                        'measurement_validated' => 'Mesure validée',
                    ][$action] ?? str($action)->replace('_', ' ')->title();
                @endphp
                <article class="admin-audit-row">
                    <span class="admin-audit-row-icon admin-audit-row-icon--{{ $tone }}"><i data-lucide="{{ $icon }}"></i></span>
                    <div class="admin-audit-row-main">
                        <div class="admin-audit-row-title">
                            <h3>{{ $actionLabel }}</h3>
                            <time datetime="{{ $log->created_at?->toIso8601String() }}">{{ $log->created_at?->diffForHumans() }}</time>
                        </div>
                        <p class="admin-audit-row-meta">
                            Par <strong>{{ $log->user_email ?: 'Système' }}</strong>
                            @if($log->resource_type)
                                <span class="admin-audit-resource">{{ $log->resource_type }}{{ $log->resource_id ? ' #' . $log->resource_id : '' }}</span>
                            @endif
                        </p>
                        @if(!empty($log->details))
                            <details class="admin-audit-details">
                                <summary>Voir les détails</summary>
                                <pre>{{ json_encode($log->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                            </details>
                        @endif
                        <div class="admin-audit-row-footer">
                            <span><i data-lucide="globe-2" aria-hidden="true"></i>{{ $log->ip_address ?: 'IP inconnue' }}</span>
                            <time datetime="{{ $log->created_at?->toIso8601String() }}">{{ $log->created_at?->format('d/m/Y à H:i:s') }}</time>
                        </div>
                    </div>
                </article>
            @empty
                <div class="admin-audit-empty">
                    <span><i data-lucide="inbox" aria-hidden="true"></i></span>
                    <h3>Aucun événement trouvé</h3>
                    <p>Aucun journal ne correspond à ces filtres. Essayez une autre recherche ou réinitialisez les filtres.</p>
                    @if(request()->filled('search') || request()->filled('action'))
                        <a href="{{ route('admin.logs') }}">Effacer les filtres</a>
                    @endif
                </div>
            @endforelse

            @if($logs->hasPages())
                <div class="admin-audit-pagination">{{ $logs->links() }}</div>
            @endif
        </section>
    </div>
</div>
@endsection
