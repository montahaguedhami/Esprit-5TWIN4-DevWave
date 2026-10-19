<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\ActionCorrectiveController;
use App\Http\Controllers\InterventionController;
use App\Http\Controllers\InterventionIaController;
use App\Http\Controllers\MaintenanceDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\TechnicienController;
use App\Http\Controllers\TravauxController;
use App\Http\Controllers\Manager\MapController;
use App\Http\Controllers\Manager\PointMesureController;
use App\Http\Controllers\Manager\WaterQualityController;
use App\Http\Controllers\InfrastructureController;
use App\Http\Controllers\Manager\InventaireExportController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\InfrastructureRiskController;
use App\Http\Controllers\Admin\InfrastructureRiskSettingsController;

// Landing Page (Public)
Route::get('/', function () {
    return view('landing');
})->name('landing');

// Auth routes GET
Route::get('/login', function () {
    return view('auth.login');
})->name('auth.login');

Route::get('/register', function () {
    return view('auth.register');
})->name('auth.register');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('auth.forgot-password');

// Auth routes POST (DEMO - connexion simulée)
Route::post('/login', function (Request $request) {
    $email = $request->input('email');
    
    // Comptes démo
    $demoAccounts = [
        'citoyen@aquasecure.tn' => ['role' => 'citizen', 'name' => 'Yassine Hamdi'],
        'amira@aquasecure.tn' => ['role' => 'technician', 'name' => 'Amira Ben Ali'],
        'gestionnaire@aquasecure.tn' => ['role' => 'manager', 'name' => 'Ines Mansouri'],
        'admin@aquasecure.tn' => ['role' => 'admin', 'name' => 'Amina Kacem'],
    ];
    
    if (isset($demoAccounts[$email])) {
        $user = $demoAccounts[$email];
        session([
            'user' => [
                'name' => $user['name'],
                'email' => $email,
                'role' => $user['role'],
            ]
        ]);
        
        // Rediriger selon le rôle
        switch ($user['role']) {
            case 'citizen':
                return redirect()->route('citizen.dashboard');
            case 'technician':
                return redirect()->route('technician.dashboard');
            case 'manager':
                return redirect()->route('manager.dashboard');
            case 'admin':
                return redirect()->route('admin.dashboard');
            default:
                return redirect()->route('landing');
        }
    }
    
    return back()->with('error', 'Email ou mot de passe incorrect');
})->name('login.post');

Route::post('/register', function (Request $request) {
    // Simuler une inscription réussie
    return redirect()->route('auth.login')->with('success', 'Compte créé avec succès ! Connectez-vous.');
})->name('register.post');

Route::post('/forgot-password', function (Request $request) {
    return back()->with('success', 'Si ce compte existe, un email a été envoyé.');
})->name('forgot-password.post');

// Logout
Route::post('/logout', function () {
    session()->forget('user');
    return redirect()->route('landing');
})->name('logout');

// Citizen Space (FrontOffice) - DEMO
Route::get('/citizen/dashboard', function () {
    if (!session('user') || session('user.role') !== 'citizen') {
        return redirect()->route('auth.login');
    }
    return view('citizen.dashboard');
})->name('citizen.dashboard');

Route::get('/citizen/reports/create', function () {
    if (!session('user') || session('user.role') !== 'citizen') {
        return redirect()->route('auth.login');
    }
    return view('citizen.reports.create');
})->name('citizen.reports.create');

Route::post('/citizen/reports', function () {
    return redirect()->route('citizen.dashboard')->with('success', 'Déclaration envoyée avec succès');
})->name('citizen.reports.store');

Route::get('/citizen/reports/{id}', function ($id) {
    if (!session('user') || session('user.role') !== 'citizen') {
        return redirect()->route('auth.login');
    }
    return view('citizen.reports.show', ['id' => $id]);
})->name('citizen.reports.show');

Route::get('/citizen/notifications', function () {
    if (!session('user') || session('user.role') !== 'citizen') {
        return redirect()->route('auth.login');
    }
    return view('citizen.notifications');
})->name('citizen.notifications');

Route::get('/citizen/invoices', function () {
    if (!session('user') || session('user.role') !== 'citizen') {
        return redirect()->route('auth.login');
    }
    return view('citizen.invoices.index');
})->name('citizen.invoices.index');

