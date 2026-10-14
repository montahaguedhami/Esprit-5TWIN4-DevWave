
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\ActionCorrectiveController;
use App\Http\Controllers\InterventionController;
use App\Http\Controllers\TechnicienController;
use App\Http\Controllers\TravauxController;
use App\Http\Controllers\Manager\MapController;
use App\Http\Controllers\Manager\PointMesureController;
use App\Http\Controllers\Manager\WaterQualityController;
use App\Http\Controllers\InfrastructureController;
use App\Http\Controllers\ZoneController;

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

<<<<<<< HEAD
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
=======
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    // Attempt authentication
    if (! \Illuminate\Support\Facades\Auth::attempt($credentials, false)) {
        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Email ou mot de passe incorrect.');
    }

    /** @var \App\Models\User $user */
    $user = \Illuminate\Support\Facades\Auth::user();

    // Check account active status
    if ($user->is_active === false) {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Votre compte a été désactivé par un administrateur.');
    }

    // Load roles eagerly
    $user->load('roles');
    $roleSlugs = $user->getRoleSlugs();

    // Block users with no assigned role (happens when DB is not seeded)
    if (empty($roleSlugs)) {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Aucun rôle assigné à ce compte. Contactez l\'administrateur.');
    }

    $request->session()->regenerate();
    $primaryRole = $user->primary_role;

    // Write role info into session for middlewares that rely on it
    session([
        'user' => [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'role'     => $primaryRole,
            'role_key' => $primaryRole,
            'roles'    => $roleSlugs,
        ],
    ]);

    \App\Models\AuditLog::record('user_login', 'User', $user->id, [
        'role' => $primaryRole,
        'ip'   => $request->ip(),
    ], $user);

    return redirect()->intended(route(\App\Models\Role::homeRoute($primaryRole)));
})->name('login.post');

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name'     => ['required', 'string', 'min:3', 'max:255'],
        'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:6', 'confirmed'],
        'terms'    => ['accepted'],
    ]);

    // Only citoyen accounts can be self-created.
    // administrateur, manager, technicien are created by the admin via /admin/users.
    $citoyenRole = \App\Models\Role::where('slug', \App\Models\Role::CITOYEN)->first();

    if (! $citoyenRole) {
        return back()
            ->withInput($request->only('name', 'email'))
            ->with('error', 'Le système n\'est pas encore configuré. Contactez l\'administrateur.');
    }

    $user = \App\Models\User::create([
        'name'      => $validated['name'],
        'email'     => $validated['email'],
        'password'  => $validated['password'],
        'is_active' => true,
    ]);

    $user->assignRole($citoyenRole);

    return redirect()->route('auth.login')
        ->with('success', 'Compte créé avec succès. Connectez-vous avec votre email et mot de passe.');
>>>>>>> user
})->name('register.post');

Route::post('/forgot-password', function (Request $request) {
    return back()->with('success', 'Si ce compte existe, un email a été envoyé.');
})->name('forgot-password.post');

// Logout
<<<<<<< HEAD
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

// Technician Space (BackOffice) - DEMO
Route::get('/technician/dashboard', function () {
    if (!session('user') || session('user.role') !== 'technician') {
        return redirect()->route('auth.login');
    }
    return view('technician.dashboard');
})->name('technician.dashboard');

Route::get('/technician/interventions', function () {
    if (!session('user') || session('user.role') !== 'technician') {
        return redirect()->route('auth.login');
    }
    return view('technician.interventions.index');
})->name('technician.interventions.index');

Route::get('/technician/interventions/{id}', function ($id) {
    if (!session('user') || session('user.role') !== 'technician') {
        return redirect()->route('auth.login');
    }
    return view('technician.interventions.show', ['id' => $id]);
})->name('technician.interventions.show');

Route::get('/technician/interventions/{id}/report', function ($id) {
    if (!session('user') || session('user.role') !== 'technician') {
        return redirect()->route('auth.login');
    }
    return view('technician.interventions.report', ['id' => $id]);
})->name('technician.interventions.report');

Route::post('/technician/interventions/{id}/report', function ($id) {
    return redirect()->route('technician.interventions.index')->with('success', 'Rapport d\'intervention créé avec succès');
})->name('technician.interventions.report.store');

Route::get('/technician/equipment', function () {
    if (!session('user') || session('user.role') !== 'technician') {
        return redirect()->route('auth.login');
    }
    return view('technician.equipment');
})->name('technician.equipment');

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
    return view('manager.dashboard');
})->name('manager.dashboard');
=======
Route::post('/logout', function (Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('landing');
})->name('logout');

