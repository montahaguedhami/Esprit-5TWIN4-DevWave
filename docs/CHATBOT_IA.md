# Chatbot IA — AquaSecure (Projets et Financements)

## 1. Objectif et périmètre

Le chatbot IA d'AquaSecure est un assistant conversationnel intégré au module **Projets et Financements**. Il répond aux questions concernant exclusivement :

- Les projets disponibles et leurs informations (objectifs, budget, statut, progression, zones).
- Les conditions de financement (montant minimum, éligibilité, workflow).
- Les demandes de financement (statuts, procédure, décisions).
- Les budgets et soldes disponibles.
- Les paiements simulés (aucun argent réel).
- Les contributions et le portefeuille personnel.
- Les reçus et justificatifs.
- Les fonctionnalités de gestion accessibles au manager.
- Les recommandations de projets (moteur V1 à règles).
- Les procédures documentées de l'application.

**Le chatbot ne permet aucune écriture :** il est en lecture seule. Il ne peut ni créer, ni modifier, ni supprimer de données, ni approuver ou rejeter une demande, ni déclencher un paiement.

## 2. Architecture réellement impliquée

Laravel reste l'orchestrateur principal. Aucun microservice IA n'est ajouté.

```
Frontend (Blade + JS vanilla)
    → POST /chatbot/ask  (route web, authentifiée, protégée par role:)
    → Validation (ChatbotRequest)
    → ChatbotOrchestrator
        → Détermine le rôle depuis Laravel (session)
        → Construit la liste des outils autorisés (ToolRegistry)
        → Détermine si le RAG est pertinent
        → Appelle Gemini Flash (uniquement les outils autorisés)
        → Exécute les outils métier via Laravel (RBAC à chaque exécution)
        → Retourne la réponse filtrée + sources
    → Frontend
```

### Composants créés

| Composant | Chemin | Responsabilité |
|-----------|--------|----------------|
| Configuration | `config/chatbot.php` | Variables d'environnement, modèle Gemini, limites |
| Client Gemini | `app/Services/Chatbot/GeminiClient.php` | Adaptateur HTTP vers l'API Gemini Flash |
| ServiceProvider | `app/Providers/ChatbotServiceProvider.php` | Enregistrement du client Gemini |
| Registre d'outils | `app/Services/Chatbot/ToolRegistry.php` | Définition des 13 outils autorisés avec schéma et rôles |
| Interface exécuteur | `app/Services/Chatbot/ToolExecutor.php` | Contrat pour chaque outil (nom, autorisation, validation, exécution) |
| Exécuteurs (13) | `app/Services/Chatbot/Tools/*.php` | Implémentation de chaque outil métier (lecture seule) |
| Orchestrateur | `app/Services/Chatbot/ChatbotOrchestrator.php` | Flux complet : validation, rôle, outils, RAG, Gemini, filtrage |
| Contrôleur HTTP | `app/Http/Controllers/ChatbotController.php` | Endpoint `/chatbot/ask` et `/chatbot/status` |
| Requête de validation | `app/Http/Requests/ChatbotRequest.php` | Validation du message (taille, format) |
| Ingestion documentaire | `app/Services/Chatbot/DocumentIngestionService.php` | Lecture des Markdown, chunking, indexation |
| Recherche documentaire | `app/Services/Chatbot/DocumentSearchService.php` | Recherche textuelle (full-text MySQL + fallback LIKE) |
| Modèle de chunk | `app/Models/ChatbotDocumentChunk.php` | Passage indexé avec métadonnées |
| Commande Artisan | `app/Console/Commands/ChatbotIngestDocuments.php` | `php artisan chatbot:ingest-documents` |
| Contrôleur | `app/Http/Controllers/ChatbotController.php` | Endpoint HTTP |
| Frontend | `resources/views/components/ai-assistant.blade.php` | Widget de chat (remplace le mock existant) |

### Base de données

Nouvelle migration non destructive :
- `database/migrations/2026_10_19_000001_create_chatbot_document_chunks_table.php`
- Table `chatbot_document_chunks` : passages indexés, index full-text MySQL (conditionnel, ignoré sur SQLite)

Aucune table métier existante n'est modifiée ni supprimée.

## 3. Choix de Gemini Flash

- **Fournisseur :** Google Gemini Flash via l'API officielle (`generativelanguage.googleapis.com`)
- **Modèle :** `gemini-1.5-flash-latest` (configurable via `GEMINI_MODEL`)
- **Documentation :** https://ai.google.dev/gemini-api/docs
- **Function Calling :** https://ai.google.dev/gemini-api/docs/function-calling
- **Clé API :** à obtenir sur https://aistudio.google.com/

Le client utilise `Illuminate\Support\Facades\Http` (déjà présent dans le projet) — aucune dépendance PHP supplémentaire n'est ajoutée.

## 4. Configuration de la clé API

### Variable d'environnement

Ajouter dans le fichier `.env` local (à créer depuis `.env.example`) :

```dotenv
GEMINI_API_KEY=
```

**Où ajouter la clé :** dans votre fichier `.env` local, à la ligne `GEMINI_API_KEY=`, après avoir créé votre clé sur https://aistudio.google.com/. Ne jamais committer cette valeur.