Route::get('/citizen/invoices/{id}', function ($id) {
    if (!session('user') || session('user.role') !== 'citizen') {
        return redirect()->route('auth.login');
    }
    return view('citizen.invoices.show', ['id' => $id]);
})->name('citizen.invoices.show');

// Citizen network directory (read-only, public service information only)
Route::middleware('role:citizen')->prefix('citizen')->name('citizen.')->group(function () {
    Route::get('/zones', [\App\Http\Controllers\Citizen\ZoneController::class, 'index'])->name('zones.index');
    Route::get('/zones/{zone}', [\App\Http\Controllers\Citizen\ZoneController::class, 'show'])->name('zones.show');
    Route::get('/infrastructures', [\App\Http\Controllers\Citizen\InfrastructureController::class, 'index'])->name('infrastructures.index');
    Route::get('/infrastructures/{infrastructure}', [\App\Http\Controllers\Citizen\InfrastructureController::class, 'show'])->name('infrastructures.show');
});

// Technician Space (Terrain & Maintenance)
Route::middleware('role:technician')->prefix('technician')->name('technician.')->group(function () {
    Route::get('/dashboard', function () {
        return view('technician.dashboard');
    })->name('dashboard');

    // Zones d'intervention du technicien
    Route::get('/zones', [\App\Http\Controllers\Technician\ZoneController::class, 'index'])->name('zones.index');
    Route::get('/zones/{zone}', [\App\Http\Controllers\Technician\ZoneController::class, 'show'])->name('zones.show');

    // Infrastructures assignées au technicien (accès restreint à ses interventions)
    Route::get('/infrastructures', [\App\Http\Controllers\Technician\InfrastructureController::class, 'index'])->name('infrastructures.index');
    Route::get('/infrastructures/{infrastructure}', [\App\Http\Controllers\Technician\InfrastructureController::class, 'show'])->name('infrastructures.show');
    Route::patch('/infrastructures/{infrastructure}/etat', [\App\Http\Controllers\Technician\InfrastructureController::class, 'updateEtat'])->name('infrastructures.etat');

    // QR Code et Scanner terrain
    Route::get('/scan', [\App\Http\Controllers\Technician\InfrastructureController::class, 'scan'])->name('scan');
    Route::post('/scan', [\App\Http\Controllers\Technician\InfrastructureController::class, 'processScan'])->name('scan.process');

    // Tournée du jour et guidage géographique
    Route::get('/tournee', [\App\Http\Controllers\Technician\TourneeController::class, 'index'])->name('tournee');

    // Signalement d'incident terrain relié à un ouvrage
    Route::get('/incidents/create', [\App\Http\Controllers\Technician\IncidentController::class, 'create'])->name('incidents.create');
    Route::post('/incidents', [\App\Http\Controllers\Technician\IncidentController::class, 'store'])->name('incidents.store');

    // Interventions réelles du technicien
    Route::get('/interventions', [\App\Http\Controllers\Technician\InterventionController::class, 'index'])->name('interventions.index');
    Route::get('/interventions/{id}', [\App\Http\Controllers\Technician\InterventionController::class, 'show'])->name('interventions.show');
    Route::post('/interventions/{id}/start', [\App\Http\Controllers\Technician\InterventionController::class, 'start'])->name('interventions.start');
    Route::get('/interventions/{id}/report', [\App\Http\Controllers\Technician\InterventionController::class, 'report'])->name('interventions.report');
    Route::post('/interventions/{id}/report', [\App\Http\Controllers\Technician\InterventionController::class, 'storeReport'])->name('interventions.report.store');
    Route::post('/interventions/{id}/report/ia', [\App\Http\Controllers\Technician\InterventionController::class, 'assistantRapport'])->name('interventions.report.ia');

    Route::get('/equipment', function () {
        return view('technician.equipment');
    })->name('equipment');
});

