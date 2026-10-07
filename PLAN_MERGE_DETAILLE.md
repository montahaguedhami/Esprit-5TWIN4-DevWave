# 🎯 PLAN DE MERGE DÉTAILLÉ

Branch source: `Ghada-projets-financements`  
Branch cible: `main`  
Date: 7 octobre 2026

---

## 📋 CHECKLIST PRÉ-MERGE

- [x] Analyse architecture équipe (main)
- [x] Analyse ton architecture (Ghada-projets-financements)
- [x] Identification des conflits potentiels
- [x] Décision user_id: NON (cohérent avec équipe)
- [ ] Backup branche actuelle
- [ ] Merge main dans ta branche
- [ ] Résolution conflits
- [ ] Tests fonctionnels
- [ ] Push vers GitHub

---

## 🔍 FICHIERS MODIFIÉS (CONFLITS POTENTIELS)

### 1. database/seeders/DatabaseSeeder.php

#### VERSION MAIN
```php
public function run(): void {
    User::firstOrCreate(['email' => 'test@example.com'], [...]);
    User::firstOrCreate(['email' => 'citoyen@aquasecure.tn'], [...]);
    
    $this->call([
        IncidentSeeder::class,
        ActionCorrectiveSeeder::class,
        TechnicienSeeder::class,
    ]);
}
```

#### VERSION TOI
```php
public function run(): void {
    User::factory()->create(['email' => 'test@example.com']);
    
    // 15 projets + financements
    $projets = Projet::factory(15)->create();
    foreach ($projets as $projet) {
        Financement::factory()->create(['projet_id' => $projet->id]);
    }
    
    // Projets spéciaux (sans financement, totalement financé, etc.)
}
```

#### SOLUTION: COMBINER
```php
public function run(): void {
    // Users de l'équipe
    User::firstOrCreate(['email' => 'test@example.com'], [...]);
    User::firstOrCreate(['email' => 'citoyen@aquasecure.tn'], [...]);
    
    // Seeders de l'équipe
    $this->call([
        IncidentSeeder::class,
        ActionCorrectiveSeeder::class,
        TechnicienSeeder::class,
    ]);
    
    // Tes projets et financements
    $projets = Projet::factory(15)->create();
    foreach ($projets as $projet) {
        $nombreFinancements = match($projet->statut) {
            'planifie' => fake()->numberBetween(0, 1),
            'en_cours' => fake()->numberBetween(1, 3),
            'termine' => fake()->numberBetween(2, 4),
            'suspendu' => fake()->numberBetween(1, 2),
            'annule' => fake()->numberBetween(0, 1),
            default => fake()->numberBetween(0, 2),
        };
        
        if ($nombreFinancements > 0) {
            for ($i = 0; $i < $nombreFinancements; $i++) {
                Financement::factory()->create(['projet_id' => $projet->id]);
            }
        }
    }
}
```

---

### 2. resources/views/citizen/dashboard.blade.php

#### CONFLIT PROBABLE
- **Main:** Ajout lien "Travaux" (module Ons)
- **Toi:** Statistiques projets

#### SOLUTION: COMBINER
```blade
<!-- Garder les deux fonctionnalités -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Stats de l'équipe -->
    <x-stat-card ... />
    
    <!-- Tes stats projets -->
    <x-stat-card 
        title="Projets en cours"
        :value="$projetsEnCours"
        icon="..." />
</div>

<!-- Liens de l'équipe + tes liens -->
<a href="{{ route('citizen.travaux.index') }}">Travaux</a>
<a href="{{ route('citizen.projets.index') }}">Projets</a>
```

---

### 3. resources/views/components/mobile-nav.blade.php

#### CONFLIT PROBABLE
- **Main:** Lien "Travaux"
- **Toi:** Lien "Projets"

#### SOLUTION: AJOUTER LES DEUX
```blade
<!-- Citizen Nav -->
<a href="{{ route('citizen.travaux.index') }}">Travaux</a>
<a href="{{ route('citizen.projets.index') }}">Projets</a>
```

---

### 4. resources/views/layouts/manager.blade.php

#### CONFLIT PROBABLE
- **Main:** Menu "Techniciens" et "Interventions"
- **Toi:** Menu "Projets" et "Financements"

