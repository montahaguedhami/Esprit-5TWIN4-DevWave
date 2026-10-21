# Qualité — analyse SonarQube (CI)

## État constaté

Le job **« Analyse SonarQube + Quality Gate »** échoue sur `main` :

```
ERROR Failed to query server version: Call to URL [***/api/v2/analysis/version] failed: null
INFO  EXECUTION FAILURE
```

Le scanner **ne parvient pas à joindre le serveur SonarQube**. Ce n'est **pas** un rejet
du Quality Gate sur le code du projet.

Remarques :

- L'échec est **préexistant** : il survient déjà sur les merges antérieurs (ex. `c3f9f1e`,
  PR #17 « amelioration_qualite_v2 »), avant toute intégration du module Projets & Financements.
- Les runs encore plus anciens apparaissent en `skipped` : le job ne s'exécute que lorsque la
  CI principale (*AquaSecure CI*) est **verte** (`if: ... workflow_run.conclusion == 'success'`).
  Il s'exécute donc réellement depuis peu et n'avait jamais été validé auparavant.

## Diagnostic (vérifié le 21/10/2026)

| Élément | État vérifié |
| --- | --- |
| Secret `SONAR_TOKEN` | présent (dépôt) |
| Secret `SONAR_HOST_URL` | présent (dépôt) |
| Variable `SONAR_RUNNER` | **absente** |
| `runs-on` effectif | `ubuntu-latest` (runner GitHub-hosted) |
| Serveur SonarQube du projet | **local** : `docker compose --profile qualite up -d` → `localhost:9000` |
| Cible du `SONAR_HOST_URL` | injoignable depuis un runner GitHub-hosted (localhost / réseau privé) |

**Cause racine** : un runner GitHub-hosted ne peut pas atteindre le `localhost:9000` de la
machine du développeur où tourne le conteneur SonarQube. L'échec est donc structurel tant
que l'une des options ci-dessous n'est pas choisie.

Le workflow (`.github/workflows/sonarqube.yml`) prévoit d'ailleurs explicitement cette dualité :

```yaml
runs-on: ${{ vars.SONAR_RUNNER || 'ubuntu-latest' }}
# - absente       → ubuntu-latest (SonarCloud ou serveur accessible depuis Internet)
# - "self-hosted" → runner auto-hébergé (serveur local, ex. docker compose --profile qualite)
```

## Correctifs possibles (au choix de l'équipe)

### Option A — runner auto-hébergé (conforme à l'architecture Docker actuelle)

1. Sur la machine qui exécute SonarQube :
   ```bash
   docker compose --profile qualite up -d
   ```
2. `Settings → Actions → Runners → New self-hosted runner`, puis installer et démarrer le
   runner sur **cette même machine** (sinon `localhost:9000` reste injoignable).
3. Définir la **variable** de dépôt `SONAR_RUNNER=self-hosted`
   (`Settings → Secrets and variables → Actions → Variables` — onglet *Variables*, pas *Secrets*).
4. Relancer le workflow (`Actions → SonarQube → Run workflow`).

Contrainte : le runner et le conteneur SonarQube doivent être actifs à chaque analyse,
sans quoi le job reste en attente (`pending`).

### Option B — serveur accessible depuis GitHub

- Exposer SonarQube au travers d'un reverse proxy (HTTPS) **ou** migrer vers SonarCloud ;
- Mettre à jour `SONAR_HOST_URL` et `SONAR_TOKEN` en conséquence ;
- Laisser `SONAR_RUNNER` absente (le job repart automatiquement sur `ubuntu-latest`).

## Vérification après correctif

Après correction, le job doit aboutir sans `EXECUTION FAILURE` et afficher un
**Quality Gate vert** (`-Dsonar.qualitygate.wait=true` est déjà positionné).

## Règle à respecter

Ne jamais commenter, désactiver ou contourner ce workflow pour le faire passer au vert :
la CI doit rester intacte. La bonne pratique consiste à **corriger l'infrastructure**,
jamais à affaibrir la vérification.