// Role Switcher Route (Instant DEMO role switching)
Route::get('/switch-role/{role}', function ($role) {
    $rolesMap = [
        'admin'      => ['name' => 'Amina Kacem (System Admin)', 'email' => 'admin@aquasecure.tn', 'role' => 'admin', 'route' => 'admin.dashboard'],
        'manager'    => ['name' => 'Ines Mansouri (Utility Manager)', 'email' => 'gestionnaire@aquasecure.tn', 'role' => 'manager', 'route' => 'manager.dashboard'],
        'quality'    => ['name' => 'Dr. Selim Dridi (Water Quality Specialist)', 'email' => 'quality@aquasecure.tn', 'role' => 'manager', 'route' => 'manager.quality'],
        'technician' => ['name' => 'Amira Ben Ali (Field Technician)', 'email' => 'amira@aquasecure.tn', 'role' => 'technician', 'route' => 'technician.dashboard'],
        'finance'    => ['name' => 'Moncef Triki (Finance Manager)', 'email' => 'finance@aquasecure.tn', 'role' => 'manager', 'route' => 'manager.budget'],
        'citizen'    => ['name' => 'Yassine Hamdi (Resident Citizen)', 'email' => 'citoyen@aquasecure.tn', 'role' => 'citizen', 'route' => 'citizen.dashboard'],
    ];

    if (isset($rolesMap[$role])) {
        $user = $rolesMap[$role];
        session(['user' => [
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'role_key' => $role
        ]]);
        return redirect()->route($user['route'])->with('success', 'Passé au rôle : ' . $user['name']);
    }
    return redirect()->back();
})->name('switch.role');

// Manager Space (BackOffice) - DEMO
Route::get('/manager/dashboard', function () {
    if (!session('user')) {
        session(['user' => ['name' => 'Ines Mansouri', 'email' => 'gestionnaire@aquasecure.tn', 'role' => 'manager', 'role_key' => 'manager']]);
    }
    $riskTop5 = \App\Models\Infrastructure::query()
        ->with(['latestRiskPrediction', 'zone'])
        ->get()
        ->filter(fn ($infrastructure) => $infrastructure->latestRiskPrediction !== null)
        ->sortByDesc(fn ($infrastructure) => $infrastructure->latestRiskPrediction->probability)
        ->take(5)
        ->values();
    $riskByZone = \App\Models\Infrastructure::query()
        ->with(['latestRiskPrediction', 'zone'])
        ->get()
        ->filter(fn ($infrastructure) => $infrastructure->latestRiskPrediction !== null)
        ->groupBy('zone_id')
        ->map(fn ($items) => [
            'zone' => $items->first()->zone?->nom ?? 'Zone inconnue',
            'average' => (int) round($items->avg(fn ($infrastructure) => $infrastructure->latestRiskPrediction->probability)),
        ])
        ->values();

    return view('manager.dashboard', compact('riskTop5', 'riskByZone'));
})->name('manager.dashboard');



Route::middleware(\App\Http\Middleware\IncidentRole::class . ':citizen')->group(function () {
    Route::get('/incidents', [IncidentController::class, 'index'])->name('incidents.index');
    Route::get('/incidents/create', [IncidentController::class, 'create'])->name('incidents.create');
    Route::post('/incidents', [IncidentController::class, 'store'])->name('incidents.store');
    Route::get('/incidents/{incident}/edit', [IncidentController::class, 'edit'])->name('incidents.edit');
    Route::put('/incidents/{incident}', [IncidentController::class, 'update'])->name('incidents.update');
    Route::delete('/incidents/{incident}', [IncidentController::class, 'destroy'])->name('incidents.destroy');
    Route::get('/incidents/{incident}', [IncidentController::class, 'show'])->name('incidents.show');
});

Route::middleware(\App\Http\Middleware\IncidentRole::class . ':manager')->group(function () {
    Route::get('/manager/incidents', [IncidentController::class, 'managerIndex'])->name('manager.incidents');
    Route::get('/manager/incidents/{incident}', [IncidentController::class, 'managerShow'])->name('manager.incidents.show');
    Route::get('/manager/incidents/{incident}/actions', [ActionCorrectiveController::class, 'index'])->name('manager.actions.index');
    Route::get('/manager/incidents/{incident}/actions/create', [ActionCorrectiveController::class, 'create'])->name('manager.actions.create');
    Route::post('/manager/incidents/{incident}/actions', [ActionCorrectiveController::class, 'store'])->name('incidents.actions.store');
    Route::get('/manager/actions/{action}/edit', [ActionCorrectiveController::class, 'edit'])->name('manager.actions.edit');
    Route::get('/manager/actions/{action}', [ActionCorrectiveController::class, 'show'])->name('manager.actions.show');
    Route::put('/manager/actions/{action}', [ActionCorrectiveController::class, 'update'])->name('manager.actions.update');
    Route::delete('/manager/actions/{action}', [ActionCorrectiveController::class, 'destroy'])->name('manager.actions.destroy');
});

// Route démo supprimée - utiliser /manager/projets (module Ghada) à la place

