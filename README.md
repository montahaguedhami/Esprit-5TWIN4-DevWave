# AquaSecure

**AquaSecure** est un projet de plateforme de démonstration consacré à la gestion des services liés à l’eau. Il met en scène les échanges entre les habitants, les équipes de terrain et les responsables du réseau, avec une vision commune des besoins et des interventions.

Le projet s’appuie sur un contexte tunisien et propose plusieurs espaces adaptés aux différents acteurs. Il permet de découvrir comment les signalements, le suivi des infrastructures et la planification des actions peuvent être réunis dans une même plateforme.

> AquaSecure est un prototype de démonstration. Certaines pages et actions utilisent des données fictives ou un fonctionnement simulé. Il ne s’agit pas d’un service public opérationnel.

## À quoi sert AquaSecure ?

La plateforme donne une vue d’ensemble du réseau d’eau et aide à suivre les demandes et les opérations qui le concernent. Selon son rôle, une personne peut consulter les informations utiles, transmettre un signalement ou suivre des activités liées à la maintenance.

Le projet cherche notamment à rendre plus lisibles les informations sur les zones desservies, les équipements, les incidents et les travaux en cours. Il présente aussi une approche de suivi de la qualité de l’eau et de repérage géographique des éléments du réseau.

## Les espaces proposés

- **Habitants** : consulter des informations sur le réseau, transmettre des signalements et retrouver des notifications ou des factures de démonstration.
- **Techniciens** : consulter les interventions et les équipements associés, organiser une tournée et renseigner des rapports de terrain.
- **Gestionnaires** : suivre les incidents, les zones et les infrastructures, visualiser le réseau sur une carte, et consulter des informations sur la qualité de l’eau, les projets et les budgets.
- **Administrateurs** : accéder aux outils de gestion des comptes et des paramètres de la plateforme.

## Ce que l’on peut découvrir

- Le suivi des incidents, de leur déclaration à leur traitement.
- La consultation des zones et des infrastructures du réseau.
- La préparation et le suivi d’interventions de maintenance.
- Une carte pour explorer les emplacements des équipements et des points de mesure.
- Des écrans de suivi de la qualité de l’eau, des projets et des budgets.
- Des tableaux de bord adaptés aux différents profils.

## Lancer la démonstration en local

Pour afficher le projet sur votre ordinateur, il faut disposer de PHP 8.2 ou plus, de Composer, de Node.js et de npm.

```sh
composer install
npm install
```

Copiez ensuite `.env.example` dans un fichier `.env`, puis préparez l’application et sa base de données :

```sh
php artisan key:generate
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

### Emails des actions correctives

Par défaut, `.env.example` utilise `MAIL_MAILER=log` : les emails sont alors écrits dans `storage/logs/laravel.log` et ne sont pas envoyés à Gmail. Pour envoyer les notifications au citoyen, configurez le SMTP Gmail dans votre fichier `.env` (ne partagez et ne committez jamais le mot de passe d’application) :

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-adresse@gmail.com
MAIL_PASSWORD=mot-de-passe-d-application-google
MAIL_FROM_ADDRESS=votre-adresse@gmail.com
MAIL_FROM_NAME="AquaSecure"
```

Utilisez un mot de passe d’application Google, pas le mot de passe normal du compte. Après modification, exécutez `php artisan config:clear`.

### 3. Préparer la base et les fichiers frontend

```sh
php artisan migrate
npm run build
php artisan serve
```

Ouvrez ensuite <http://127.0.0.1:8000> dans votre navigateur. Pendant le développement de l’interface, `npm run dev` permet de compiler les ressources au fil des modifications.

## Accès de démonstration

L’écran de connexion reconnaît les adresses de démonstration ci-dessous :

| Profil | Adresse |
| --- | --- |
| Habitant | `citoyen@aquasecure.tn` |
| Technicien | `amira@aquasecure.tn` |
| Gestionnaire | `gestionnaire@aquasecure.tn` |
| Administrateur | `admin@aquasecure.tn` |

La connexion est simulée pour la démonstration et ne constitue pas un système d’authentification destiné à un déploiement réel.

## Cadre du projet

AquaSecure est développé comme un projet de démonstration. Les contenus, comptes, indicateurs et scénarios affichés peuvent être fictifs. Les fonctionnalités présentées illustrent une organisation possible du suivi d’un réseau d’eau et ne remplacent pas les outils ni les données des opérateurs concernés.