Route::middleware('role:citoyen')->group(function () {
    Route::get('/citizen/dashboard', fn () => view('citizen.dashboard'))->name('citizen.dashboard');
    Route::get('/citizen/reports/create', fn () => view('citizen.reports.create'))->name('citizen.reports.create');
    Route::post('/citizen/reports', fn () => redirect()->route('citizen.dashboard')->with('success', 'Déclaration envoyée avec succès'))->name('citizen.reports.store');
    Route::get('/citizen/reports/{id}', fn ($id) => view('citizen.reports.show', ['id' => $id]))->name('citizen.reports.show');
    Route::get('/citizen/notifications', fn () => view('citizen.notifications'))->name('citizen.notifications');
    Route::get('/citizen/invoices', fn () => view('citizen.invoices.index'))->name('citizen.invoices.index');
    Route::get('/citizen/invoices/{id}', fn ($id) => view('citizen.invoices.show', ['id' => $id]))->name('citizen.invoices.show');
});

Route::middleware('role:technicien')->group(function () {
    Route::get('/technician/dashboard', fn () => view('technician.dashboard'))->name('technician.dashboard');
    Route::get('/technician/interventions', fn () => view('technician.interventions.index'))->name('technician.interventions.index');
    Route::get('/technician/interventions/{id}', fn ($id) => view('technician.interventions.show', ['id' => $id]))->name('technician.interventions.show');
    Route::get('/technician/interventions/{id}/report', fn ($id) => view('technician.interventions.report', ['id' => $id]))->name('technician.interventions.report');
    Route::post('/technician/interventions/{id}/report', fn ($id) => redirect()->route('technician.interventions.index')->with('success', 'Rapport d\'intervention créé avec succès'))->name('technician.interventions.report.store');
    Route::get('/technician/equipment', fn () => view('technician.equipment'))->name('technician.equipment');
});
>>>>>>> user



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

<<<<<<< HEAD
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

Route::get('/manager/quality', [WaterQualityController::class, 'index'])
    ->name('manager.quality');

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
=======
Route::middleware('role:manager')->group(function () {
    Route::get('/manager/dashboard', fn () => view('manager.dashboard'))->name('manager.dashboard');

    Route::get('/manager/budget', function () {
        $projets = \App\Models\Projet::with('financements')->get();
        $financements = \App\Models\Financement::with('projet')->latest()->get();
        $totalBudget = $projets->sum('budget');
        $totalFinance = $financements->sum('montant');
        $budgetRestant = $totalBudget - $totalFinance;
        $pourcentageUtilise = $totalBudget > 0 ? ($totalFinance / $totalBudget * 100) : 0;
        $sourcesUniques = $financements->pluck('source')->unique()->count();

        return view('manager.budget', compact('projets', 'financements', 'totalBudget', 'totalFinance', 'budgetRestant', 'pourcentageUtilise', 'sourcesUniques'));
    })->name('manager.budget');

    Route::get('/manager/analytics', fn () => view('manager.analytics'))->name('manager.analytics');
    Route::get('/manager/map', MapController::class)->name('manager.map');
    Route::get('/manager/quality', [WaterQualityController::class, 'index'])->name('manager.quality');

    Route::resource('/manager/points-mesure', PointMesureController::class)
        ->parameters(['points-mesure' => 'point_mesure'])
        ->names('manager.points-mesure');

    Route::resource('/manager/quality/mesures', WaterQualityController::class)
        ->parameters(['mesures' => 'mesure'])
        ->names('manager.quality.mesures')
        ->except('index');

    Route::post('/manager/quality/mesures/{mesure}/validate', [WaterQualityController::class, 'validateMeasurement'])
        ->name('manager.quality.mesures.validate');

    Route::get('/manager/teams', fn () => view('manager.teams'))->name('manager.teams');
    Route::get('/manager/reports', fn () => view('manager.reports'))->name('manager.reports');
});

Route::middleware('role:manager')->prefix('manager')->name('manager.')->group(function () {
>>>>>>> user
    Route::resource('zones', ZoneController::class);
    Route::resource('infrastructures', InfrastructureController::class);
});

