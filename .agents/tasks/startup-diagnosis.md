# AquaSecure — Diagnostic de démarrage

**Date du diagnostic :** Investigation complète du projet Laravel 12

---

## Résumé exécutif

**Un seul bloquant critique** empêche tout le reste : la base de données MariaDB (XAMPP) est dans un état corrompu. Son répertoire `data/` est incomplet — les tables système (`mysql/`) sont absentes, elles se trouvent encore dans `data_old/`. Laravel ne peut donc pas se connecter, et aucune migration ne peut tourner. **Tout le reste du projet est sain.**

---

## Problèmes bloquants — liste ordonnée

---

### 🔴 BLOQUANT 1 — MariaDB : répertoire `data/` sans tables système

**Problème exact :**  
XAMPP tourne avec un répertoire `data/` vide de ses tables système (`mysql`, `performance_schema`, `phpmyadmin`). Ces dossiers existent dans `c:\xampp\mysql\data_old\` mais pas dans `c:\xampp\mysql\data\`. MariaDB démarre mais ne peut pas ouvrir ses propres tables de privilèges :

```
[ERROR] Can't open and lock privilege tables: Table 'mysql.servers' doesn't exist
[ERROR] InnoDB: Cannot open datafile for read-only: '.\mysql\innodb_index_stats.ibd'
[ERROR] Could not open mysql.plugin table.
```

Conséquence : toute connexion TCP à `127.0.0.1:3306` reste en attente (hanging), y compris depuis PHP PDO, `mysql.exe`, et `php artisan`. La base `aquasecure` **existe physiquement** dans `data/` mais est inaccessible.

**Fichier/commande concerné :**  
- `c:\xampp\mysql\data\` — absent : `mysql/`, `performance_schema/`, `phpmyadmin/`  
- `c:\xampp\mysql\data_old\` — contient : `mysql/`, `performance_schema/`, `phpmyadmin/`, `aquasecure/`, `laravel/`  
- `c:\xampp\mysql\data\mysql_error.log` — confirme les erreurs au démarrage

**Correction exacte :**  
Arrêter MariaDB depuis XAMPP Control Panel, copier les dossiers système manquants de `data_old` vers `data`, puis redémarrer.

```powershell
# 1. Arrêter MySQL dans XAMPP Control Panel d'abord, PUIS :
Copy-Item "c:\xampp\mysql\data_old\mysql"             "c:\xampp\mysql\data\mysql"             -Recurse -Force
Copy-Item "c:\xampp\mysql\data_old\performance_schema" "c:\xampp\mysql\data\performance_schema" -Recurse -Force
Copy-Item "c:\xampp\mysql\data_old\phpmyadmin"         "c:\xampp\mysql\data\phpmyadmin"         -Recurse -Force
# 2. Relancer MySQL dans XAMPP Control Panel
```

> **Alternatively (option plus propre) :** changer le `datadir` dans `c:\xampp\mysql\bin\my.ini` pour pointer vers `data_old` au lieu de `data`.

---

### 🟡 MINEUR 2 — `SESSION_DRIVER=database` / `CACHE_STORE=database` / `QUEUE_CONNECTION=database` — tables pas encore créées

**Problème exact :**  
Ces trois drivers nécessitent des tables SQL (`sessions`, `cache`, `jobs`). Les migrations existent (`0001_01_01_000000_create_users_table.php` crée `sessions` ; `0001_01_01_000001_create_cache_table.php` ; `0001_01_01_000002_create_jobs_table.php`) mais n'ont jamais été exécutées (MariaDB injoignable). Une fois la DB réparée, il faudra lancer `php artisan migrate`.

**Correction :** Exécuter les migrations (voir séquence de démarrage ci-dessous).

---

### ✅ Non-bloquant — Tout le reste est en ordre

| Vérification | État | Détail |
|---|---|---|
| **APP_KEY** | ✅ OK | `base64:9FExvlfyY/QP7Qs...` défini dans `.env` |
| **JWT_SECRET** | ✅ OK | Défini dans `.env` (64 chars) |
| **config/jwt.php** | ✅ OK | Fichier présent (vendor:publish déjà fait) |
| **vendor/autoload.php** | ✅ OK | `vendor/` complet |
| **tymon/jwt-auth** | ✅ OK | `vendor/tymon/jwt-auth/` présent |
| **Extensions PHP** | ✅ OK | `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath` — toutes actives |
| **PHP version** | ✅ OK | PHP 8.5.10 NTS x64 |
| **Laravel version** | ✅ OK | Laravel Framework 12.70.0 |
| **bootstrap/app.php** | ✅ OK | 4 middleware aliases enregistrés, tous existent comme classes |
| **Middlewares** | ✅ OK | `CheckRole`, `CheckPermission`, `JwtAuthenticate`, `JwtCheckRole` — 4 classes trouvées |
| **Routes** | ✅ OK | 138 routes listées sans erreur |
| **public/build/** | ✅ OK | Assets Vite compilés (`manifest.json` + `assets/`) |
| **node_modules/** | ✅ OK | Existe |
| **Migration sessions** | ✅ OK | Dans `0001_01_01_000000_create_users_table.php` |
| **DB_CONNECTION** | ℹ️ Note | Configuré `mysql` mais serveur est MariaDB 10.4.32 — compatible, pas de problème |
| **Port MySQL** | ✅ OK | `.env` dit 3306, MariaDB écoute sur 3306 |
| **Mot de passe root** | ✅ OK | Vide (`DB_PASSWORD=`) — conforme à XAMPP par défaut |

---

## Analyse détaillée

### DB — connexion

La commande `netstat -an` confirme que MariaDB écoute bien sur `0.0.0.0:3306` et qu'il y a des connexions ESTABLISHED (depuis phpMyAdmin probablement). Cependant, le client `mysql.exe` et PHP PDO tombent en timeout au handshake. Le log MariaDB révèle que le serveur ne peut pas charger ses tables de privilèges au démarrage — il refuse donc tout nouveau client après un certain point.

La base `aquasecure` **existe** dans `c:\xampp\mysql\data\aquasecure\` mais est inaccessible jusqu'à ce que le serveur soit réparé.

### PHP — extensions

`php -m` confirme la présence de toutes les extensions nécessaires. Le `php.ini` chargé est `C:\Users\monta\Downloads\php-8.5.10-nts-Win32-vs17-x64\php.ini` (PHP standalone, pas celui de XAMPP). Il active `extension=pdo_mysql` et `extension=mysqli`. Aucun problème.

### Bootstrap / Middleware

`bootstrap/app.php` déclare 4 alias :
- `role` → `App\Http\Middleware\CheckRole`
- `permission` → `App\Http\Middleware\CheckPermission`
- `jwt.auth` → `App\Http\Middleware\JwtAuthenticate`
- `jwt.role` → `App\Http\Middleware\JwtCheckRole`

Toutes ces classes ont été vérifiées et existent. Pas de référence à une classe manquante.

### Routes

`php artisan route:list` retourne 138 routes sans erreur de parsing ni exception. Le routing est sain.

### Migrations

18 fichiers de migration couvrant :
- `users`, `password_reset_tokens`, `sessions` (dans `0001_01_01_000000`)
- `cache` (dans `0001_01_01_000001`)
- `jobs` (dans `0001_01_01_000002`)
- Tables métier : `financements`, `projets`, `zones`, `infrastructures`, `incidents`, `action_correctives`, `techniciens`, `interventions`, `point_mesures`, `mesures_qualites`
- `rbac_tables`, `jwt_refresh_tokens`

Aucune migration pour `sessions` séparée — c'est normal, elle est dans la migration users standard.

---

## Séquence de démarrage

Une fois le problème DB résolu, exécuter ces commandes dans l'ordre :

```powershell
# ─── ÉTAPE 0 : Réparer MariaDB (une seule fois) ────────────────────────────
# Dans XAMPP Control Panel : cliquer "Stop" sur MySQL
# Puis en PowerShell :
Copy-Item "c:\xampp\mysql\data_old\mysql"             "c:\xampp\mysql\data\mysql"             -Recurse -Force
Copy-Item "c:\xampp\mysql\data_old\performance_schema" "c:\xampp\mysql\data\performance_schema" -Recurse -Force
# Dans XAMPP Control Panel : cliquer "Start" sur MySQL
# Vérifier que MariaDB démarre sans erreur dans les logs XAMPP

