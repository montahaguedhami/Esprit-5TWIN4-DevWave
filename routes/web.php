
<?php

use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Password;
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
use App\Http\Controllers\Manager\InventaireExportController;
use App\Http\Controllers\ZoneController;

// Landing Page (Public)
Route::get('/', function () {
    return view('landing');
})->name('landing');

// Auth routes — GET
Route::get('/login',           fn () => view('auth.login'))->name('auth.login');
Route::get('/register',        fn () => view('auth.register'))->name('auth.register');
Route::get('/forgot-password', fn () => view('auth.forgot-password'))->name('auth.forgot-password');

// Login
Route::post('/login', function (Request $request) {
    // Brute-force guard, checked before validation and before any credential
    // check: a locked identifier must keep being refused whether the password
    // it sends is right or wrong. 5 failures -> 30 s lockout, server side.
    $limiter = app(\App\Services\LoginRateLimiter::class);
    $identifier = (string) $request->input('email');
    $ip = (string) $request->ip();

    if ($limiter->isBlocked($identifier, $ip)) {
        return back()
            ->withInput($request->only('email'))
            ->with('error', $limiter->message($identifier, $ip))
            ->with('retry_after', $limiter->secondsRemaining($identifier, $ip));
    }

    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (! \Illuminate\Support\Facades\Auth::attempt($credentials, false)) {
        // Counted against this account and against the source IP: the second
        // counter absorbs a spray of many addresses tried from one host.
        $limiter->hit($identifier, $ip);

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Email ou mot de passe incorrect.');
    }

    /** @var \App\Models\User $user */
    $user = \Illuminate\Support\Facades\Auth::user();

    if ($user->is_active === false) {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Votre compte a été désactivé par un administrateur.');
    }

    $user->load('roles');
    $roleSlugs = $user->getRoleSlugs();

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

    // The credentials were right: the failure counter must not follow the
    // account into the next session, otherwise one success still leaves the
    // user one attempt away from a lockout.
    $limiter->clear($identifier, $ip);

    return redirect()->intended(route(\App\Models\Role::homeRoute($primaryRole)));
})->name('login.post');

// Passwords that are rejected outright, whatever else they contain. The last
// entries satisfy every mechanical rule (length, case, digit, symbol) and are
// still refused because they are among the most used passwords in the wild:
// that is exactly what a common-password check is for.
$commonPasswords = [
    'password', 'password1', 'password123', '123456', '12345678', '123456789',
    'qwerty', 'azerty', 'iloveyou', 'admin', 'welcome', 'letmein', 'monkey',
    'dragon', 'sunshine', 'princess', 'football', 'abc123', 'motdepasse',
    'motdepasse123', 'azertyuiop', 'qwertyuiop', 'aquasecure', 'citoyen',
    'password123!', 'password1234!', 'welcome12345!', 'qwerty12345!',
    'azerty12345!', 'admin12345!', 'letmein12345!', 'motdepasse1234!',
];

// Register (citoyen only — other roles are created by admin)
Route::post('/register', function (Request $request) use ($commonPasswords) {
    // Phone is collected by the form but not persisted yet (no users.phone
    // column), so it is only checked for format to reject obvious garbage.
    $request->validate([
        'name'     => [
            'required', 'string', 'min:3', 'max:255',
            // Letters (any alphabet), apostrophes, hyphens, dots and spaces:
            // "Yassine Hamdi", "O'Brien", "Jean-Pierre", "Ben Salah".
            "regex:/^[\\p{L}\\p{M}\\s'\\-\\.]+$/u",
        ],
        'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
        'phone'    => ['nullable', 'string', 'max:20', 'regex:/^\\+?[0-9\\s\\-\\(\\)]{8,20}$/'],
        'password' => [
            'required', 'string', 'confirmed',
            \Illuminate\Validation\Rules\Password::min(12)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols(),
            // Mirrors the "common" rule shown live in the registration form.
            function ($attribute, $value, $fail) use ($commonPasswords) {
                if (in_array(strtolower((string) $value), $commonPasswords, true)) {
                    $fail('Ce mot de passe est trop courant. Choisissez-en un d\'original.');
                }
            },
        ],
        'terms'    => ['accepted'],
    ]);

    $citoyenRole = \App\Models\Role::where('slug', \App\Models\Role::CITOYEN)->first();

    if (! $citoyenRole) {
        return back()
            ->withInput($request->only('name', 'email'))
            ->with('error', 'Le système n\'est pas encore configuré. Contactez l\'administrateur.');
    }

    $user = \App\Models\User::create([
        'name'      => trim($request->string('name')),
        'email'     => strtolower(trim($request->string('email'))),
        'password'  => $request->string('password'),
        'is_active' => true,
    ]);

    $user->assignRole($citoyenRole);

    // Les deux emails partent ensemble. Un dysfonctionnement SMTP ne doit
    // JAMAIS faire échouer l'inscription : le compte est déjà créé et actif,
    // on signale l'incident à la place (même discipline que
    // PasswordResetController qui enveloppe déjà son envoi d'OTP).
    try {
        // Email de bienvenue — notification déclarée mais jamais appelée.
        $user->notify(new \App\Notifications\WelcomeNotification());

        // Email d'activation du compte.
        $user->sendEmailVerificationNotification();
    } catch (\Throwable $e) {
        report($e);
    }

    return redirect()->route('auth.login')
        ->with('success', 'Compte créé avec succès. Vérifiez votre email pour activer votre compte.');
})->name('register.post');

