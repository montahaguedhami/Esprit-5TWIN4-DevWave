# AquaSecure

AquaSecure est une application web de démonstration pour le suivi et la gestion d’un réseau d’eau. Elle propose des espaces adaptés aux habitants, aux techniciens, aux gestionnaires et aux administrateurs.

> **Prototype de démonstration :** plusieurs écrans s’appuient sur des données fictives et des actions simulées. AquaSecure est une plateforme logicielle ; ce projet ne fournit pas de capteurs ni de matériel IoT.

## Fonctionnalités

- **Espace habitant** : tableau de bord, signalements, factures et notifications.
- **Espace technicien** : interventions, rapports de terrain et inventaire d’équipements.
- **Espace gestionnaire** : incidents, carte et qualité du réseau, équipes, projets, budget, rapports et analyses.
- **Espace administrateur** : utilisateurs, rôles, journaux, sécurité, système et sauvegardes.
- **Interface publique** : présentation de la plateforme et formulaire de demande de démonstration.
- **Assistant de démonstration** : réponses prédéfinies côté navigateur ; il n’est pas relié à un service d’IA.

Les exemples de tableaux et indicateurs sont définis principalement dans `app/Data/PlaceholderData.php`. Les routes web se trouvent dans `routes/web.php`.

## Technologies

- PHP 8.2 ou plus récent
- Laravel 12
- Blade
- SQLite
- Node.js et npm
- Vite 7, Tailwind CSS 4 et JavaScript

## Installation locale

### Prérequis

Installez PHP 8.2+, Composer, Node.js avec npm, ainsi que les extensions PHP requises par Laravel et SQLite (`pdo_sqlite` notamment).

### 1. Installer les dépendances

À la racine du dépôt :

```sh
composer install
npm install
```

### 2. Préparer l’environnement

Copiez `.env.example` vers `.env`, puis générez la clé de l’application :

```sh
# macOS / Linux
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
```

Sous Windows PowerShell :

```powershell
Copy-Item .env.example .env
php artisan key:generate
if (-not (Test-Path database/database.sqlite)) {
    New-Item -ItemType File database/database.sqlite | Out-Null
}
```

Dans `.env`, utilisez les pilotes locaux suivants pour la démonstration :

```dotenv
DB_CONNECTION=sqlite
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### 3. Préparer la base et les fichiers frontend

```sh
php artisan migrate
npm run build
```

### 4. Démarrer l’application

Dans un terminal :

```sh
php artisan serve
```

Ouvrez <http://127.0.0.1:8000>. Pour travailler sur le frontend avec le rechargement automatique, lancez aussi `npm run dev` dans un second terminal.

## Comptes de démonstration

La connexion reconnaît les adresses ci-dessous, déclarées dans `routes/web.php` :

| Espace | Adresse de démonstration |
| --- | --- |
| Habitant | `citoyen@aquasecure.tn` |
| Technicien | `amira@aquasecure.tn` |
| Gestionnaire | `gestionnaire@aquasecure.tn` |
| Administrateur | `admin@aquasecure.tn` |

Le formulaire de connexion est simulé : dans l’implémentation actuelle, le serveur choisit le compte en fonction de l’adresse et ne vérifie pas le mot de passe. N’utilisez pas cette authentification en production.

## Commandes utiles

```sh
npm run dev          # Vite en mode développement
npm run build        # Compiler les fichiers frontend
php artisan serve    # Lancer le serveur Laravel
php artisan migrate  # Appliquer les migrations
php artisan test     # Lancer la suite PHPUnit
```

## Structure du dépôt

```text
app/
  Data/PlaceholderData.php   Données d’exemple
  Models/                    Modèles Laravel
resources/
  css/                        Styles et configuration Tailwind
  js/                         Entrées JavaScript
  views/                      Vues Blade et composants
routes/web.php                Pages et parcours de démonstration
database/migrations/          Migrations Laravel
```

## Limites du prototype

- Les données d’exemple ne proviennent pas d’un service de gestion de réseau.
- La connexion, l’inscription, les signalements, les rapports, les notifications, les factures et certaines actions d’administration sont simulés ou incomplets.
- L’assistant utilise des réponses préparées ; aucun fournisseur d’IA n’est configuré.
- Les écrans ne constituent pas une authentification ni une application prête à être déployée en production.

Avant tout déploiement, il faut notamment mettre en place une authentification et des autorisations serveur robustes, la validation et la persistance des données, ainsi que la gestion sécurisée des secrets et des sessions.