<<<<<<< HEAD
// Admin Space (BackOffice) - DEMO
Route::get('/admin/dashboard', function () {
    if (!session('user') || session('user.role') !== 'admin') {
        return redirect()->route('auth.login');
    }
    return view('admin.dashboard');
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

Route::get('/admin/system', function () {
    if (!session('user') || session('user.role') !== 'admin') {
        return redirect()->route('auth.login');
    }
    return view('admin.system');
})->name('admin.system');

Route::get('/admin/logs', function () {
    if (!session('user') || session('user.role') !== 'admin') {
        return redirect()->route('auth.login');
    }
    return view('admin.logs');
})->name('admin.logs');

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

Route::get('/notifications', function () {
    if (!session('user')) {
        return redirect()->route('auth.login');
    }
    return view('notifications.index');
})->name('notifications.index');
=======
Route::middleware('role:administrateur')->group(function () {
    Route::get('/admin/dashboard', fn () => view('admin.dashboard'))->name('admin.dashboard');
    Route::get('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::patch('/admin/users/{user}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::delete('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/admin/roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('admin.roles');
    Route::put('/admin/roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'updatePermissions'])->name('admin.roles.update-permissions');
    Route::get('/admin/logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('admin.logs');
    Route::get('/admin/system', fn () => view('admin.system'))->name('admin.system');
    Route::get('/admin/security', fn () => view('admin.security'))->name('admin.security');
    Route::get('/admin/backups', fn () => view('admin.backups'))->name('admin.backups');
});

Route::middleware('role:administrateur,manager,technicien,citoyen')->group(function () {
    Route::get('/profile', fn () => view('profile.show'))->name('profile.show');
    Route::get('/settings', fn () => view('settings.index'))->name('settings.index');
    Route::get('/settings/notifications', fn () => view('settings.notifications'))->name('settings.notifications');
    Route::get('/notifications', fn () => view('notifications.index'))->name('notifications.index');
});
>>>>>>> user

// AI Assistant Demo (Public)
Route::get('/ai-demo', function () {
    return view('ai-demo');
})->name('ai.demo');

// Aurora Weather Forecast (Liquid Glass UI)
Route::get('/weather', function () {
    return response()->file(public_path('weather.html'));
})->name('weather');

// Module Maintenance (Back Office) : Techniciens & Interventions
<<<<<<< HEAD
Route::middleware('manager')->prefix('manager')->name('manager.')->group(function () {
=======
Route::middleware('role:manager')->prefix('manager')->name('manager.')->group(function () {
>>>>>>> user
    Route::resource('techniciens', TechnicienController::class);
    Route::resource('interventions', InterventionController::class);
});

<<<<<<< HEAD
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
=======
Route::middleware('role:citoyen')->group(function () {
    Route::get('/citizen/travaux', [TravauxController::class, 'index'])->name('citizen.travaux.index');
    Route::get('/citizen/travaux/{intervention}', [TravauxController::class, 'show'])->name('citizen.travaux.show');
    Route::prefix('citizen/projets')->name('citizen.projets.')->group(function () {
        Route::get('/', [App\Http\Controllers\Citizen\ProjetController::class, 'index'])->name('index');
        Route::get('/{projet}', [App\Http\Controllers\Citizen\ProjetController::class, 'show'])->name('show');
    });
});

Route::middleware('role:manager')->prefix('manager/projets')->name('manager.projets.')->group(function () {
>>>>>>> user
    Route::get('/', [App\Http\Controllers\Manager\ProjetController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Manager\ProjetController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Manager\ProjetController::class, 'store'])->name('store');
    Route::get('/map', [App\Http\Controllers\Manager\ProjetController::class, 'map'])->name('map');
    Route::get('/{projet}', [App\Http\Controllers\Manager\ProjetController::class, 'show'])->name('show');
    Route::get('/{projet}/edit', [App\Http\Controllers\Manager\ProjetController::class, 'edit'])->name('edit');
    Route::put('/{projet}', [App\Http\Controllers\Manager\ProjetController::class, 'update'])->name('update');
    Route::delete('/{projet}', [App\Http\Controllers\Manager\ProjetController::class, 'destroy'])->name('destroy');
});

<<<<<<< HEAD
// Manager - Financements CRUD
Route::prefix('manager/financements')->name('manager.financements.')->group(function () {
=======
Route::middleware('role:manager')->prefix('manager/financements')->name('manager.financements.')->group(function () {
>>>>>>> user
    Route::get('/', [App\Http\Controllers\Manager\FinancementController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Manager\FinancementController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Manager\FinancementController::class, 'store'])->name('store');
    Route::get('/{financement}', [App\Http\Controllers\Manager\FinancementController::class, 'show'])->name('show');
    Route::get('/{financement}/edit', [App\Http\Controllers\Manager\FinancementController::class, 'edit'])->name('edit');
    Route::put('/{financement}', [App\Http\Controllers\Manager\FinancementController::class, 'update'])->name('update');
    Route::delete('/{financement}', [App\Http\Controllers\Manager\FinancementController::class, 'destroy'])->name('destroy');
});
