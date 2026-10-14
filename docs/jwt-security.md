# Sécurité JWT — AquaSecure API

## Choix retenu

**Bibliothèque :** `tymon/jwt-auth ^2.3`  
**Algorithme :** HS256  
**Transport :** Header `Authorization: Bearer <token>` ou cookie HttpOnly `access_token` (opt-in)

### Justification HS256 vs RS256

AquaSecure est déployé sur un serveur unique. RS256 (paire de clés asymétriques) est pertinent
dans un environnement multi-services où plusieurs services doivent vérifier des tokens sans
partager un secret commun. Dans ce contexte mono-serveur, HS256 avec une clé forte (256 bits
générée par `php artisan jwt:secret`) offre une sécurité équivalente avec une gestion simplifiée :
aucune infrastructure de clés publiques/privées, aucun risque de fuite de clé privée via un
service secondaire.

---

## Architecture

La couche web (Blade, `routes/web.php`, sessions Laravel) **n'est pas touchée**.  
JWT s'applique exclusivement aux routes `routes/api.php` (préfixe `/api/*`).

```
POST   /api/auth/login     → public, émet access_token + refresh_token
POST   /api/auth/refresh   → public, rotate refresh_token
POST   /api/auth/logout    → protégé jwt.auth
GET    /api/auth/me        → protégé jwt.auth
GET    /api/mesures        → protégé jwt.auth
...    /api/mesures/*      → protégé jwt.auth + jwt.role selon l'opération
GET    /api/user/permissions → protégé jwt.auth
```

---

## Durée de vie des tokens

| Token        | Durée      | Stockage serveur                          |
|--------------|------------|-------------------------------------------|
| Access token | 60 minutes | Blacklist JWT (cache Laravel)             |
| Refresh token| 7 jours    | Table `jwt_refresh_tokens` (hash sha256)  |

Les valeurs sont configurables via les variables d'environnement `JWT_TTL` et `JWT_REFRESH_TTL`.

---

## Claims validés à chaque requête

| Claim | Source   | Validation                                      |
|-------|----------|-------------------------------------------------|
| `iss` | APP_URL  | Vérification de l'émetteur                      |
| `aud` | aquasecure-api | Vérification de l'audience               |
| `exp` | iat + TTL | Token rejeté automatiquement à expiration      |
| `nbf` | iat       | Token invalide avant cette date                 |
| `iat` | émission  | Horodatage d'émission                           |
| `jti` | UUID      | Identifiant unique — utilisé pour le blacklist  |
| `sub` | user.id   | Identifiant du sujet                            |

### Claim `rol` — informatif uniquement

Le claim `rol` est inclus dans le payload à titre informatif pour l'UI cliente (affichage du
menu selon le rôle sans appel réseau supplémentaire). **Il n'est jamais utilisé pour les
décisions d'autorisation côté serveur.** Le rôle réel est systématiquement relu depuis la base
de données dans les middlewares `JwtAuthenticate` et `JwtCheckRole`.

---

## Processus de validation (middleware `JwtAuthenticate`)

À chaque requête protégée, le middleware effectue dans l'ordre :

1. Extraction du token depuis `Authorization: Bearer` en priorité, puis cookie `access_token`.
2. Vérification de la **signature** HS256 via `tymon/jwt-auth`.
3. Vérification de l'**expiration** (`exp`) et de `nbf`.
4. Vérification que le token **n'est pas blacklisté** (révocation après logout).
5. **Rechargement de l'utilisateur depuis la DB** par `sub` — jamais de confiance aveugle au payload.
6. Vérification de `is_active` en base — un compte désactivé est rejeté même avec un token valide.

---

## Stockage côté client

| Mode          | Activation                          | Sécurité                                    |
|---------------|-------------------------------------|---------------------------------------------|
| Header Bearer | Par défaut                          | Le client gère le stockage (SPA) — ne pas utiliser `localStorage` |
| Cookie HttpOnly | Header `X-Token-Delivery: cookie` | **Recommandé** — inaccessible depuis JavaScript |

### Configuration du cookie

Lorsque le client envoie `X-Token-Delivery: cookie` :

- `access_token` : HttpOnly, Secure (production), SameSite=Lax, Path=/, durée 60 min
- `refresh_token` : HttpOnly, Secure (production), SameSite=Lax, Path=/api/auth/refresh, durée 7 jours