// Google OAuth
Route::get('/auth/google',          [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// ── Password recovery (OTP par email) ────────────────────────────────────────
Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'create'])
    ->middleware('throttle:10,1')
    ->name('forgot-password.post');

Route::get('/password/otp',  [\App\Http\Controllers\Auth\PasswordResetController::class, 'showOtpForm'])
    ->name('password.verify.otp');

Route::post('/password/otp', [\App\Http\Controllers\Auth\PasswordResetController::class, 'verifyOtp'])
    ->middleware('throttle:10,1')
    ->name('password.verify.otp.post');

Route::post('/password/otp/resend', [\App\Http\Controllers\Auth\PasswordResetController::class, 'resend'])
    ->middleware('throttle:10,1')
    ->name('password.resend.otp');

Route::get('/password/reset',  [\App\Http\Controllers\Auth\PasswordResetController::class, 'showResetForm'])
    ->name('password.reset.form');

Route::post('/password/reset', [\App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])
    ->middleware('throttle:10,1')
    ->name('password.reset.post');

// ── Email Verification ────────────────────────────────────────────────────────
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (Request $request) {
    $user = \App\Models\User::findOrFail($request->route('id'));

    if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
        abort(403, 'Lien de vérification invalide.');
    }

    if ($user->hasVerifiedEmail()) {
        return redirect()->route('profile.show')->with('info', 'Email déjà vérifié.');
    }

    $user->markEmailAsVerified();

    return redirect()->route('profile.show')->with('success', 'Votre adresse email a été vérifiée avec succès !');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// ── Profile & 2FA ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::patch('/profile/avatar', [\App\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [\App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    // Two-Factor Authentication
    Route::get('/profile/two-factor', [\App\Http\Controllers\TwoFactorController::class, 'show'])->name('profile.two-factor');
    Route::post('/profile/two-factor', [\App\Http\Controllers\TwoFactorController::class, 'enable'])->name('profile.two-factor.enable');
    Route::get('/profile/two-factor/confirm', [\App\Http\Controllers\TwoFactorController::class, 'showConfirm'])->name('profile.two-factor.confirm');
    Route::post('/profile/two-factor/confirm', [\App\Http\Controllers\TwoFactorController::class, 'confirm'])->name('profile.two-factor.confirm.post');
    Route::delete('/profile/two-factor', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('profile.two-factor.disable');
    Route::post('/profile/two-factor/recovery', [\App\Http\Controllers\TwoFactorController::class, 'regenerateRecoveryCodes'])->name('profile.two-factor.regenerate');
    Route::get('/profile/two-factor/recovery', [\App\Http\Controllers\TwoFactorController::class, 'showRecoveryCodes'])->name('profile.two-factor.recovery');

    // Explicit "link my Google account". Reached only from the profile page,
    // never from the login form — the login button is a plain sign-in.
    Route::get('/profile/link/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'linkRedirect'])
        ->name('profile.google.link');
});

// Logout
Route::post('/logout', function (Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('landing');
})->name('logout');

// ── Citoyen ───────────────────────────────────────────────────────────────────
Route::middleware('role:citoyen')->group(function () {
    Route::get('/citizen/dashboard',        fn () => view('citizen.dashboard'))->name('citizen.dashboard');
    Route::get('/citizen/reports/create',   fn () => view('citizen.reports.create'))->name('citizen.reports.create');
    Route::post('/citizen/reports',         fn () => redirect()->route('citizen.dashboard')->with('success', 'Déclaration envoyée avec succès'))->name('citizen.reports.store');
    Route::get('/citizen/reports/{id}',     fn ($id) => view('citizen.reports.show', ['id' => $id]))->name('citizen.reports.show');
    Route::get('/citizen/notifications', function () {
        $incidentNotifications = auth()->user()
            ? auth()->user()->notifications()
                ->where('type', \App\Notifications\ActionCorrectiveCreated::class)
                ->latest()
                ->limit(20)
                ->get()
            : collect();

        return view('citizen.notifications', compact('incidentNotifications'));
    })->name('citizen.notifications');

    // Polling alertes en temps réel (main)
    Route::get('/citizen/alerts/poll', function () {
        $user = auth()->user();

        if (! $user) {
            return response()->json(['unread_count' => 0, 'notifications' => []]);
        }

        $notifications = $user->notifications()->latest()->limit(8)->get();

        return response()->json([
            'unread_count' => $user->notifications()->whereNull('read_at')->count(),
            'notifications' => $notifications->map(function ($notification) {
                $data = $notification->data ?? [];

                return [
                    'id' => (string) $notification->id,
                    'title' => isset($data['action_title'])
                        ? 'Nouvelle action corrective : ' . $data['action_title']
                        : ($data['incident_title'] ?? 'Nouvelle notification'),
                    'message' => $data['message'] ?? 'Une mise à jour est disponible.',
                    'read' => $notification->read_at !== null,
                    'time' => $notification->created_at?->diffForHumans() ?? 'Récemment',
                    'url' => isset($data['incident_id']) ? route('incidents.show', $data['incident_id']) : null,
                ];
            })->values(),
        ]);
    })->name('citizen.alerts.poll');
    Route::get('/citizen/invoices',         fn () => view('citizen.invoices.index'))->name('citizen.invoices.index');
    Route::get('/citizen/invoices/{id}',    fn ($id) => view('citizen.invoices.show', ['id' => $id]))->name('citizen.invoices.show');
});