Route::get('/manager/budget', function () {
    if (!session('user')) {
        session(['user' => ['name' => 'Moncef Triki', 'email' => 'finance@aquasecure.tn', 'role' => 'manager', 'role_key' => 'finance']]);
    }
    
    // Récupérer les projets et financements depuis la base de données
    $projets = \App\Models\Projet::with('financements')->get();
    $financements = \App\Models\Financement::with('projet')->latest()->get();
    
    // Calculer les totaux
    $totalBudget = $projets->sum('budget');
    $totalFinance = $financements->sum('montant');
    $budgetRestant = $totalBudget - $totalFinance;
    $pourcentageUtilise = $totalBudget > 0 ? ($totalFinance / $totalBudget * 100) : 0;
    
    // Compter les sources de financement uniques
    $sourcesUniques = $financements->pluck('source')->unique()->count();
    
    return view('manager.budget', compact('projets', 'financements', 'totalBudget', 'totalFinance', 'budgetRestant', 'pourcentageUtilise', 'sourcesUniques'));
})->name('manager.budget');

Route::get('/manager/analytics', function () {
    if (!session('user')) {
        session(['user' => ['name' => 'Ines Mansouri', 'email' => 'gestionnaire@aquasecure.tn', 'role' => 'manager', 'role_key' => 'manager']]);
    }
    return view('manager.analytics');
})->name('manager.analytics');

Route::get('/manager/map', MapController::class)->name('manager.map');

    Route::get('/manager/analytics', fn () => view('manager.analytics'))->name('manager.analytics');
    Route::get('/manager/map', MapController::class)->name('manager.map');
    Route::get('/manager/quality', [WaterQualityController::class, 'index'])->name('manager.quality');
    Route::delete('/manager/quality/mesures', [WaterQualityController::class, 'bulkDestroy'])->name('manager.quality.mesures.bulk-destroy');

Route::resource('/manager/points-mesure', PointMesureController::class)
    ->parameters(['points-mesure' => 'point_mesure'])
    ->names('manager.points-mesure');

Route::resource('/manager/quality/mesures', WaterQualityController::class)
    ->parameters(['mesures' => 'mesure'])
    ->names('manager.quality.mesures')
    ->except('index');

Route::get('/manager/teams', function () {
    if (!session('user')) {
        session(['user' => ['name' => 'Ines Mansouri', 'email' => 'gestionnaire@aquasecure.tn', 'role' => 'manager', 'role_key' => 'manager']]);
    }
    return view('manager.teams');
})->name('manager.teams');

Route::get('/manager/reports', function () {
    if (!session('user')) {
        session(['user' => ['name' => 'Ines Mansouri', 'email' => 'gestionnaire@aquasecure.tn', 'role' => 'manager', 'role_key' => 'manager']]);
    }
    return view('manager.reports');
})->name('manager.reports');

Route::middleware('manager')->prefix('manager')->name('manager.')->group(function () {
    // Exports d'inventaire
    Route::get('zones/export/csv', [InventaireExportController::class, 'exportZonesCsv'])->name('zones.export.csv');
    Route::get('zones/{zone}/export/pdf', [InventaireExportController::class, 'exportZonePdf'])->name('zones.export.pdf');
    Route::get('infrastructures/export/csv', [InventaireExportController::class, 'exportInfrastructuresCsv'])->name('infrastructures.export.csv');
    Route::get('infrastructures/{infrastructure}/export/pdf', [InventaireExportController::class, 'exportInfrastructurePdf'])->name('infrastructures.export.pdf');

    Route::resource('zones', ZoneController::class);
    Route::patch('infrastructures/{infrastructure}/etat', [InfrastructureController::class, 'updateEtat'])->name('infrastructures.etat');
    Route::get('infrastructures/{infrastructure}/risk', [InfrastructureRiskController::class, 'show'])->name('infrastructures.risk.show');
    Route::post('infrastructures/{infrastructure}/risk/recompute', [InfrastructureRiskController::class, 'recompute'])->name('infrastructures.risk.recompute');
    Route::post('infrastructures/risk/recompute-all', [InfrastructureRiskController::class, 'recomputeAll'])->name('infrastructures.risk.recompute-all');
    Route::patch('infrastructures/alertes/{alerte}/resoudre', [InfrastructureController::class, 'resoudreAlerte'])->name('infrastructures.alertes.resoudre');
    Route::resource('infrastructures', InfrastructureController::class);
});

Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('map', MapController::class)->name('map');
    Route::get('zones/export/csv', [InventaireExportController::class, 'exportZonesCsv'])->name('zones.export.csv');
    Route::get('zones/{zone}/export/pdf', [InventaireExportController::class, 'exportZonePdf'])->name('zones.export.pdf');
    Route::get('infrastructures/export/csv', [InventaireExportController::class, 'exportInfrastructuresCsv'])->name('infrastructures.export.csv');
    Route::get('infrastructures/{infrastructure}/export/pdf', [InventaireExportController::class, 'exportInfrastructurePdf'])->name('infrastructures.export.pdf');
    Route::resource('zones', ZoneController::class);
    Route::patch('infrastructures/{infrastructure}/etat', [InfrastructureController::class, 'updateEtat'])->name('infrastructures.etat');
    Route::get('infrastructures/{infrastructure}/risk', [InfrastructureRiskController::class, 'show'])->name('infrastructures.risk.show');
    Route::post('infrastructures/{infrastructure}/risk/recompute', [InfrastructureRiskController::class, 'recompute'])->name('infrastructures.risk.recompute');
    Route::post('infrastructures/risk/recompute-all', [InfrastructureRiskController::class, 'recomputeAll'])->name('infrastructures.risk.recompute-all');
    Route::patch('infrastructures/alertes/{alerte}/resoudre', [InfrastructureController::class, 'resoudreAlerte'])->name('infrastructures.alertes.resoudre');
    Route::resource('infrastructures', InfrastructureController::class);
});