#### SOLUTION: GARDER TOUS LES MENUS
```blade
<nav>
    <!-- Menus équipe -->
    <a href="{{ route('manager.techniciens.index') }}">Techniciens</a>
    <a href="{{ route('manager.interventions.index') }}">Interventions</a>
    <a href="{{ route('manager.incidents') }}">Incidents</a>
    <a href="{{ route('manager.actions.index') }}">Actions</a>
    
    <!-- Tes menus -->
    <a href="{{ route('manager.projets.index') }}">Projets</a>
    <a href="{{ route('manager.financements.index') }}">Financements</a>
</nav>
```

---

### 5. resources/views/layouts/admin.blade.php

#### CONFLIT: Mineur (ajouts CSS ou scripts)

#### SOLUTION: MERGER LES AJOUTS

---

### 6. package-lock.json

#### CONFLIT: Fichier généré automatiquement

#### SOLUTION: RÉGÉNÉRER
```bash
rm package-lock.json
npm install
```

---

## 🚀 ÉTAPES D'EXÉCUTION

### Étape 1: Backup (SÉCURITÉ) ✅

```bash
# Créer une branche de backup
git branch backup-ghada-projets-$(date +%Y%m%d)

# Vérifier
git branch -a
```

**Résultat attendu:**
```
* Ghada-projets-financements
  backup-ghada-projets-20261007
  main
```

---

### Étape 2: Fetch latest main ✅

```bash
git fetch origin
git log origin/main --oneline --max-count=5
```

**Vérifier les derniers commits de l'équipe**

---

### Étape 3: Merge main → ta branche ⚠️

```bash
# S'assurer d'être sur ta branche
git checkout Ghada-projets-financements

# Merger main dans ta branche
git merge origin/main
```

**Résultats possibles:**

#### A) Merge automatique réussi ✅
```
Auto-merging database/seeders/DatabaseSeeder.php
Merge made by the 'ort' strategy.
```
→ Passer à Étape 4

#### B) Conflits détectés ⚠️
```
CONFLICT (content): Merge conflict in database/seeders/DatabaseSeeder.php
CONFLICT (content): Merge conflict in resources/views/citizen/dashboard.blade.php
Automatic merge failed; fix conflicts and then commit the result.
```
→ Résoudre les conflits (voir Étape 3.1)

---

### Étape 3.1: Résolution des conflits 🔧

#### Identifier les conflits
```bash
git status
```

**Exemple output:**
```
Unmerged paths:
  both modified:   database/seeders/DatabaseSeeder.php
  both modified:   resources/views/citizen/dashboard.blade.php
  both modified:   resources/views/components/mobile-nav.blade.php
  both modified:   resources/views/layouts/manager.blade.php
  both modified:   package-lock.json
```

#### Résoudre chaque conflit

**Format des marqueurs Git:**
```php
<<<<<<< HEAD (ta version)
// Ton code
=======
// Code de main
>>>>>>> origin/main
```

**Pour chaque fichier:**

1. Ouvrir le fichier
2. Chercher `<<<<<<<`
3. Analyser les deux versions
4. Créer version combinée
5. Supprimer les marqueurs Git
6. Sauvegarder

**Exemple DatabaseSeeder.php:**
```bash
# Ouvrir dans l'éditeur
code database/seeders/DatabaseSeeder.php
```

Avant (avec conflit):
```php
<<<<<<< HEAD
User::factory()->create(['email' => 'test@example.com']);
$projets = Projet::factory(15)->create();
=======
User::firstOrCreate(['email' => 'test@example.com'], [...]);
User::firstOrCreate(['email' => 'citoyen@aquasecure.tn'], [...]);
$this->call([IncidentSeeder::class, TechnicienSeeder::class]);
>>>>>>> origin/main
```

Après (résolu):
```php
// Combiner les deux
User::firstOrCreate(['email' => 'test@example.com'], [...]);
User::firstOrCreate(['email' => 'citoyen@aquasecure.tn'], [...]);

$this->call([
    IncidentSeeder::class,
    ActionCorrectiveSeeder::class,
    TechnicienSeeder::class,
]);

// Tes projets
$projets = Projet::factory(15)->create();
foreach ($projets as $projet) {
    Financement::factory()->create(['projet_id' => $projet->id]);
}
```

#### Marquer comme résolu
```bash
git add database/seeders/DatabaseSeeder.php
git add resources/views/citizen/dashboard.blade.php
git add resources/views/components/mobile-nav.blade.php
git add resources/views/layouts/manager.blade.php

# Supprimer package-lock.json et régénérer
git rm package-lock.json
npm install
git add package-lock.json
```

