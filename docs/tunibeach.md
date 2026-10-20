# TuniBeach — catalogue public des plages

TuniBeach expose un catalogue public en lecture seule sous `/api/v1`. Le dépôt ne contenait ni entité plage, ni liste de plages vérifiées, ni spécification d'un fournisseur externe TuniBeach. Le module crée donc un espace de données séparé. Il ne transforme pas les points de mesure d'AquaSecure en plages et n'ajoute aucune donnée fictive. Le catalogue restera vide jusqu'à l'ajout de données vérifiées par un processus de confiance.

## Migration

La migration additive `2026_10_19_000001_create_tunibeach_public_data_tables.php` crée quatre tables isolées :

- `tunibeach_governorates` et `tunibeach_beaches` pour les notices publiées ;
- `tunibeach_measurements` pour les prélèvements publics, leur source et les paramètres accompagnés de leurs unités ;
- `tunibeach_bathing_statuses` pour les décisions de baignade documentées, leur source et leur période de validité.

La migration additive crée uniquement les tables TuniBeach; elle ne modifie pas les tables AquaSecure existantes.

Les notices restent privées par défaut (`is_published=false`, `is_public=false`, `is_verified=false`). Les statuts autorisés sont `safe`, `restricted` et `prohibited`. Le statut API vaut `unknown` lorsqu'aucune décision n'est vérifiée, sourcée et valide à l'instant de la requête. Une décision doit avoir `verified_at`, `valid_from` et `valid_until`; une décision expirée ne sert jamais à afficher `safe`.

Un gouvernorat doit être à la fois vérifié et publié pour que ses plages apparaissent dans l'API.

Les coordonnées ne donnent un marqueur cartographique que si `coordinates_verified=true` et que les deux coordonnées sont présentes. Les valeurs de prélèvement sont conservées séparément des mesures `MesureQualite`; les paramètres sont un objet limité à `ph`, `turbidity`, `residual_chlorine`, `lead` et `nitrates`, avec une forme `{ "value": 7.2, "unit": "pH" }`.

## API

Les endpoints sont publics et limités à 60 requêtes par minute. Les réponses paginées comprennent `data` et `meta`. `per_page` est limité à 100.

| Méthode et URL | Usage |
| --- | --- |
| `GET /api/v1/governorates` | Gouvernorats publiés (`page`, `per_page`) |
| `GET /api/v1/beaches` | Plages publiées (`q`, `governorate` slug, `status`=`safe|restricted|prohibited|unknown`, `page`, `per_page`) |
| `GET /api/v1/beaches/{slug}` | Fiche publiée et statut de baignade justifié |
| `GET /api/v1/beaches/{slug}/measurements` | Historique public (`page`, `per_page`) |

Une plage absente ou non publiée renvoie `404`. Les filtres et pages invalides renvoient `422`. Les réponses ne contiennent aucun modèle brut ou champ interne.

Le filtre `status` n'utilise que les décisions vérifiées, sourcées et actuellement valides. `unknown` regroupe les plages sans décision répondant à ces critères; il ne signifie pas que la baignade est sûre.

## Cache et invalidation

La durée se règle avec `TUNIBEACH_CACHE_TTL` (300 secondes par défaut). Les clés sont dédiées à TuniBeach et incluent la version, les filtres et la page. Les modifications Eloquent aux quatre modèles incrémentent la génération du cache TuniBeach; le cache des autres modules n'est pas touché. Aucune réponse `5xx` n'est mise en cache.

## Carte citoyenne

La page `GET /citizen/beaches` est protégée par le rôle citoyen et réutilise le layout front-office. Elle lit l'API publique, montre les plages publiées, permet la recherche et le filtre par gouvernorat et n'ajoute un marqueur que pour les coordonnées vérifiées. Le statut, sa source et sa période sont affichés; l'absence de données est explicitement signalée. Leaflet est déjà utilisé par le projet et chargé depuis son CDN, sans nouvelle dépendance npm.

## Gestionnaire : mise à jour des plages et alertes

Dans l'espace Manager, `GET /manager/beaches` appelle le même `TuniBeachService::beaches` que l'API lue par l'espace Citoyen. Il affiche donc le même catalogue public filtré (plages publiées avec gouvernorat vérifié et publié), avec les mêmes statuts, coordonnées vérifiées et dernières mesures publiques vérifiées; les sessions restent indépendantes par rôle. La liste accepte aussi les mêmes filtres par nom, gouvernorat et statut. La fiche `GET /manager/beaches/{beach}/edit` permet de corriger le nom, le gouvernorat, la description, les coordonnées et la publication d'une plage existante. Les coordonnées ne sont marquées vérifiées que si les deux valeurs sont présentes. Toute sauvegarde invalide la génération du cache TuniBeach afin que les prochaines lectures utilisent les valeurs enregistrées.