# ─── ÉTAPE 1 : Créer la base si elle n'existe pas encore ────────────────────
c:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS aquasecure CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# ─── ÉTAPE 2 : Aller dans le répertoire du projet ───────────────────────────
cd "c:\Users\monta\Downloads\Esprit-5TWIN4-DevWave-main (2)\Esprit-5TWIN4-DevWave-main"

# ─── ÉTAPE 3 : Nettoyer les caches Laravel ──────────────────────────────────
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# ─── ÉTAPE 4 : Lancer les migrations ────────────────────────────────────────
php artisan migrate

# ─── ÉTAPE 5 : (Optionnel) Lancer les seeders si existants ──────────────────
php artisan db:seed

# ─── ÉTAPE 6 : Démarrer le serveur de développement ─────────────────────────
php artisan serve
# Le projet sera disponible sur http://127.0.0.1:8000
```

> **Note :** `npm run dev` n'est pas nécessaire car `public/build/` contient déjà les assets compilés.

---

## Conclusion

Le projet AquaSecure est bien développé et complet. Code, routes, middlewares, extensions PHP, variables d'environnement — tout est en ordre. **Le seul et unique bloquant est que XAMPP a démarré avec un `data/` tronqué** (sans les répertoires système `mysql/` et `performance_schema/`), vraisemblablement suite à une réinitialisation ou migration de données dans les sessions précédentes de déploiement. La correction est une simple copie de dossiers depuis `data_old/`.