// ── Technicien ────────────────────────────────────────────────────────────────
Route::middleware('role:technicien')->group(function () {
    Route::get('/technician/dashboard',              fn () => view('technician.dashboard'))->name('technician.dashboard');
    // Incidents technicien (main)
    Route::get('/technician/incidents/actions', [\App\Http\Controllers\Technician\ActionCorrectiveController::class, 'index'])->name('technician.incidents.index');
    Route::patch('/technician/incidents/actions/{action}/status', [\App\Http\Controllers\Technician\ActionCorrectiveController::class, 'updateStatus'])->name('technician.incidents.actions.status');
    Route::get('/technician/interventions', [\App\Http\Controllers\Technician\InterventionController::class, 'index'])->name('technician.interventions.index');
    Route::get('/technician/interventions/{id}', [\App\Http\Controllers\Technician\InterventionController::class, 'show'])->name('technician.interventions.show');
    Route::post('/technician/interventions/{id}/start', [\App\Http\Controllers\Technician\InterventionController::class, 'start'])->name('technician.interventions.start');
    Route::get('/technician/interventions/{id}/report', [\App\Http\Controllers\Technician\InterventionController::class, 'report'])->name('technician.interventions.report');
    Route::post('/technician/interventions/{id}/report', [\App\Http\Controllers\Technician\InterventionController::class, 'storeReport'])->name('technician.interventions.report.store');
    // Rapport IA (module Maintenance — collègues)
    Route::post('/technician/interventions/{id}/report/ia', [\App\Http\Controllers\Technician\InterventionController::class, 'assistantRapport'])->name('technician.interventions.report.ia');
    // Recommandation IA technicien (module Maintenance — collègues)
    Route::post('interventions/recommandation-ia', [\App\Http\Controllers\InterventionIaController::class, 'recommander'])->name('interventions.recommandation');
    // Planning (module Maintenance — collègues)
    Route::get('planning',                              [\App\Http\Controllers\PlanningController::class, 'index'])->name('planning.index');
    Route::get('planning/evenements',                   [\App\Http\Controllers\PlanningController::class, 'evenements'])->name('planning.evenements');
    Route::patch('planning/interventions/{intervention}', [\App\Http\Controllers\PlanningController::class, 'deplacer'])->name('planning.deplacer');
    Route::post('planning/preventive',                  [\App\Http\Controllers\PlanningController::class, 'preventive'])->name('planning.preventive');
    // Tableau de bord maintenance (module Maintenance — collègues)
    Route::get('maintenance/statistiques',    [\App\Http\Controllers\MaintenanceDashboardController::class, 'index'])->name('maintenance.statistiques');
    Route::get('maintenance/rapport-mensuel', [\App\Http\Controllers\MaintenanceDashboardController::class, 'rapportMensuel'])->name('maintenance.rapport-mensuel');
    Route::get('/technician/equipment',              fn () => view('technician.equipment'))->name('technician.equipment');
});

// ── Incidents (citoyen = declare, manager = handle) ───────────────────────────
Route::middleware(\App\Http\Middleware\IncidentRole::class . ':citizen')->group(function () {
    Route::get('/incidents',                  [IncidentController::class, 'index'])->name('incidents.index');
    Route::get('/incidents/create',           [IncidentController::class, 'create'])->name('incidents.create');
    Route::post('/incidents',                 [IncidentController::class, 'store'])->name('incidents.store');
    Route::get('/incidents/{incident}/edit',  [IncidentController::class, 'edit'])->name('incidents.edit');
    Route::put('/incidents/{incident}',       [IncidentController::class, 'update'])->name('incidents.update');
    Route::delete('/incidents/{incident}',    [IncidentController::class, 'destroy'])->name('incidents.destroy');
    Route::get('/incidents/{incident}/attachment', [IncidentController::class, 'attachment'])->name('incidents.attachment');
    Route::get('/incidents/{incident}',       [IncidentController::class, 'show'])->name('incidents.show');
    // IA conseil citoyen (main)
    Route::post('/incidents/ai-advice/preview',          [\App\Http\Controllers\IncidentAiController::class, 'citizenPreview'])->name('incidents.ai.preview');
    Route::post('/incidents/{incident}/ai-advice',       [\App\Http\Controllers\IncidentAiController::class, 'citizenAdvice'])->name('incidents.ai.advice');
});

Route::middleware(\App\Http\Middleware\IncidentRole::class . ':manager')->group(function () {
    Route::get('/manager/incidents',                          [IncidentController::class, 'managerIndex'])->name('manager.incidents');
    Route::get('/manager/incidents/{incident}/attachment',     [IncidentController::class, 'attachment'])->name('manager.incidents.attachment');
    Route::get('/manager/incidents/{incident}',               [IncidentController::class, 'managerShow'])->name('manager.incidents.show');
    // IA analyse incident (main)
    Route::post('/manager/incidents/{incident}/ai-analysis',  [\App\Http\Controllers\IncidentAiController::class, 'managerAnalysis'])
        ->middleware('throttle:10,1')->name('manager.incidents.ai.analysis');
    Route::get('/manager/incidents/{incident}/actions',       [ActionCorrectiveController::class, 'index'])->name('manager.actions.index');
    Route::get('/manager/incidents/{incident}/actions/create',[ActionCorrectiveController::class, 'create'])->name('manager.actions.create');
    Route::post('/manager/incidents/{incident}/actions',      [ActionCorrectiveController::class, 'store'])->name('incidents.actions.store');
    Route::get('/manager/actions/{action}/edit',              [ActionCorrectiveController::class, 'edit'])->name('manager.actions.edit');
    Route::get('/manager/actions/{action}',                   [ActionCorrectiveController::class, 'show'])->name('manager.actions.show');
    Route::put('/manager/actions/{action}',                   [ActionCorrectiveController::class, 'update'])->name('manager.actions.update');
    Route::delete('/manager/actions/{action}',                [ActionCorrectiveController::class, 'destroy'])->name('manager.actions.destroy');
});