Depuis cette fiche, le gestionnaire peut ajouter une décision de baignade (`safe`, `restricted` ou `prohibited`) avec sa source et sa période de validité. Les décisions sont ajoutées à l'historique et ne remplacent pas les anciennes. La fiche montre aussi, séparément, les points historiques signalés dans le gouvernorat au 20 juin 2026; ils ne sont pas associés automatiquement à la plage sélectionnée. Une décision n'est prise en compte par l'API que si elle est vérifiée et encore valide; une alerte sans validation reste donc visible dans l'historique de gestion mais ne devient pas un statut public confirmé. Ces écrans sont protégés par le middleware Manager (manager et administrateur).

## Source de données et mise en service

Le catalogue de noms et gouvernorats provient du classeur APAL officiel « Liste des plages en Tunisie », référencé dans [database/data/tunibeach/README.md](../database/data/tunibeach/README.md). Le seeder dédié importe uniquement ces noms. Les coordonnées ne sont pas déduites des colonnes Long./Larg. qui décrivent des dimensions; elles restent absentes et non vérifiées. Les plages du catalogue sont donc identifiées, mais ne sont pas géolocalisées ni évaluées sanitairement par cette importation.

Le résumé relatif à la surveillance publié le 20 juin 2026 mentionne 49 points où la baignade était déconseillée jusqu'à amélioration des indicateurs et nouvelle évaluation. Le total et la répartition sont rapportés par [Radio Nationale](https://www.radionationale.tn/article/6a36593808d2abfa30768ba3/%D8%B9%D8%AF%D8%AF%D9%87%D8%A7-49-%D9%86%D9%82%D8%B7%D8%A9-%D8%A7%D9%84%D8%A5%D8%B9%D9%84%D8%A7%D9%86-%D8%B9%D9%86-%D9%82%D8%A7%D8%A6%D9%85%D8%A9-%D8%A7%D9%84%D8%B4%D9%88%D8%A7%D8%B7%D8%A6-%D8%BA%D9%8A%D8%B1-%D8%A7%D9%84%D8%B5%D8%A7%D9%84%D8%AD%D8%A9-%D9%84%D9%84%D8%B3%D8%A8%D8%A7%D8%AD%D8%A9) et [TAP](https://www.tap.info.tn/fr/Portail-Titres-de-l-actualit%C3%A9/20310648-interdiction-de). Les localisations détaillées du CSV sont transcrites du relevé fourni pour le projet; ce n'est pas l'export nominatif original du ministère. Ce sont des informations datées, pas une décision actuelle vérifiée pour chaque plage du catalogue. Les noms des points de surveillance ne sont donc pas assimilés automatiquement aux plages APAL.

Les 48 localisations nommées sont intégrées séparément comme historique daté avec `restricted`; le point annoncé manquant de Bizerte est conservé comme entrée `unknown`. L'import remplace les lignes de cette date dans `tunibeach_historical_advisory_points`, par `php artisan db:seed --class=Database\\Seeders\\TuniBeachHistoricalAdvisorySeeder`. La fiche citoyenne affiche les signalements du gouvernorat, leurs états historiques, la date et la source, et précise qu'ils ne confirment pas le statut actuel ni l'identité du point pour la plage sélectionnée. Ils ne sont pas créés comme décisions de baignade actuelles. Les autres plages ne sont pas déclarées sûres par défaut. Les statuts restent `unknown` jusqu'à ce qu'une source sanitaire suffisamment précise et à jour confirme la plage concernée, le résultat ou la décision, sa date et sa période de validité. `unknown` signifie que l'application ne dispose pas d'une décision vérifiée et valide; il ne signifie ni « autorisé » ni « interdit ».

Le relevé historique du 20 juin 2026 contient 48 localisations nommées classées `restricted` et un douzième point annoncé à Bizerte dont le nom reste non identifié (`unknown`). Ces états sont exposés point par point, restent explicitement historiques et n'alimentent pas `bathing_status`; le statut sanitaire actuel reste `unknown` tant qu'une décision vérifiée et valide n'est pas enregistrée. Les points ne sont pas automatiquement associés aux fiches APAL portant des noms similaires. Le seeder historique remplace les lignes du relevé de cette date par le contenu du CSV afin que la base reflète la version validée.

Pour alimenter le catalogue, après revue de la source, exécuter uniquement le seeder dédié : `php artisan db:seed --class=Database\\Seeders\\TuniBeachApalSeeder`. Il est idempotent et n'efface pas de données.

Pour l'essayer après migration :

```powershell
Invoke-RestMethod http://localhost:8000/api/v1/governorates
Invoke-RestMethod 'http://localhost:8000/api/v1/beaches?per_page=20'
```

La page citoyenne nécessite une session ouverte avec le rôle citoyen : <http://localhost:8000/citizen/beaches>.