Route::middleware('role:technicien')->prefix('technician')->name('technician.')->group(function () {
    Route::get('/zones', [\App\Http\Controllers\Technician\ZoneController::class, 'index'])->name('zones.index');
    Route::get('/zones/{zone}', [\App\Http\Controllers\Technician\ZoneController::class, 'show'])->name('zones.show');
    Route::get('/zones/{zone}/mesures/create', [\App\Http\Controllers\Technician\WaterQualityController::class, 'create'])->name('quality.create');
    Route::post('/zones/{zone}/mesures', [\App\Http\Controllers\Technician\WaterQualityController::class, 'store'])->name('quality.store');
    Route::get('/infrastructures', [\App\Http\Controllers\Technician\InfrastructureController::class, 'index'])->name('infrastructures.index');
    Route::get('/infrastructures/{infrastructure}', [\App\Http\Controllers\Technician\InfrastructureController::class, 'show'])->name('infrastructures.show');
    Route::patch('/infrastructures/{infrastructure}/etat', [\App\Http\Controllers\Technician\InfrastructureController::class, 'updateEtat'])->name('infrastructures.etat');
    Route::get('/scan', [\App\Http\Controllers\Technician\InfrastructureController::class, 'scan'])->name('scan');
    Route::post('/scan', [\App\Http\Controllers\Technician\InfrastructureController::class, 'processScan'])->name('scan.process');
    Route::get('/tournee', [\App\Http\Controllers\Technician\TourneeController::class, 'index'])->name('tournee');
    Route::get('/incidents/create', [\App\Http\Controllers\Technician\IncidentController::class, 'create'])->name('incidents.create');
    Route::post('/incidents', [\App\Http\Controllers\Technician\IncidentController::class, 'store'])->name('incidents.store');
});

// Admin Space (BackOffice) - DEMO
Route::get('/admin/dashboard', function () {
    if (!session('user') || session('user.role') !== 'admin') {
        return redirect()->route('auth.login');
    }
    $riskDistribution = app(InfrastructureRiskSettingsController::class)->distribution();

    return view('admin.dashboard', compact('riskDistribution'));
})->name('admin.dashboard');

Route::get('/admin/users', function () {
    if (!session('user') || session('user.role') !== 'admin') {
        return redirect()->route('auth.login');
    }
    return view('admin.users.index');
})->name('admin.users.index');

Route::get('/admin/roles', function () {
    if (!session('user') || session('user.role') !== 'admin') {
        return redirect()->route('auth.login');
    }
    return view('admin.roles');
})->name('admin.roles');

