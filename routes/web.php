<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\ActionCorrectiveController;

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

Route::get('/manager/map', function () {
    if (!session('user')) {
        session(['user' => ['name' => 'Ines Mansouri', 'email' => 'gestionnaire@aquasecure.tn', 'role' => 'manager', 'role_key' => 'manager']]);
    }
    return view('manager.map');
})->name('manager.map');

Route::get('/manager/quality', function () {
    if (!session('user')) {
        session(['user' => ['name' => 'Dr. Selim Dridi', 'email' => 'quality@aquasecure.tn', 'role' => 'manager', 'role_key' => 'quality']]);
    }
    return view('manager.quality');
})->name('manager.quality');

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

Route::get('/manager/projects', function () {
    if (!session('user')) {
        session(['user' => ['name' => 'Ines Mansouri', 'email' => 'gestionnaire@aquasecure.tn', 'role' => 'manager', 'role_key' => 'manager']]);
    }
    return view('manager.projects');
})->name('manager.projects');

Route::get('/manager/budget', function () {
    if (!session('user')) {
        session(['user' => ['name' => 'Moncef Triki', 'email' => 'finance@aquasecure.tn', 'role' => 'manager', 'role_key' => 'finance']]);
    }
    return view('manager.budget');
})->name('manager.budget');

Route::get('/manager/analytics', function () {
    if (!session('user')) {
        session(['user' => ['name' => 'Ines Mansouri', 'email' => 'gestionnaire@aquasecure.tn', 'role' => 'manager', 'role_key' => 'manager']]);
    }
    return view('manager.analytics');
})->name('manager.analytics');

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

// AI Assistant Demo (Public)
Route::get('/ai-demo', function () {
    return view('ai-demo');
})->name('ai.demo');

// Aurora Weather Forecast (Liquid Glass UI)
Route::get('/weather', function () {
    return response()->file(public_path('weather.html'));
})->name('weather');