**`HttpOnly: true`** — le cookie ne peut pas être lu par JavaScript, protégeant contre le vol
de token via XSS.  
**`Secure: true`** en production — transmission HTTPS uniquement.  
**`SameSite: Lax`** — protection CSRF de base : le cookie n'est pas envoyé dans les requêtes
cross-site initiées par un tiers.

### Protection CSRF complémentaire

Lorsque les cookies sont utilisés avec des requêtes mutantes (POST, PUT, DELETE), inclure
l'en-tête `X-CSRF-TOKEN` (fourni par Laravel via la session ou `@csrf` dans les vues Blade).

---

## Révocation et rotation

### Access tokens

Lors du logout (`POST /api/auth/logout`), le token actif est ajouté à la **blacklist JWT**
gérée par tymon/jwt-auth. La blacklist est stockée dans le driver de cache configuré
(`CACHE_STORE` dans `.env`).

### Refresh tokens (table `jwt_refresh_tokens`)

- Chaque refresh token est stocké sous forme de `sha256(token_brut)` dans la colonne `jti`.
  Le token brut n'est jamais persisté.
- **Usage unique avec rotation** : chaque appel à `POST /api/auth/refresh` révoque l'ancien
  refresh token et émet un nouveau. Si un token révoqué est présenté, la requête est rejetée
  avec `401 invalid_refresh_token`.
- Lors du logout, tous les refresh tokens actifs de l'utilisateur sont révoqués avec
  horodatage dans `revoked_at`.

---

## Secrets et variables d'environnement

| Variable              | Description                                    |
|-----------------------|------------------------------------------------|
| `JWT_SECRET`          | Clé HMAC-SHA256, ≥256 bits. Ne jamais committer. |
| `JWT_TTL`             | Durée de vie access token (minutes, défaut 60) |
| `JWT_REFRESH_TTL`     | TTL blacklist refresh (minutes, défaut 10080)  |
| `JWT_ALGO`            | Algorithme (défaut HS256)                      |

**Génération de la clé :**
```bash
php artisan jwt:secret
```

**Rotation de la clé :** régénérer `JWT_SECRET` invalide immédiatement tous les access tokens
en cours. Prévoir une communication aux utilisateurs et une procédure de re-login.

---

## Ce qui N'EST PAS stocké dans le payload

- Mot de passe ou hash de mot de passe
- Permissions granulaires (trop volatiles — elles changent en temps réel en base)
- Données personnelles sensibles (numéro de téléphone, adresse, etc.)

---

## Implications de l'architecture

| Aspect                        | Implication                                                              |
|-------------------------------|--------------------------------------------------------------------------|
| Rotation de `JWT_SECRET`      | Invalide tous les access tokens actifs — re-login requis                 |
| Flush du cache                | La blacklist est perdue — les tokens révoqués redeviennent valides jusqu'à `exp` |
| Désactivation d'un compte     | Prise en compte immédiate dès la prochaine requête (re-validation `is_active`) |
| Changement de rôle            | Pris en compte immédiatement (les rôles sont relus depuis la DB)         |

---

## Commandes de déploiement

```bash
# 1. Installer les dépendances
composer install --no-dev --optimize-autoloader

# 2. Copier et configurer les variables d'environnement
cp .env.example .env
# Éditer .env : DB_*, APP_URL, JWT_SECRET, etc.

# 3. Générer APP_KEY et JWT_SECRET
php artisan key:generate
php artisan jwt:secret

# 4. Exécuter les migrations (inclut jwt_refresh_tokens)
php artisan migrate --force

# 5. Vider et reconstruire le cache de configuration
php artisan config:cache
php artisan route:cache
```

---

## Recommandations futures

- **Rate limiting** sur `POST /api/auth/login` : utiliser `RateLimiter` de Laravel pour limiter
  les tentatives de connexion (ex. 5 tentatives par IP sur 60 secondes).
- **Rotation automatique** des access tokens proches de l'expiration : émettre un nouveau token
  si `exp - now() < 5 min` et retourner le nouveau token dans un header `X-New-Access-Token`.
- **Audit des refresh tokens révoqués** : alerter si un token révoqué est présenté à nouveau
  (détection de vol de token).
- **RS256** si l'architecture évolue vers des micro-services nécessitant la vérification de
  tokens sans accès au secret partagé.
- **Monitoring** du cache blacklist : surveiller la taille de la blacklist et mettre en place
  une purge automatique des entrées expirées.