// ── Manager ───────────────────────────────────────────────────────────────────
Route::middleware('role:manager')->group(function () {
    Route::get('/manager/dashboard', function () {
        // Variables requises par la vue manager.dashboard
        $riskTop5  = collect();
        $riskByZone = [];
        $topRiskInfrastructures = collect();

        // Top 5 & risque moyen par zone : prédictions enregistrées (module infrastructures)
        try {
            $predictions = \App\Models\Infrastructure::with(['zone', 'latestRiskPrediction'])->get()
                ->filter(fn ($i) => $i->latestRiskPrediction);

            $topRiskInfrastructures = $predictions
                ->sortByDesc(fn ($i) => $i->latestRiskPrediction->probability)
                ->take(5)
                ->values();

            $riskTop5 = $topRiskInfrastructures;

            // Format attendu par la vue : [['zone' => nom, 'average' => pourcentage], ...]
            $riskByZone = $predictions
                ->groupBy(fn ($i) => $i->zone?->nom ?? 'Sans zone')
                ->map(fn ($items, $zone) => [
                    'zone'    => $zone,
                    'average' => (int) round($items->avg(fn ($i) => $i->latestRiskPrediction->probability)),
                ])
                ->values()
                ->all();
        } catch (\Throwable $e) {
            // Module indisponible — valeurs par défaut
        }

        // Statistiques incidents
        $totalInc   = \App\Models\Incident::count();
        $pendingInc = \App\Models\Incident::where('statut', 'en_attente')->count();
        $inProgressInc  = \App\Models\Incident::where('statut', 'en_cours')->count();
        $resolvedInc    = \App\Models\Incident::where('statut', 'resolu')->count();

        // Projets en cours
        $projects = \App\Models\Projet::where('statut', 'en_cours')->get();

        // Financement
        $funding = \App\Models\Financement::sum('montant');

        // Données fictives / calculées pour les autres variables
        $firstName      = session('user.name', 'Gestionnaire');
        $municipalities = \App\Models\Zone::count();
        $monthly        = [];
        $qualityData    = [];
        $stats          = [];
        $reclamations   = collect();

        return view('manager.dashboard', compact(
            'riskTop5', 'riskByZone', 'topRiskInfrastructures',
            'totalInc', 'pendingInc', 'inProgressInc', 'resolvedInc',
            'projects', 'funding', 'firstName', 'municipalities',
            'monthly', 'qualityData', 'stats', 'reclamations'
        ));
    })->name('manager.dashboard');

    Route::get('/manager/budget', function (\App\Services\BudgetReportService $service) {
        return view('manager.budget', $service->stats());
    })->name('manager.budget');

    // ── Export du rapport budgétaire (mêmes données que l'écran) ──────────
    Route::get('/manager/budget/export/csv', [\App\Http\Controllers\Manager\BudgetExportController::class, 'exportCsv'])
        ->name('manager.budget.export.csv');
    Route::get('/manager/budget/export/pdf', [\App\Http\Controllers\Manager\BudgetExportController::class, 'exportPdf'])
        ->name('manager.budget.export.pdf');

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

    // Suppression en masse des mesures (main)
    Route::delete('/manager/quality/mesures', [WaterQualityController::class, 'bulkDestroy'])
        ->name('manager.quality.mesures.bulk-destroy');

    Route::get('/manager/teams', fn () => view('manager.teams'))->name('manager.teams');
    Route::get('/manager/reports', fn () => view('manager.reports'))->name('manager.reports');
});

Route::middleware('role:manager')->prefix('manager')->name('manager.')->group(function () {
    Route::get('zones/export/csv', [InventaireExportController::class, 'exportZonesCsv'])->name('zones.export.csv');
    Route::get('zones/{zone}/export/pdf', [InventaireExportController::class, 'exportZonePdf'])->name('zones.export.pdf');
    Route::get('infrastructures/export/csv', [InventaireExportController::class, 'exportInfrastructuresCsv'])->name('infrastructures.export.csv');
    Route::get('infrastructures/{infrastructure}/export/pdf', [InventaireExportController::class, 'exportInfrastructurePdf'])->name('infrastructures.export.pdf');
    Route::resource('zones', ZoneController::class);
    Route::patch('infrastructures/{infrastructure}/etat', [InfrastructureController::class, 'updateEtat'])->name('infrastructures.etat');
    Route::patch('infrastructures/alertes/{alerte}/resoudre', [InfrastructureController::class, 'resoudreAlerte'])->name('infrastructures.alertes.resoudre');
    Route::resource('infrastructures', InfrastructureController::class);
    // Recalcul risque infrastructure (main)
    Route::get('infrastructures/{infrastructure}/risk', [\App\Http\Controllers\InfrastructureRiskController::class, 'show'])->name('infrastructures.risk');
    Route::post('infrastructures/{infrastructure}/risk/recompute', [\App\Http\Controllers\InfrastructureRiskController::class, 'recompute'])->name('infrastructures.risk.recompute');
    Route::post('infrastructures/risk/recompute-all', [\App\Http\Controllers\InfrastructureRiskController::class, 'recomputeAll'])->name('infrastructures.risk.recompute-all');
});