### Autres variables

| Variable | Défaut | Description |
|----------|--------|-------------|
| `CHATBOT_ENABLED` | `true` | Active/désactive le chatbot |
| `CHATBOT_LLM_PROVIDER` | `gemini` | Fournisseur LLM |
| `GEMINI_API_KEY` | *(vide)* | Clé API Gemini (à renseigner) |
| `GEMINI_MODEL` | `gemini-1.5-flash-latest` | Modèle Gemini Flash |
| `GEMINI_TIMEOUT` | `30` | Timeout en secondes |
| `CHATBOT_MAX_MESSAGE_LENGTH` | `2000` | Taille max du message |
| `CHATBOT_MAX_TOOL_CALLS` | `3` | Nombre max d'appels d'outils |
| `CHATBOT_RATE_LIMIT` | `10` | Requêtes max par minute par utilisateur |
| `CHATBOT_RAG_ENABLED` | `true` | Active la recherche documentaire |
| `CHATBOT_MAX_RAG_CHUNKS` | `5` | Nombre max de passages RAG |

## 5. Procédure de lancement

1. Copier `.env.example` vers `.env` (si ce n'est pas déjà fait).
2. Ajouter votre clé API Gemini : `GEMINI_API_KEY=votre_cle` dans `.env`.
3. Exécuter les migrations : `php artisan migrate`.
4. Indexer les documents : `php artisan chatbot:ingest-documents`.
5. Lancer le serveur : `php artisan serve`.
6. Ouvrir l'application : le widget de chat est disponible sur toutes les pages (via `<x-ai-assistant />` dans `layouts/app.blade.php`).

## 6. Procédure d'indexation documentaire

```bash
php artisan chatbot:ingest-documents
```

Cette commande :
- Lit uniquement les fichiers `.md` de `docs/chatbot/`.
- Exclut tout fichier contenant `.env`, `composer.lock`, etc. (sécurité).
- Découpe chaque document en passages de ~800 caractères avec chevauchement de 150.
- Indexe les passages dans `chatbot_document_chunks` avec titre, section et index.
- Purge les anciens passages d'un document avant réindexation (pas de duplication).
- Signale les fichiers ignorés et les erreurs.

## 7. Fonctionnement du RAG

- **Sources :** fichiers Markdown sous `docs/chatbot/` (guide-projets, regles-financement, demandes-financement, paiements-simules, guide-citoyen, guide-manager, faq).
- **Stratégie :** recherche textuelle full-text MySQL, avec fallback LIKE sur SQLite. **Ce n'est PAS une recherche vectorielle.**
- **Pas d'embeddings, pas de base vectorielle.**
- Les documents indexés sont uniquement de la documentation publique. Aucune donnée privée de citoyen n'est indexée.
- Les sources retournées sont réelles (titre de document, section, contenu). Aucune citation n'est inventée.

## 8. Fonctionnement du Tool Calling

Le LLM peut proposer des appels d'outils, mais Laravel décide de leur exécution :

1. L'outil doit exister dans `ToolRegistry`.
2. L'outil doit être accessible au rôle de l'utilisateur (citoyen ou manager).
3. Les arguments sont validés strictement.
4. L'exécuteur Laravel exécute l'outil et retourne des données limitées.
5. Un maximum de 3 appels d'outils par question.
6. Aucun outil d'écriture n'existe.

## 9. Outils métier disponibles

| Outil | Rôles autorisés | Description |
|-------|----------------|-------------|
| `search_projects` | Citoyen, Manager | Recherche de projets avec filtres |
| `get_project_details` | Citoyen, Manager | Détails d'un projet |
| `get_project_funding_status` | Citoyen, Manager | Budget, financé, réservé, disponible |
| `get_funding_rules` | Citoyen, Manager | Règles métier (montant minimum, etc.) |
| `search_documentation` | Citoyen, Manager | Recherche documentaire RAG |
| `get_project_recommendations` | Citoyen | Recommandations V1 (règles uniquement) |
| `get_my_funding_requests` | Citoyen | Mes propres demandes |
| `get_my_payments` | Citoyen | Mes propres paiements (simulés) |
| `get_my_funding_portfolio` | Citoyen | Mon portefeuille |
| `get_my_project_contributions` | Citoyen | Mes contributions avec projets |
| `get_funding_dashboard_summary` | Manager | Statistiques globales |
| `get_pending_funding_requests` | Manager | Demandes en attente |
| `get_manager_project_budget_report` | Manager | Rapport budgétaire par projet |

## 10. Matrice RBAC

| Outil | Citoyen | Manager |
|-------|---------|---------|
| `search_projects` | Oui | Oui |
| `get_project_details` | Oui | Oui |
| `get_project_funding_status` | Oui (données autorisées) | Oui |
| `get_funding_rules` | Oui | Oui |
| `search_documentation` | Oui | Oui |
| `get_project_recommendations` | Oui (propre profil) | Non |
| `get_my_funding_requests` | Oui (propres uniquement) | Non |
| `get_my_payments` | Oui (propres uniquement) | Non |
| `get_my_funding_portfolio` | Oui (propre uniquement) | Non |
| `get_my_project_contributions` | Oui (propres uniquement) | Non |
| `get_funding_dashboard_summary` | **Interdit** | Oui |
| `get_pending_funding_requests` | **Interdit** | Oui |
| `get_manager_project_budget_report` | **Interdit** | Oui |

## 11. Règles de confidentialité

- La clé API Gemini est lue depuis `.env` via `config()`, jamais codée en dur, jamais exposée au frontend.
- Les données personnelles des citoyens ne sont jamais envoyées au modèle sans nécessité.
- Le RAG partagé ne contient que de la documentation publique.
- Les outils personnels utilisent l'utilisateur authentifié (jamais d'identifiant depuis le payload).
- Les erreurs ne révèlent jamais de secrets, de traces de pile ni de clés API.
- Les paiements sont simulés : aucun argent réel, aucune donnée bancaire.