#### Finaliser le merge
```bash
git commit -m "chore: merge main into Ghada-projets-financements

- Combine DatabaseSeeder with team's seeders
- Add Projets links in citizen dashboard
- Add Travaux + Projets in mobile nav
- Add all modules in manager layout
- Regenerate package-lock.json"
```

---

### Étape 4: Vérifications post-merge 🔍

#### 4.1 Vérifier qu'il n'y a plus de marqueurs de conflit
```bash
# Chercher les marqueurs Git restants
git grep '<<<<<<<' || echo "✅ Aucun conflit restant"
git grep '=======' || echo "✅ Aucun conflit restant"
git grep '>>>>>>>' || echo "✅ Aucun conflit restant"
```

#### 4.2 Vérifier les migrations
```bash
php artisan migrate:status
```

**Résultat attendu:**
```
+------+----------------------------------------------------------------+-------+
| Ran? | Migration                                                      | Batch |
+------+----------------------------------------------------------------+-------+
| Yes  | 0001_01_01_000000_create_users_table                          | 1     |
| Yes  | 2026_10_06_031836_create_projets_table                        | 2     |
| Yes  | 2026_10_06_031836_create_financements_table                   | 2     |
| Yes  | 2026_10_07_000001_create_incidents_table                      | 3     |
| Yes  | 2026_10_07_000002_create_action_correctives_table             | 3     |
| Yes  | 2026_10_07_115712_create_techniciens_table                    | 3     |
| Yes  | 2026_10_07_115713_create_interventions_table                  | 3     |
| Yes  | 2026_10_07_113243_add_progression_to_projets_table            | 4     |
+------+----------------------------------------------------------------+-------+
```

#### 4.3 Vérifier les routes
```bash
php artisan route:list | grep -E "(projet|financement|incident|technicien)"
```

**Résultat attendu:**
```
GET|HEAD  manager/projets ...................... manager.projets.index
GET|HEAD  manager/financements ................ manager.financements.index
GET|HEAD  citizen/projets ..................... citizen.projets.index
GET|HEAD  manager/incidents ................... manager.incidents
GET|HEAD  manager/techniciens ................. manager.techniciens.index
GET|HEAD  citizen/travaux ..................... citizen.travaux.index
```

#### 4.4 Optimiser Laravel
```bash
php artisan optimize:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

---

### Étape 5: Tests fonctionnels 🧪

#### 5.1 Reset base de données et seed
```bash
php artisan migrate:fresh --seed
```

**Vérifier:**
- ✅ Migrations exécutées sans erreur
- ✅ Seeders exécutés sans erreur
- ✅ Données créées (users, projets, financements, incidents, techniciens)

#### 5.2 Lancer le serveur
```bash
php artisan serve
```

#### 5.3 Tests manuels

**A) Test Login Manager**
1. Aller sur http://127.0.0.1:8000/login
2. Email: `gestionnaire@aquasecure.tn`
3. Mot de passe: (n'importe quoi, c'est demo)
4. ✅ Redirigé vers manager dashboard

**B) Test CRUD Projets (Manager)**
1. Menu → Projets
2. ✅ Liste des projets affichée
3. Cliquer "Créer projet"
4. ✅ Formulaire affiché
5. Remplir et enregistrer
6. ✅ Projet créé avec succès
7. Cliquer sur un projet
8. ✅ Détails + financements affichés

**C) Test CRUD Financements (Manager)**
1. Menu → Financements
2. ✅ Liste des financements
3. Créer nouveau financement
4. ✅ Formulaire + select projets
5. Enregistrer
6. ✅ Financement créé

**D) Test Menu Manager**
1. Vérifier présence de:
   - ✅ Projets
   - ✅ Financements
   - ✅ Techniciens
   - ✅ Interventions
   - ✅ Incidents

**E) Test Citizen**
1. Se déconnecter
2. Login: `citoyen@aquasecure.tn`
3. ✅ Dashboard citoyen
4. Menu → Projets
5. ✅ Liste projets (lecture seule)
6. Cliquer sur projet
7. ✅ Détails + progression + financements
8. Menu → Travaux
9. ✅ Liste des travaux (module équipe)

**F) Test Carte géolocalisation**
1. Manager → Projets
2. Cliquer "Carte"
3. ✅ Carte affichée avec marqueurs projets

---

### Étape 6: Vérification code quality 📊

#### 6.1 Vérifier syntaxe PHP
```bash
php artisan about
```

#### 6.2 Vérifier pas de N+1 queries évidentes
Examiner manuellement les controllers:
- ✅ `with('financements')` utilisé
- ✅ Pas de boucles avec queries

#### 6.3 Vérifier assets compilés
```bash
npm run build
```

---

### Étape 7: Commit final et push 🚀

#### 7.1 Vérifier l'état
```bash
git status
git log --oneline -5
```

#### 7.2 Push vers GitHub
```bash
git push origin Ghada-projets-financements
```

**Résultat attendu:**
```
Enumerating objects: XX, done.
Counting objects: 100% (XX/XX), done.
Writing objects: 100% (XX/XX), XX KiB | XX MiB/s, done.
Total XX (delta XX), reused 0 (delta 0), pack-reused 0
To https://github.com/montahaguedhami/Esprit-5TWIN4-DevWave.git
   9a82a87..XXXXXXX  Ghada-projets-financements -> Ghada-projets-financements