Route::middleware('role:administrateur')->prefix('admin')->name('admin.')->group(function () {
    Route::get('settings/infrastructure-risk', [\App\Http\Controllers\Admin\InfrastructureRiskSettingsController::class, 'index'])->name('settings.infrastructure-risk');
    Route::put('settings/infrastructure-risk', [\App\Http\Controllers\Admin\InfrastructureRiskSettingsController::class, 'update'])->name('settings.infrastructure-risk.update');
    Route::post('settings/infrastructure-risk/recompute', [\App\Http\Controllers\Admin\InfrastructureRiskSettingsController::class, 'recomputeAll'])->name('settings.infrastructure-risk.recompute');
    Route::get('budget', [\App\Http\Controllers\Admin\ConsultationController::class, 'budget'])->name('budget');
    Route::get('projets', [\App\Http\Controllers\Admin\ConsultationController::class, 'projets'])->name('projets.index');
    Route::get('qualite', [\App\Http\Controllers\Admin\ConsultationController::class, 'qualite'])->name('qualite');
    Route::get('incidents', [\App\Http\Controllers\Admin\ConsultationController::class, 'incidents'])->name('incidents');
    Route::get('interventions', [\App\Http\Controllers\Admin\ConsultationController::class, 'interventions'])->name('interventions');
    Route::get('zones/export/csv', [InventaireExportController::class, 'exportZonesCsv'])->name('zones.export.csv');
    Route::get('zones/{zone}/export/pdf', [InventaireExportController::class, 'exportZonePdf'])->name('zones.export.pdf');
    Route::get('infrastructures/export/csv', [InventaireExportController::class, 'exportInfrastructuresCsv'])->name('infrastructures.export.csv');
    Route::get('infrastructures/{infrastructure}/export/pdf', [InventaireExportController::class, 'exportInfrastructurePdf'])->name('infrastructures.export.pdf');
    Route::resource('zones', ZoneController::class)->only(['index', 'show', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::patch('infrastructures/{infrastructure}/etat', [InfrastructureController::class, 'updateEtat'])->name('infrastructures.etat');
    Route::patch('infrastructures/alertes/{alerte}/resoudre', [InfrastructureController::class, 'resoudreAlerte'])->name('infrastructures.alertes.resoudre');
    Route::resource('infrastructures', InfrastructureController::class)->only(['index', 'show', 'create', 'store', 'edit', 'update', 'destroy']);
});

Route::middleware('role:citoyen')->prefix('citizen')->name('citizen.')->group(function () {
    Route::get('/beaches', [\App\Http\Controllers\Citizen\BeachMapController::class, 'index'])->name('beaches.index');
    Route::get('/zones', [\App\Http\Controllers\Citizen\ZoneController::class, 'index'])->name('zones.index');
    Route::get('/zones/{zone}', [\App\Http\Controllers\Citizen\ZoneController::class, 'show'])->name('zones.show');
    Route::get('/infrastructures', [\App\Http\Controllers\Citizen\InfrastructureController::class, 'index'])->name('infrastructures.index');
    Route::get('/infrastructures/{infrastructure}', [\App\Http\Controllers\Citizen\InfrastructureController::class, 'show'])->name('infrastructures.show');
});

Route::middleware('role:technicien')->prefix('technician')->name('technician.')->group(function () {
    Route::get('/zones', [\App\Http\Controllers\Technician\ZoneController::class, 'index'])->name('zones.index');
    Route::get('/zones/{zone}', [\App\Http\Controllers\Technician\ZoneController::class, 'show'])->name('zones.show');
    Route::get('/infrastructures', [\App\Http\Controllers\Technician\InfrastructureController::class, 'index'])->name('infrastructures.index');
    Route::get('/infrastructures/{infrastructure}', [\App\Http\Controllers\Technician\InfrastructureController::class, 'show'])->name('infrastructures.show');
    Route::patch('/infrastructures/{infrastructure}/etat', [\App\Http\Controllers\Technician\InfrastructureController::class, 'updateEtat'])->name('infrastructures.etat');
    Route::get('/scan', [\App\Http\Controllers\Technician\InfrastructureController::class, 'scan'])->name('scan');
    Route::post('/scan', [\App\Http\Controllers\Technician\InfrastructureController::class, 'processScan'])->name('scan.process');
    Route::get('/tournee', [\App\Http\Controllers\Technician\TourneeController::class, 'index'])->name('tournee');
    Route::get('/incidents/create', [\App\Http\Controllers\Technician\IncidentController::class, 'create'])->name('incidents.create');
    Route::post('/incidents', [\App\Http\Controllers\Technician\IncidentController::class, 'store'])->name('incidents.store');
    // Qualité eau technicien (main)
    Route::get('/zones/{zone}/mesures/create', [\App\Http\Controllers\Technician\WaterQualityController::class, 'create'])->name('quality.create');
    Route::post('/zones/{zone}/mesures', [\App\Http\Controllers\Technician\WaterQualityController::class, 'store'])->name('quality.store');
});

if (app()->environment(['local', 'testing'])) {
    Route::get('/switch-role/{role}', function (string $role) {
        $rolesMap = [
            'admin' => ['name' => 'Amina Kacem (System Admin)', 'email' => 'admin@aquasecure.tn', 'role' => 'admin', 'route' => 'admin.dashboard'],
            'manager' => ['name' => 'Ines Mansouri (Utility Manager)', 'email' => 'gestionnaire@aquasecure.tn', 'role' => 'manager', 'route' => 'manager.dashboard'],
            'quality' => ['name' => 'Dr. Selim Dridi (Water Quality Specialist)', 'email' => 'quality@aquasecure.tn', 'role' => 'manager', 'route' => 'manager.quality'],
            'technician' => ['name' => 'Amira Ben Ali (Field Technician)', 'email' => 'amira@aquasecure.tn', 'role' => 'technician', 'route' => 'technician.dashboard'],
            'finance' => ['name' => 'Moncef Triki (Finance Manager)', 'email' => 'finance@aquasecure.tn', 'role' => 'manager', 'route' => 'manager.budget'],
            'citizen' => ['name' => 'Yassine Hamdi (Resident Citizen)', 'email' => 'citoyen@aquasecure.tn', 'role' => 'citizen', 'route' => 'citizen.dashboard'],
        ];

        abort_unless(isset($rolesMap[$role]), 404);

        $user = $rolesMap[$role];
        session(['user' => [
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'role_key' => $role,
        ]]);

        return redirect()->route($user['route'])->with('success', 'Passé au rôle : ' . $user['name']);
    })->name('switch.role');
}

// ── Administrateur ────────────────────────────────────────────────────────────
Route::middleware('role:administrateur')->group(function () {
    Route::get('/admin/dashboard', function () {
        // Variables requises par la vue admin.dashboard
        // Distribution des risques : agrégation des dernières prédictions enregistrées,
        // complétée par les 4 clés attendues par la vue (Faible / Moyen / Élevé / Critique).
        $riskDistribution = ['Faible' => 0, 'Moyen' => 0, 'Élevé' => 0, 'Critique' => 0];

        try {
            $counts = \App\Models\InfrastructureRiskPrediction::query()
                ->get()
                ->groupBy('level')
                ->map->count();

            foreach ($counts as $level => $count) {
                if (array_key_exists($level, $riskDistribution)) {
                    $riskDistribution[$level] = $count;
                }
            }
        } catch (\Throwable $e) {
            // Tables de prédictions indisponibles — valeurs par défaut
        }

        // Statistiques incidents
        $totalRec    = \App\Models\Incident::count();
        $criticalRec = \App\Models\Incident::where('statut', 'critique')->count();
        $progressRec = \App\Models\Incident::where('statut', 'en_cours')->count();
        $resolvedRec = \App\Models\Incident::where('statut', 'resolu')->count();
        $pendingRec  = \App\Models\Incident::where('statut', 'en_attente')->count();

        // Utilisateurs
        $citizens     = \App\Models\User::whereHas('roles', fn ($q) => $q->where('slug', 'citoyen'))->count();
        $managers     = \App\Models\User::whereHas('roles', fn ($q) => $q->where('slug', 'manager'))->count();
        $technicians  = \App\Models\User::whereHas('roles', fn ($q) => $q->where('slug', 'technicien'))->count();
        $accounts     = \App\Models\User::count();

        // Techniciens
        $techCount     = $technicians;
        $techAvail     = 0;
        $techOnMission = 0;

        // Incidents 7j
        $inc7d = \App\Models\Incident::where('created_at', '>=', now()->subDays(7))->count();

        // Zones
        $mapZones    = \App\Models\Zone::withCount('incidents')->get();
        $zoneOverview = $mapZones;

        // Données par défaut
        $reclamations = collect();
        $resHistory   = [];
        $distData     = [];

        return view('admin.dashboard', compact(
            'riskDistribution', 'mapZones', 'zoneOverview',
            'totalRec', 'criticalRec', 'progressRec', 'resolvedRec', 'pendingRec',
            'citizens', 'managers', 'technicians', 'accounts',
            'techCount', 'techAvail', 'techOnMission', 'inc7d',
            'reclamations', 'resHistory', 'distData'
        ));
    })->name('admin.dashboard');
    Route::get('/admin/users',       [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users',      [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
    Route::put('/admin/users/{user}',[\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
    Route::patch('/admin/users/{user}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::delete('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/admin/logs',        [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('admin.logs');
    Route::get('/admin/system',      fn () => view('admin.system'))->name('admin.system');
    Route::get('/admin/security',    fn () => view('admin.security'))->name('admin.security');
    Route::get('/admin/backups',     fn () => view('admin.backups'))->name('admin.backups');
    // Routes admin manquantes (main)
    Route::get('/admin/roles',       fn () => view('admin.roles'))->name('admin.roles');
    Route::get('/admin/map',         fn () => view('admin.map'))->name('admin.map');
});

// ── All authenticated roles ───────────────────────────────────────────────────
Route::middleware('role:administrateur,manager,technicien,citoyen')->group(function () {
    Route::get('/settings',               fn () => view('settings.index'))->name('settings.index');
    Route::get('/settings/notifications', fn () => view('settings.notifications'))->name('settings.notifications');
    Route::get('/notifications',          fn () => view('notifications.index'))->name('notifications.index');
    // Notifications CRUD (main)
    Route::post('/notifications/tout-lire',  [\App\Http\Controllers\NotificationController::class, 'toutLire'])->name('notifications.tout-lire');
    Route::get('/notifications/{id}/ouvrir', [\App\Http\Controllers\NotificationController::class, 'ouvrir'])->name('notifications.ouvrir');
});

// ── Chatbot IA (module Projets et Financements) ──
// La route status est publique (vérification côté client avant login)
Route::get('/chatbot/status', [\App\Http\Controllers\ChatbotController::class, 'status'])->name('chatbot.status');

Route::middleware('role:citoyen,manager,administrateur')->prefix('chatbot')->name('chatbot.')->group(function () {
    Route::post('/ask', [\App\Http\Controllers\ChatbotController::class, 'ask'])->name('ask');
});

// ── Public demo pages ─────────────────────────────────────────────────────────
Route::get('/ai-demo', fn () => view('ai-demo'))->name('ai.demo');
Route::get('/weather',  fn () => response()->file(public_path('weather.html')))->name('weather');

// ── Maintenance module (techniciens & interventions — manager only) ────────────
Route::middleware('role:manager')->prefix('manager')->name('manager.')->group(function () {
    Route::resource('techniciens',   TechnicienController::class);
    Route::resource('interventions', InterventionController::class);
    // PDF rapport intervention (main)
    Route::get('interventions/{intervention}/pdf', [InterventionController::class, 'pdf'])
        ->name('interventions.pdf');
    // Recommandation IA manager (main)
    Route::post('interventions/recommandation-ia', [\App\Http\Controllers\InterventionIaController::class, 'recommander'])
        ->name('interventions.recommandation');
    // Statistiques & rapport mensuel maintenance (main)
    Route::get('maintenance/statistiques',    [\App\Http\Controllers\MaintenanceDashboardController::class, 'index'])->name('maintenance.statistiques');
    Route::get('maintenance/rapport-mensuel', [\App\Http\Controllers\MaintenanceDashboardController::class, 'rapportMensuel'])->name('maintenance.rapport-mensuel');
    // Planning (main)
    Route::get('planning',                              [\App\Http\Controllers\PlanningController::class, 'index'])->name('planning.index');
    Route::get('planning/evenements',                   [\App\Http\Controllers\PlanningController::class, 'evenements'])->name('planning.evenements');
    Route::patch('planning/interventions/{intervention}', [\App\Http\Controllers\PlanningController::class, 'deplacer'])->name('planning.deplacer');
    Route::post('planning/preventive',                  [\App\Http\Controllers\PlanningController::class, 'preventive'])->name('planning.preventive');
});

// ── Travaux (citizen read-only) ───────────────────────────────────────────────
Route::get('/citizen/travaux', [TravauxController::class, 'index'])->name('citizen.travaux.index');
Route::get('/citizen/travaux/{intervention}', [TravauxController::class, 'show'])->name('citizen.travaux.show');

Route::middleware('role:citoyen')->group(function () {
    Route::prefix('citizen/projets')->name('citizen.projets.')->group(function () {
        Route::get('/',        [App\Http\Controllers\Citizen\ProjetController::class, 'index'])->name('index');
        Route::get('/{projet}', [App\Http\Controllers\Citizen\ProjetController::class, 'show'])->name('show');

        // ── Favoris : le citoyen sauvegarde des projets ────────────────────
        // POST/PATCH/DELETE, donc protégés CSRF comme toutes les autres
        // mutations du dépôt. Aucun identifiant citoyen n'est accepté depuis
        // l'URL : le propriétaire vient toujours de la session.
        Route::post('/{projet}/favori',    [App\Http\Controllers\Citizen\FavoriController::class, 'store'])->name('favori.store');
        Route::delete('/{projet}/favori',  [App\Http\Controllers\Citizen\FavoriController::class, 'destroy'])->name('favori.destroy');
        // Bascule : bouton des cartes, qui ignore l'état courant.
        Route::patch('/{projet}/favori',   [App\Http\Controllers\Citizen\FavoriController::class, 'basculer'])->name('favori.basculer');
    });

    // ── Mes favoris ──────────────────────────────────────────────────────────
    Route::get(
        '/citizen/favoris',
        [App\Http\Controllers\Citizen\FavoriController::class, 'index']
    )->name('citizen.favoris');

    // ── Recommandations personnalisées (moteur V1 à règles, sans ML) ───────
    Route::get(
        '/citizen/recommandations',
        [App\Http\Controllers\Citizen\RecommandationController::class, 'index']
    )->name('citizen.recommandations');
    Route::get(
        '/citizen/recommandations/api',
        [App\Http\Controllers\Citizen\RecommandationController::class, 'api']
    )->name('citizen.recommandations.api');

    // ── Mon portefeuille et mon impact ─────────────────────────────────────
    Route::get(
        '/citizen/portefeuille',
        [App\Http\Controllers\Citizen\PortefeuilleController::class, 'index']
    )->name('citizen.portefeuille');

    // ── Demandes de financement citoyen ───────────────────────────────────
    Route::prefix('citizen/demandes')->name('citizen.demandes.')->group(function () {
        Route::get('/',                       [App\Http\Controllers\Citizen\DemandeFinancementController::class, 'index'])->name('index');
        Route::get('/create',                 [App\Http\Controllers\Citizen\DemandeFinancementController::class, 'create'])->name('create');
        Route::post('/',                      [App\Http\Controllers\Citizen\DemandeFinancementController::class, 'store'])->name('store');
        Route::get('/{demande}',              [App\Http\Controllers\Citizen\DemandeFinancementController::class, 'show'])->name('show');
        Route::get('/{demande}/recapitulatif', [App\Http\Controllers\Citizen\DemandeFinancementController::class, 'recap'])->name('recap');

        // Sensitive: this is the only route that records money. Throttled so a
        // script cannot replay it, and CSRF protected as any other POST.
        Route::post('/{demande}/confirmer',   [App\Http\Controllers\Citizen\DemandeFinancementController::class, 'confirmer'])
            ->middleware('throttle:10,1')
            ->name('confirmer');
        Route::post('/{demande}/annuler',     [App\Http\Controllers\Citizen\DemandeFinancementController::class, 'annuler'])->name('annuler');
    });

    // ── Opérations de paiement simulées (résultat & reçu) ─────────────────
    Route::prefix('citizen/paiements')->name('citizen.demandes.paiement.')->group(function () {
        Route::get('/{paiement}',             [App\Http\Controllers\Citizen\DemandeFinancementController::class, 'resultat'])->name('resultat');
        Route::get('/{paiement}/recu',        [App\Http\Controllers\Citizen\DemandeFinancementController::class, 'recu'])->name('recu');
        Route::get('/{paiement}/recu/pdf',    [App\Http\Controllers\Citizen\DemandeFinancementController::class, 'recuPdf'])->name('recu.pdf');
    });
});

// ── Projets & Financements (manager CRUD) ─────────────────────────────────────
Route::middleware('role:manager')->prefix('manager/projets')->name('manager.projets.')->group(function () {
    Route::get('/',            [App\Http\Controllers\Manager\ProjetController::class, 'index'])->name('index');
    Route::get('/create',      [App\Http\Controllers\Manager\ProjetController::class, 'create'])->name('create');
    Route::post('/',           [App\Http\Controllers\Manager\ProjetController::class, 'store'])->name('store');
    Route::get('/map',         [App\Http\Controllers\Manager\ProjetController::class, 'map'])->name('map');
    Route::get('/{projet}',    [App\Http\Controllers\Manager\ProjetController::class, 'show'])->name('show');
    Route::get('/{projet}/edit',[App\Http\Controllers\Manager\ProjetController::class, 'edit'])->name('edit');
    Route::put('/{projet}',    [App\Http\Controllers\Manager\ProjetController::class, 'update'])->name('update');
    Route::delete('/{projet}', [App\Http\Controllers\Manager\ProjetController::class, 'destroy'])->name('destroy');
});

Route::middleware('role:manager')->prefix('manager/financements')->name('manager.financements.')->group(function () {
    Route::get('/',                [App\Http\Controllers\Manager\FinancementController::class, 'index'])->name('index');
    Route::get('/create',          [App\Http\Controllers\Manager\FinancementController::class, 'create'])->name('create');
    Route::post('/',               [App\Http\Controllers\Manager\FinancementController::class, 'store'])->name('store');
    Route::get('/{financement}',   [App\Http\Controllers\Manager\FinancementController::class, 'show'])->name('show');
    Route::get('/{financement}/edit', [App\Http\Controllers\Manager\FinancementController::class, 'edit'])->name('edit');
    Route::put('/{financement}',   [App\Http\Controllers\Manager\FinancementController::class, 'update'])->name('update');
    Route::delete('/{financement}',[App\Http\Controllers\Manager\FinancementController::class, 'destroy'])->name('destroy');
});

// ── Demandes de financement citoyen (validation manager) ──────────────────────
Route::middleware('role:manager')->prefix('manager/demandes')->name('manager.demandes.')->group(function () {
    Route::get('/',                          [App\Http\Controllers\Manager\DemandeFinancementController::class, 'index'])->name('index');
    Route::get('/{demande}',                 [App\Http\Controllers\Manager\DemandeFinancementController::class, 'show'])->name('show');
    Route::post('/{demande}/accepter',       [App\Http\Controllers\Manager\DemandeFinancementController::class, 'accepter'])->name('accepter');
    Route::post('/{demande}/refuser',        [App\Http\Controllers\Manager\DemandeFinancementController::class, 'refuser'])->name('refuser');
});

Route::middleware('role:manager')->group(function () {
    Route::post('/manager/data-collection/trigger',    [\App\Http\Controllers\Manager\DataCollectionController::class, 'trigger'])->name('manager.data-collection.trigger');
    Route::post('/manager/data-collection/import-csv', [\App\Http\Controllers\Manager\DataCollectionController::class, 'importCsv'])->name('manager.data-collection.import-csv');
    Route::get('/manager/data-collection/status',      [\App\Http\Controllers\Manager\DataCollectionController::class, 'status'])->name('manager.data-collection.status');
    Route::get('/manager/data-collection/history',     [\App\Http\Controllers\Manager\DataCollectionController::class, 'history'])->name('manager.data-collection.history');
    // Téléchargement template CSV (main)
    Route::get('/manager/data-collection/template-csv', [\App\Http\Controllers\Manager\DataCollectionController::class, 'downloadTemplateCsv'])->name('manager.data-collection.template-csv');
    Route::get('/manager/quality/{point}/analyse',     [\App\Http\Controllers\Manager\DataCollectionController::class, 'analysePoint'])->name('manager.quality.analyse');
});