## 12. Gestion des erreurs et quotas

- Absence de clé API → message d'indisponibilité clair, l'application continue de fonctionner.
- Erreur HTTP Gemini → message générique, aucune fuite de détails.
- Quota dépassé (429) → message de surcharge temporaire.
- Réponse invalide → message d'indisponibilité.
- Outil inconnu ou non autorisé → refus explicite, pas d'exécution.
- Limite de 3 appels d'outils par question pour empêcher les boucles infinies.

## 13. Dépendances ajoutées

Aucune dépendance PHP ou JS supplémentaire n'a été ajoutée. Le projet utilise :
- `Illuminate\Support\Facades.Http` (déjà présent) pour appeler Gemini.
- `axios` (déjà présent dans `package.json`) pour les appels AJAX depuis le frontend.

## 14. Tests exécutés et résultats

| Test | Résultat |
|------|----------|
| `ChatbotRagTest` (7 tests) | ✅ Tous passés |
| `ChatbotRbacTest` (6 tests) | ✅ Tous passés |
| `ChatbotToolCallingTest` (9 tests) | ✅ Tous passés |
| `ChatbotGeminiClientTest` (5 tests) | ✅ Tous passés |
| Tests existants liés au financement | ✅ 109 passés, 1 échec préexistant (route manquante) |

Aucun appel externe n'est effectué pendant les tests automatisés (Gemini est mocké par l'absence de clé, et les outils retournent des données de test).

## 15. Limites connues

- **Recherche textuelle, pas vectorielle** : le RAG utilise une recherche LIKE/full-text, pas d'embeddings.
- **Gemini requis pour les réponses générées** : sans clé API, le chatbot affiche un message d'indisponibilité mais l'application fonctionne.
- **Frontend en JS vanilla** : pas de framework JS moderne (React/Vue), le widget utilise du JavaScript pur.
- **Pas d'historique de conversation persistant** : chaque requête est traitée indépendamment (décision documentée pour éviter l'ajout de tables inutiles).
- **Pas d'outil d'écriture** : le chatbot ne peut effectuer aucune modification de données.

## 16. Procédure de démonstration devant le jury

### Scénario citoyen
1. Se connecter en tant que citoyen (`citoyen@aquasecure.tn` / `password123`).
2. Ouvrir le widget de chat (bouton flottant en bas à droite).
3. Demander : « Quels projets sont ouverts au financement ? » → le chatbot utilise `search_projects`.
4. Demander : « Quel est le montant minimum de contribution ? » → le chatbot utilise `get_funding_rules`.
5. Demander : « Consulte mes contributions. » → le chatbot utilise `get_my_funding_portfolio`.
6. Demander : « Quel est le statut de ma demande ? » → le chatbot utilise `get_my_funding_requests`.
7. Demander : « Montre-moi le tableau de bord du manager. » → le chatbot refuser (RBAC).

### Scénario manager
1. Se connecter en tant que manager (`gestionnaire@aquasecure.tn` / `password123`).
2. Demander : « Quelles demandes sont en attente ? » → le chatbot utilise `get_pending_funding_requests`.
3. Demander : « Consulte les budgets des projets. » → le chatbot utilise `get_manager_project_budget_report`.
4. Demander : « Explique les règles de décision. » → le chatbot utilise `get_funding_rules` et/ou RAG.
5. Vérifier que le chatbot ne peut pas approuver ou rejeter une demande (pas d'outil d'écriture).

### Scénario RAG
1. Poser : « Comment fonctionne le paiement simulé ? » → le chatbot trouve une source dans `paiements-simules.md`.
2. Vérifier que la source affichée correspond à un document réel.
3. Modifier un document sous `docs/chatbot/` (ex: ajouter une FAQ).
4. Exécuter `php artisan chatbot:ingest-documents`.
5. Reposer la question → le chatbot utilise le contenu actualisé.

### Scénario d'indisponibilité
1. Tester sans clé API (`GEMINI_API_KEY=` vide dans `.env`).
2. Vérifier que le chatbot affiche un message clair d'indisponibilité.
3. Vérifier que l'application continue de fonctionner normalement.
4. Exécuter les tests automatisés → tous passent sans appel externe.