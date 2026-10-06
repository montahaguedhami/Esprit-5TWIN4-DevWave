# AquaSecure — Audit Complet du Projet

> Document d'audit destiné à toute IA/développeur souhaitant comprendre le projet en détail.
> Date de l'audit : 06 octobre 2026

---

## 1. Identité du projet

| Champ | Valeur |
|-------|--------|
| Nom | AquaSecure |
| Type | Plateforme SaaS de gestion intelligente des infrastructures hydrauliques en Tunisie |
| État actuel | Prototype **frontend uniquement** (UI/UX complet, données statiques) |
| Framework | Laravel 12 (PHP ^8.2) |
| Base de données | SQLite (dev) — migrations users/cache/jobs uniquement |
| Frontend | Blade + Tailwind CSS v4 (via Vite 7) + Vanilla JS + Leaflet (cartes) |
| Branche git | `main`, 4 commits (dernier : `c27e42b feat: improve AquaSecure frontend templatesn v2`) |
| Locale | Interface en français, `APP_LOCALE=en` dans `.env` |
| URL dev | `http://localhost` (`php artisan serve`) |

## 2. Objectif métier

Gérer la surveillance et la maintenance des réseaux d'eau (qualité, fuites, incidents, factures, interventions terrain) autour de **4 espaces utilisateurs par rôle** :

- **Citoyen** (frontoffice) : signaler des problèmes, consulter factures, notifications
- **Technicien** (backoffice terrain) : interventions, équipements, rapports
- **Manager/Gestionnaire** (backoffice) : incidents, équipes, carte, projets, analytics
- **Admin** (backoffice système) : utilisateurs, rôles, logs, sécurité, backups, métriques

## 3. Stack technique détaillée

### Backend (`composer.json`)
- `php: ^8.2`, `laravel/framework: ^12.0`, `laravel/tinker: ^2.10.1`
- Dev : PHPUnit 11, Laravel Pint, Pail, Sail, Mockery, Collision
- **Aucun** package d'auth (pas de Breeze/Fortify/Sanctum/Socialite)

### Frontend (`package.json`)
- Vite 7, `laravel-vite-plugin` 2, Tailwind CSS 4 (`@tailwindcss/vite`)
- `leaflet` 1.9.4 (cartes interactives, page `/manager/map`)
- `axios` 1.11 (prévu pour futures appels API), `concurrently`
- Fonts Google : Plus Jakarta Sans (body), Space Grotesk (display)
- Icônes : Lucide

### Config Vite (`vite.config.js`)
Entrées : `resources/css/app.css` + `resources/js/app.js`, plugin Tailwind, refresh activé.

### Environnement (`.env`)
- `DB_CONNECTION=sqlite`, `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`, `CACHE_STORE=database`
- Mail : driver `log` — aucun e-mail réel
- `APP_DEBUG=true` — **à désactiver en production**

## 4. Arborescence complète

```
aquasecure/
├── app/
│   ├── Data/
│   │   └── PlaceholderData.php     # ~50 méthodes de données mock (SOURCE UNIQUE DES DONNÉES)
│   ├── Http/Controllers/
│   │   └── Controller.php          # Classe abstraite de base, AUCUN contrôleur métier
│   ├── Models/
│   │   └── User.php                # Modèle Eloquent User (migration existante)
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/                      # app.php, providers.php, cache/
├── config/                         # Configuration Laravel standard
├── database/
│   ├── database.sqlite             # Base vide (tables Laravel système)
│   ├── factories/UserFactory.php
│   ├── migrations/                 # users, cache, jobs (défaut Laravel 12)
│   └── seeders/DatabaseSeeder.php
├── public/                         # index.php, assets compilés, favicon
├── resources/
│   ├── css/app.css                 # Design system : 920+ lignes, variables, keyframes, responsive
│   ├── js/app.js, bootstrap.js     # JS vanilla (toasts, dropdowns, timer, tabs...)
│   └── views/                      # 50+ templates Blade (~840 Ko de vues)
├── routes/
│   ├── web.php                     # 47 routes — TOUTES en closures, auth simulée par session
│   └── console.php
├── storage/                        # Logs, cache, sessions (driver database)
├── tests/                          # Skeleton uniquement, AUCUN test métier
├── vendor/, node_modules/
├── .env, .env.example, composer.json, package.json, vite.config.js, phpunit.xml
├── README.md                       # Documentation utilisateur/installation
├── PROJECT_SUMMARY.md              # Synthèse phases de développement
└── ACCESSIBILITY.md                # Guide WCAG 2.1 AA
```

