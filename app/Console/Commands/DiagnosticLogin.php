<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class DiagnosticLogin extends Command
{
    protected $signature = 'login:diagnostic {email?}';
    protected $description = 'Diagnostiquer le problème de redirection vers login';

    public function handle(): void
    {
        $email = $this->argument('email');

        $this->newLine();
        $this->line('════════════════════════════════════════');
        $this->info('  DIAGNOSTIC REDIRECTION → LOGIN');
        $this->line('════════════════════════════════════════');

        // 1. Trouver un citoyen
        $query = User::with('roles');
        if ($email) {
            $query->where('email', $email);
        } else {
            $query->whereHas('roles', fn ($q) => $q->where('slug', Role::CITOYEN));
        }
        $user = $query->first();

        if (! $user) {
            $this->error("Aucun citoyen trouvé" . ($email ? " pour $email" : " en DB"));
            return;
        }

        $this->newLine();
        $this->warn('👤 Utilisateur :');
        $this->line('  Email     : ' . $user->email);
        $this->line('  is_active : ' . ($user->is_active ? 'true ✅' : 'false ❌'));
        $this->line('  Rôles     : ' . implode(', ', $user->getRoleSlugs()));
        $this->line('  Primary   : ' . $user->primary_role);

        // 2. Vérifier Role::sessionMatches simulé
        $this->newLine();
        $this->warn('🔍 Simulation session :');
        session(['user' => [
            'id'    => $user->id,
            'email' => $user->email,
            'role'  => $user->primary_role,
            'roles' => $user->getRoleSlugs(),
        ]]);

        $matchesCitoyen = Role::sessionMatches(Role::CITOYEN);
        $this->line('  session(user.role)       : ' . session('user.role'));
        $this->line('  Role::CITOYEN            : ' . Role::CITOYEN);
        $this->line('  sessionMatches(CITOYEN)  : ' . ($matchesCitoyen ? 'OUI ✅' : 'NON ❌'));

        // 3. Vérifier Auth
        $this->newLine();
        $this->warn('🔑 Guard Auth :');
        Auth::login($user);
        $this->line('  Auth::check()            : ' . (Auth::check() ? 'OUI ✅' : 'NON ❌'));
        $this->line('  Auth::id()               : ' . (Auth::id() ?? 'null'));
        $this->line('  hasAnyRole([citoyen])    : ' . ($user->hasAnyRole([Role::CITOYEN]) ? 'OUI ✅' : 'NON ❌'));

        // 4. Vérifier route middleware
        $this->newLine();
        $this->warn('🛣️ Routes citizen/projets :');
        $routeInfo = collect(\Route::getRoutes())->filter(fn ($r) => str_contains($r->getName() ?? '', 'citizen.projets'))->values();
        foreach ($routeInfo as $route) {
            $middlewares = implode(', ', $route->middleware());
            $this->line("  {$route->getName()} → [{$middlewares}]");
        }

        // 5. Conclusion
        $this->newLine();
        $this->line('════════════════════════════════════════');
        if ($matchesCitoyen && Auth::check()) {
            $this->info('  ✅ Tout semble correct — vérifier si MySQL tourne');
        } else {
            $this->error('  ❌ Problème détecté :');
            if (! $matchesCitoyen) {
                $this->line("  → sessionMatches(CITOYEN) retourne false");
                $this->line("  → session role '{$user->primary_role}' vs CITOYEN constant '" . Role::CITOYEN . "'");
            }
            if (! Auth::check()) {
                $this->line('  → Auth::check() retourne false après Auth::login()');
            }
        }
        $this->newLine();
    }
}