// ── Administrateur ────────────────────────────────────────────────────────────
Route::middleware('role:administrateur')->group(function () {
    Route::get('/admin/settings/infrastructure-risk', [InfrastructureRiskSettingsController::class, 'index'])->name('admin.settings.infrastructure-risk');
    Route::put('/admin/settings/infrastructure-risk', [InfrastructureRiskSettingsController::class, 'update'])->name('admin.settings.infrastructure-risk.update');
    Route::post('/admin/settings/infrastructure-risk/recompute', [InfrastructureRiskSettingsController::class, 'recomputeAll'])->name('admin.settings.infrastructure-risk.recompute');
    Route::get('/admin/dashboard', function () {
        $riskDistribution = app(InfrastructureRiskSettingsController::class)->distribution();

        return view('admin.dashboard', compact('riskDistribution'));
    })->name('admin.dashboard');
    Route::get('/admin/users',       [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users',      [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
    Route::put('/admin/users/{user}',[\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::patch('/admin/users/{user}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::delete('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/admin/logs',        [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('admin.logs');
    Route::get('/admin/system', function () {
        $metrics = \App\Data\PlaceholderData::adminSystemMetrics();
        $cutoff = now()->subMinutes(15)->timestamp;

        if (config('session.driver') === 'database') {
            $metrics['active_sessions'] = \Illuminate\Support\Facades\DB::table(config('session.table', 'sessions'))
                ->where('last_activity', '>=', $cutoff)
                ->count();
        } elseif (config('session.driver') === 'file') {
            $metrics['active_sessions'] = collect(\Illuminate\Support\Facades\File::files(config('session.files')))
                ->filter(fn ($file) => $file->getMTime() >= $cutoff)
                ->count();
        }

        return view('admin.system', compact('metrics'));
    })->name('admin.system');
    Route::get('/admin/security',    fn () => view('admin.security'))->name('admin.security');
    Route::post('/admin/security/renew-sessions', [\App\Http\Controllers\Settings\SessionController::class, 'forceRenewAll'])->name('admin.security.renew-sessions');
    Route::get('/admin/backups',     fn () => view('admin.backups'))->name('admin.backups');
});

// ── All authenticated roles ───────────────────────────────────────────────────
Route::middleware('role:administrateur,manager,technicien,citoyen')->group(function () {
    Route::get('/profile',                fn () => view('profile.show'))->name('profile.show');
    Route::get('/settings',               [\App\Http\Controllers\Settings\SessionController::class, 'index'])->name('settings.index');
    Route::delete('/settings/sessions/{sessionId}', [\App\Http\Controllers\Settings\SessionController::class, 'revoke'])->name('settings.sessions.revoke');
    Route::post('/settings/sessions/revoke-others', [\App\Http\Controllers\Settings\SessionController::class, 'revokeOthers'])->name('settings.sessions.revoke-others');
    Route::get('/settings/notifications', fn () => view('settings.notifications'))->name('settings.notifications');
    Route::get('/notifications',             [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/tout-lire',  [NotificationController::class, 'toutLire'])->name('notifications.tout-lire');
    Route::get('/notifications/{id}/ouvrir', [NotificationController::class, 'ouvrir'])->name('notifications.ouvrir');
});

Route::get('/admin/security', function () {
    if (!session('user') || session('user.role') !== 'admin') {
        return redirect()->route('auth.login');
    }
    return view('admin.security');
})->name('admin.security');

Route::get('/admin/backups', function () {
    if (!session('user') || session('user.role') !== 'admin') {
        return redirect()->route('auth.login');
    }
    return view('admin.backups');
})->name('admin.backups');

// Global Routes (All Roles)
Route::get('/profile', function () {
    if (!session('user')) {
        return redirect()->route('auth.login');
    }
    return view('profile.show');
})->name('profile.show');

Route::get('/settings', function () {
    if (!session('user')) {
        return redirect()->route('auth.login');
    }
    return view('settings.index');
})->name('settings.index');

Route::get('/settings/notifications', function () {
    if (!session('user')) {
        return redirect()->route('auth.login');
    }
    return view('settings.notifications');
})->name('settings.notifications');

// AI Assistant Demo (Public)
Route::get('/ai-demo', function () {
    return view('ai-demo');
})->name('ai.demo');

// Aurora Weather Forecast (Liquid Glass UI)
Route::get('/weather', function () {
    return response()->file(public_path('weather.html'));
})->name('weather');

// Module Maintenance (Back Office) : Techniciens & Interventions
Route::middleware('manager')->prefix('manager')->name('manager.')->group(function () {
    Route::resource('techniciens', TechnicienController::class);
    Route::resource('interventions', InterventionController::class);
    Route::get('interventions/{intervention}/pdf', [InterventionController::class, 'pdf'])->name('interventions.pdf');
    Route::post('interventions/recommandation-ia', [InterventionIaController::class, 'recommander'])->name('interventions.recommandation');

    // Statistiques et rapport mensuel du module Maintenance
    Route::get('maintenance/statistiques',    [MaintenanceDashboardController::class, 'index'])->name('maintenance.statistiques');
    Route::get('maintenance/rapport-mensuel', [MaintenanceDashboardController::class, 'rapportMensuel'])->name('maintenance.rapport-mensuel');

    // Planning des interventions (calendrier + détection de conflits)
    Route::get('planning',                              [PlanningController::class, 'index'])->name('planning.index');
    Route::get('planning/evenements',                   [PlanningController::class, 'evenements'])->name('planning.evenements');
    Route::patch('planning/interventions/{intervention}', [PlanningController::class, 'deplacer'])->name('planning.deplacer');
    Route::post('planning/preventive',                  [PlanningController::class, 'preventive'])->name('planning.preventive');
});

// Module Maintenance (Front Office) : travaux visibles par les citoyens
Route::get('/citizen/travaux', [TravauxController::class, 'index'])->name('citizen.travaux.index');
Route::get('/citizen/travaux/{intervention}', [TravauxController::class, 'show'])->name('citizen.travaux.show');

// === MODULE PROJETS/FINANCEMENTS (Ghada) ===

// Citizen - Projets (Lecture seule)
Route::prefix('citizen/projets')->name('citizen.projets.')->group(function () {
    Route::get('/', [App\Http\Controllers\Citizen\ProjetController::class, 'index'])->name('index');
    Route::get('/{projet}', [App\Http\Controllers\Citizen\ProjetController::class, 'show'])->name('show');
});

// Manager - Projets CRUD
Route::prefix('manager/projets')->name('manager.projets.')->group(function () {
    Route::get('/', [App\Http\Controllers\Manager\ProjetController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Manager\ProjetController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Manager\ProjetController::class, 'store'])->name('store');
    Route::get('/map', [App\Http\Controllers\Manager\ProjetController::class, 'map'])->name('map');
    Route::get('/{projet}', [App\Http\Controllers\Manager\ProjetController::class, 'show'])->name('show');
    Route::get('/{projet}/edit', [App\Http\Controllers\Manager\ProjetController::class, 'edit'])->name('edit');
    Route::put('/{projet}', [App\Http\Controllers\Manager\ProjetController::class, 'update'])->name('update');
    Route::delete('/{projet}', [App\Http\Controllers\Manager\ProjetController::class, 'destroy'])->name('destroy');
});

// Manager - Financements CRUD
Route::prefix('manager/financements')->name('manager.financements.')->group(function () {
    Route::get('/', [App\Http\Controllers\Manager\FinancementController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Manager\FinancementController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Manager\FinancementController::class, 'store'])->name('store');
    Route::get('/{financement}', [App\Http\Controllers\Manager\FinancementController::class, 'show'])->name('show');
    Route::get('/{financement}/edit', [App\Http\Controllers\Manager\FinancementController::class, 'edit'])->name('edit');
    Route::put('/{financement}',   [App\Http\Controllers\Manager\FinancementController::class, 'update'])->name('update');
    Route::delete('/{financement}',[App\Http\Controllers\Manager\FinancementController::class, 'destroy'])->name('destroy');
});

Route::middleware('role:manager')->group(function () {
    Route::post('/manager/data-collection/trigger',     [\App\Http\Controllers\Manager\DataCollectionController::class, 'trigger'])->name('manager.data-collection.trigger');
    Route::post('/manager/data-collection/import-csv',  [\App\Http\Controllers\Manager\DataCollectionController::class, 'importCsv'])->name('manager.data-collection.import-csv');
    Route::get('/manager/data-collection/template-csv', [\App\Http\Controllers\Manager\DataCollectionController::class, 'downloadTemplateCsv'])->name('manager.data-collection.template-csv');
    Route::get('/manager/data-collection/status',       [\App\Http\Controllers\Manager\DataCollectionController::class, 'status'])->name('manager.data-collection.status');
    Route::get('/manager/data-collection/history',      [\App\Http\Controllers\Manager\DataCollectionController::class, 'history'])->name('manager.data-collection.history');
    Route::get('/manager/quality/{point}/analyse',      [\App\Http\Controllers\Manager\DataCollectionController::class, 'analysePoint'])->name('manager.quality.analyse');
});