## 5. Couche données — `app/Data/PlaceholderData.php`

Seule source de données de l'application (tout est statique/mock). Méthodes principales :

| Zone | Méthodes |
|------|----------|
| Référentiel | `zones()`, `zoneStats()`, `stats()`, `zoneColors()`, `zoneLabels()`, `landingKPIs()`, `findZone()`, `formatNumber()` |
| Citoyen | `citizenReports()`, `citizenNotifications()`, `citizenInvoices()` |
| Technicien | `technicianInterventions()`, `technicianEquipment()`, `technicianZones()` |
| Admin | `adminUsers()`, `adminUserStats()`, `adminRoles()`, `adminLogs()`, `adminSystemMetrics()`, `adminBackups()`, `adminSecurityAlerts()`, `adminAllAccounts()`, `adminAllReclamations()`, `adminGlobalKPIs()`, `adminRecentIncidents()`, `adminTechnicians()`, `adminManagers()`, `adminFunctionalActivity()`, `adminResolutionHistory()`, `adminZoneOverview()`, `adminPerformanceStats()`, `adminIncidents7Days()`, `adminPlatformActivity()`, `adminSecurityEvents()`, `adminResourceUsage()`, `adminSystemAlerts()`, `adminUserActivity7Days()` |
| Manager | `mapZones()`, `mapPipelines()`, `analyticsMonthly()`, `analyticsKPIs()`, `analyticsIncidentTypes()`, `analyticsTeamPerformance()`, `analyticsWeeklyByZone()`, `managerProjects()` |
| Global | `globalNotifications()` |

> ⚠️ Les vues ne passent jamais par la BD : elles appellent `PlaceholderData::xxx()` directement dans les closures de routes ou les templates.

## 6. Routes — inventaire complet (`routes/web.php`)

**Pattern d'authentification** : chaque route protégée commence par :
```php
if (!session('user') || session('user.role') !== '<role>') {
    return redirect()->route('auth.login');
}
```
La session est posée dans `POST /login` à partir d'un tableau d'hôtes **hardcodé** (4 comptes démo). Aucune vérification de mot de passe, aucun hash, aucune table users utilisée.

### Publiques (5)
| Méthode | URI | Nom | Action |
|---------|-----|-----|--------|
| GET | `/` | `landing` | view `landing` |
| GET | `/login` | `auth.login` | view `auth.login` |
| GET | `/register` | `auth.register` | view `auth.register` |
| GET | `/forgot-password` | `auth.forgot-password` | view |
| GET | `/ai-demo` | `ai.demo` | view `ai-demo` |

### Auth simulée (4)
| Méthode | URI | Nom | Comportement |
|---------|-----|-----|--------------|
| POST | `/login` | `login.post` | Valide l'email contre 4 comptes démo → pose `session('user')` → redirect selon rôle |
| POST | `/register` | `register.post` | Toujours succès → redirect login avec message |
| POST | `/forgot-password` | `forgot-password.post` | Message générique (aucun mail) |
| POST | `/logout` | `logout` | `session()->forget('user')` → redirect landing |

**Comptes démo :**
| Email | Rôle | Nom |
|-------|------|-----|
| `citoyen@aquasecure.tn` | citizen | Yassine Hamdi |
| `amira@aquasecure.tn` | technician | Amira Ben Ali |
| `gestionnaire@aquasecure.tn` | manager | Ines Mansouri |
| `admin@aquasecure.tn` | admin | Amina Kacem |

(Mot de passe affiché côté UI : `demo123`, non vérifié côté serveur.)

### Espace Citoyen (7)
`citizen.dashboard`, `citizen.reports.create` (GET), `citizen.reports.store` (POST → redirect + flash), `citizen.reports.show/{id}`, `citizen.notifications`, `citizen.invoices.index`, `citizen.invoices.show/{id}`