```

---

### Étape 8: Créer Pull Request (optionnel) 📝

Si l'équipe utilise des PRs:

1. Aller sur GitHub
2. Créer Pull Request: `Ghada-projets-financements` → `main`
3. Titre: `feat: Add Projets and Financements module with geolocation`
4. Description:
```markdown
## Module Projets et Financements

### Features
- ✅ CRUD complet Projets (Manager)
- ✅ CRUD complet Financements (Manager)
- ✅ Consultation projets (Citizen, lecture seule)
- ✅ Géolocalisation avec carte interactive
- ✅ Calculs automatiques (budget restant, % financé)
- ✅ Factories et Seeders avec données tunisiennes
- ✅ Validations Form Requests
- ✅ Templates Blade avec composants

### Architecture
- Models: `Projet`, `Financement`
- Relations: `Projet hasMany Financement`
- Migrations: 3 fichiers (tables + progression)
- Routes: 16 routes (8 Manager + 8 Citizen)
- Views: 10 vues Blade

### Integration
- ✅ Merge main (incidents, techniciens, interventions)
- ✅ Menus combinés dans layouts
- ✅ Navigation mobile mise à jour
- ✅ DatabaseSeeder combiné
```

5. Assigner reviewers (ton équipe)
6. Attendre validation

**OU** merger directement dans main si pas de PR process:
```bash
git checkout main
git merge Ghada-projets-financements
git push origin main
```

---

## ✅ CHECKLIST FINALE

Avant de considérer le merge terminé:

- [ ] Backup créé
- [ ] Merge réussi (pas de conflits restants)
- [ ] Pas de `<<<<<<<` dans le code
- [ ] Migrations OK (`migrate:status`)
- [ ] Routes OK (`route:list`)
- [ ] Seeders OK (`migrate:fresh --seed`)
- [ ] Login Manager fonctionne
- [ ] CRUD Projets fonctionne
- [ ] CRUD Financements fonctionne
- [ ] Citizen peut voir projets
- [ ] Géolocalisation fonctionne
- [ ] Menus combinés affichés
- [ ] Module équipe (Incidents/Techniciens) fonctionne toujours
- [ ] Assets compilés (`npm run build`)
- [ ] Push vers GitHub réussi

---

## 🆘 EN CAS DE PROBLÈME

### Problème: Merge conflit impossible à résoudre

**Solution 1: Abort et recommencer**
```bash
git merge --abort
git log origin/main --oneline -10
# Analyser les commits problématiques
```

**Solution 2: Merge fichier par fichier**
```bash
git merge --abort
git checkout origin/main -- path/to/file.php
# Réappliquer tes modifications manuellement
```

### Problème: Tests échouent après merge

**Solution: Rollback au backup**
```bash
git reset --hard backup-ghada-projets-20261007
# Analyser le problème
# Refaire le merge avec corrections
```

### Problème: Migrations échouent

**Vérifier:**
```bash
php artisan migrate:status
php artisan migrate:rollback --step=1
# Vérifier le fichier de migration
php artisan migrate
```

---

## 📞 DEMANDER DE L'AIDE

Si tu es bloquée pendant le merge, **STOP** et demande-moi:

1. Copie le message d'erreur complet
2. Indique à quelle étape tu es
3. Envoie le résultat de `git status`

**NE PAS faire:**
- ❌ `git reset --hard origin/main` (perte de ton travail)
- ❌ Supprimer des fichiers manuellement
- ❌ Forcer le push (`--force`)

---

## ✅ PRÊT À COMMENCER?

Dis-moi "**OUI, on commence**" et je lance l'Étape 1! 🚀