### Espace Technicien (6)
`technician.dashboard`, `technician.interventions.index`, `technician.interventions.show/{id}`, `technician.interventions.report` (GET formulaire + POST store), `technician.equipment`

### Espace Manager (8)
`manager.dashboard`, `manager.map`, `manager.analytics`, `manager.incidents`, `manager.teams`, `manager.projects`, `manager.reports` (+ rôle requis `manager`)

### Espace Admin (8)
`admin.dashboard`, `admin.users.index`, `admin.roles`, `admin.system`, `admin.logs`, `admin.security`, `admin.backups`

### Globales (4)
`profile.show`, `settings.index`, `settings.notifications`, `notifications.index` — nécessitent seulement `session('user')`, quel que soit le rôle.

## 7. Vues & layouts

### Layouts (`resources/views/layouts/`)
| Layout | Usage |
|--------|-------|
| `public.blade.php` | Landing |
| `auth.blade.php` | Pages d'authentification |
| `frontoffice.blade.php` | Espace citoyen |
| `admin.blade.php` | Espace admin |
| `manager.blade.php` | Espace manager |
| `app.blade.php` | Layout générique |

### Pages par espace (`resources/views/`)
- **landing.blade.php** : hero, how-it-works, features, roles, footer
- **auth/** : `login`, `register` (password strength), `forgot-password`
- **citizen/** : `dashboard`, `reports/create` (multi-étapes), `reports/show` (timeline), `invoices/index`, `invoices/show`, `notifications`
- **technician/** : `dashboard`, `interventions/index|show|report` (timer, photos avant/après, pièces), `equipment`
- **manager/** : `dashboard`, `map` (Leaflet), `analytics`, `incidents`, `teams`, `projects`, `reports`
- **admin/** : `dashboard`, `users/index`, `roles`, `system`, `logs`, `security`, `backups`
- **profile/show**, **settings/index** (5 tabs : général, notifications, sécurité, confidentialité, apparence), **settings/notifications**, **notifications/index**, **welcome.blade.php** (défaut Laravel), **ai-demo**

### Composants (`resources/views/components/`) — 23
- **ui/** (14) : `alert`, `avatar`, `button`, `card`, `dropdown`, `empty-state`, `loading-skeleton`, `modal`, `pagination`, `search-input`, `stat-card`, `status-badge`, `tabs`, `toast`, `tooltip` *(en réalité ~16 fichiers si on compte ui/*)*
- **forms/** (3) : `input`, `select`, `textarea`
- **dashboard/** (2) : `kpi-card`, `activity-feed`
- **globaux** : `mobile-nav`, `notification-center`, `user-menu`, `ai-assistant`, `admin-nav-item`, `badge`, `drop-loader`, `icon`, `rain-effect`, `ripple-button`, `water-progress`, `wave-background`

## 8. Design system (`resources/css/app.css`)

- **Tailwind v4** avec syntaxe `@theme`
- Variables : `--bg-primary: #061525` (navy), `--accent-cyan: #05bfdb`, `--accent-turquoise: #2dd4bf`, `--text-primary: #f0fdff`, `--text-secondary: #9cc8d8`, états positive/warning/critical
- Effets : glassmorphism (`.glass`, `.glass-strong`), `.text-gradient`, `.hover-lift`, `.pulse-glow`
- 15+ keyframes (fadeIn, slideIn, scaleIn, waves, rain), thèmes dark/light/deep via variables CSS
- Responsive : breakpoints 640/1024px, classes `.mobile-only`, `.desktop-only`, `.mobile-stack`, grilles adaptatives
- Accessibilité intégrée : `prefers-reduced-motion`, focus outline cyan 2px, touch targets ≥ 44px
- Print styles

## 9. JavaScript (`resources/js/app.js`, `bootstrap.js`)

Vanilla JS — pas de framework. Interactions : toggles de dropdowns/menus, tabs, timer d'intervention, toasts, IA assistant (chat simulé avec 6 réponses par keyword matching), effets visuels (rain, waves), Leaflet map init.
Axios importé dans `bootstrap.js` pour de futurs appels API.

## 10. Sécurité & authentification — état actuel

| Aspect | État |
|--------|------|
| Mots de passe | ❌ Non vérifiés (login par email seul, comptes hardcodés dans `web.php`) |
| Hashage | ❌ Aucun |
| Middleware d'auth | ❌ Aucun — vérification manuelle dans chaque closure |
| CSRF | ✅ Actif (formulaires Blade avec `@csrf`, middleware web implicite) |
| Autorisation par rôle | ⚠️ Dupliquée dans chaque route, fragile |
| Sessions | Driver `database` (table `sessions` à migrer si besoin) |
| `APP_DEBUG` | ⚠️ `true` en local |
| Mass assignment / validation serveur | ❌ Aucune validation sur les POST (`reports.store`, `report.store`, login...) |
| XSS | ✅ Blade échappe par défaut (`{{ }}`) |

## 11. Tests & qualité

- `tests/` contient le squelette Laravel (ExampleTest) — **0 test métier**
- `phpunit.xml` configuré pour SQLite en mémoire
- `Laravel Pint` disponible pour le formatage
- Aucun CI/CD, aucun linter JS, aucune couverture

## 12. Ce qui fonctionne vs ce qui manque

### ✅ Fonctionnel
- Navigation complète entre ~35+ pages
- Gardes de rôle par session
- Design system cohérent et responsive
- Composants réutilisables
- Démo AI assistant, cartes Leaflet (données mock)

### ❌ Absent (par conception — prototype)
- Modèles Eloquent métier (Report, Intervention, Invoice, Incident, Zone, Equipment...) 
- Migrations métier
- Contrôleurs (toute la logique est dans les closures de `routes/web.php`)
- CRUD persistant, API REST
- Auth réelle (Breeze/Fortify), rôles/permissions via BDD (Spatie)
- Validation serveur, uploads réels, emails, queues, tests, CI/CD

## 13. Risques & faiblesses identifiés

1. **Logique dans les routes** : `routes/web.php` concentre toute la logique → difficile à tester/maintenir.
2. **Auth non sécurisée** : comptes en clair dans le code, pas de hash, pas de middleware.
3. **Données mock non centralisées côté vues** : couplage fort vues ↔ `PlaceholderData`.
4. **Duplication** du contrôle d'accès à chaque route.
5. **`APP_DEBUG=true`** et comptes démo hardcodés : inacceptable en production.
6. **Pas de tests** : aucune garantie de non-régression.
7. Documentation (`README`/`PROJECT_SUMMARY`) indique "Laravel 11.x" alors que `composer.json` exige Laravel 12 — légère incohérence.

## 14. Recommandations (feuille de route pour passer en vrai backend)

1. **Extraire les contrôleurs** : `php artisan make:controller Citizen/ReportController --resource`, idem Technician/Manager/Admin. Déplacer les closures vers des méthodes.
2. **Auth réelle** : `composer require laravel/breeze --dev && php artisan breeze:install blade` — remplacer le login simulé.
3. **Rôles** : package `spatie/laravel-permission` ou colonne `role` sur `users` + middleware `role:admin`.
4. **Domaine** : créer les modèles + migrations `Report`, `Intervention`, `Invoice`, `Zone`, `Equipment`, `Incident`, `Notification` et seeders à partir de `PlaceholderData`.
5. **Middleware** `EnsureRole` pour remplacer les `if (!session('user')...)` dupliqués.
6. **Validation FormRequest** sur tous les POST.
7. **Tests** : feature tests par rôle et par page.
8. **API REST** optionnelle sous `/api/v1` pour futur front mobile.
9. Passer `APP_DEBUG=false`, configurer SMTP réel, queue driver, et déploiement (Forge/VPS).

## 15. Commandes utiles

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate
npm run dev          # Vite dev server
php artisan serve    # http://localhost:8000
php artisan test     # tests (squelette)
./vendor/bin/pint    # formatage PHP
```

---

**Conclusion** : AquaSecure est un prototype UI/UX très abouti (50+ vues Blade, design system, accessibilité, responsive, 47 routes) mais entièrement découplé de toute persistance : données 100 % statiques via `PlaceholderData`, logique dans les closures de `web.php`, authentification simulée. Le passage en production nécessite la création d'une couche domaine (modèles, migrations, contrôleurs, middleware d'autorisation, auth réelle, tests).
