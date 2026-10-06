# 🧪 GUIDE DE TEST - MODULE PROJET/FINANCEMENT
# AquaSecure - Tests Manuels

**Date :** 6 octobre 2026  
**Objectif :** Vérifier que toutes les fonctionnalités sont correctement implémentées

---

## 🚀 DÉMARRAGE

### 1️⃣ **Lancer le serveur de développement**

```bash
php artisan serve
```

Le serveur démarre sur : **http://127.0.0.1:8000**

---

## 🔐 AUTHENTIFICATION

### **Se connecter en tant que Manager**

**URL :** http://127.0.0.1:8000/login

**Identifiants Manager :**
- Email : `manager@aquasecure.tn`
- Mot de passe : `password`

### **Se connecter en tant que Citizen**

**URL :** http://127.0.0.1:8000/login

**Identifiants Citizen :**
- Email : `citizen@aquasecure.tn`
- Mot de passe : `password`

---

## 📋 TESTS MANAGER - PROJETS (10 URLs)

### ✅ **1. Liste des projets**
**URL :** http://127.0.0.1:8000/manager/projets

**À vérifier :**
- [x] Liste de 19 projets affichés en grille
- [x] 4 statistiques KPIs en haut (Total projets, En cours, Terminés, Budget total)
- [x] Filtres fonctionnels :
  - Barre de recherche (nom/description)
  - Filtre par statut (dropdown)
- [x] Chaque carte projet affiche :
  - Badge statut coloré
  - Nom du projet
  - Adresse (si géolocalisé)
  - Icône localisation si GPS présent
  - Barre de progression financement
  - Budget / Montant financé
  - Dates début/fin
  - Nombre de financements
- [x] Bouton "Créer un projet" visible
- [x] Bouton "Carte des projets" visible
- [x] Pagination fonctionnelle (15 items/page)
- [x] Design glassmorphism cyan/blue

**Tests interactifs :**
1. Rechercher "Tunis" → Doit filtrer les projets
2. Sélectionner statut "en_cours" → Doit afficher uniquement projets en cours
3. Cliquer sur "Réinitialiser" → Retour à la liste complète

---

### ✅ **2. Carte globale des projets**
**URL :** http://127.0.0.1:8000/manager/projets/map

**À vérifier :**
- [x] Carte Leaflet affichée (hauteur 600px)
- [x] Markers colorés selon statut :
  - 🟢 Vert : Terminé
  - 🔵 Bleu : En cours
  - 🟠 Orange : Planifié
  - 🟡 Jaune : Suspendu
  - 🔴 Rouge : Annulé
- [x] Légende des statuts affichée
- [x] 4 statistiques KPIs projets géolocalisés
- [x] Clic sur marker → Popup avec :
  - Nom du projet
  - Statut
  - Budget
  - Lien "Voir les détails"
- [x] Zoom automatique sur tous les markers
- [x] OpenStreetMap tiles chargées
- [x] Attribution OSM visible

**Tests interactifs :**
1. Cliquer sur plusieurs markers → Popups s'ouvrent
2. Cliquer "Voir les détails" dans popup → Redirection vers show
3. Zoomer/dézoomer → Carte réactive
4. Si aucun projet géolocalisé : Message "Aucun projet géolocalisé"

---

### ✅ **3. Créer un projet**
**URL :** http://127.0.0.1:8000/manager/projets/create

**À vérifier :**
- [x] Formulaire avec tous les champs :
  - Nom du projet (required)
  - Description (textarea, optionnel)
  - Date de début (required)
  - Date de fin (optionnel)
  - Budget (required, number)
  - Statut (select avec 5 options)
  - Adresse (optionnel)
  - Latitude (readonly, optionnel)
  - Longitude (readonly, optionnel)
- [x] Carte Leaflet interactive (400px) :
  - Centrée sur Tunisie (lat: 34.0, lng: 9.0, zoom: 7)
  - Instructions "Cliquez sur la carte pour placer un marker"
  - Marker apparaît au clic
  - Marker draggable (déplaçable)
  - Latitude/Longitude mises à jour automatiquement
- [x] Validation HTML5 (required, min, max)
- [x] Boutons "Annuler" et "Créer le projet"

**Tests interactifs :**
1. Remplir tous les champs obligatoires
2. Cliquer sur la carte (ex: Tunis) → Marker apparaît + lat/lng remplies
3. Déplacer le marker → Coordonnées mises à jour
4. Modifier manuellement latitude → Marker se déplace sur carte
5. Soumettre formulaire → Redirection vers liste avec message succès
6. Essayer de soumettre sans nom → Erreur de validation affichée
7. Essayer date_fin < date_debut → Erreur "doit être après date début"

**Projet test à créer :**
```
Nom : Projet Test Manuel
Description : Test de création depuis l'interface
Date début : 2026-10-01
Date fin : 2027-03-31
Budget : 150000
Statut : en_cours
Adresse : Tunis, Tunisie
Cliquer sur carte à Tunis (lat ~36.8, lng ~10.2)
```

---

### ✅ **4. Voir détails d'un projet**
**URL :** http://127.0.0.1:8000/manager/projets/1
*(Remplacer 1 par l'ID d'un projet existant)*

**À vérifier :**
- [x] Breadcrumb : Manager > Projets > [Nom du projet]
- [x] En-tête avec :
  - Icône projet
  - Nom du projet
  - Badge statut
  - Badge "Géolocalisé" (si coordonnées présentes)
  - Bouton "Modifier le projet"
  - Bouton "Supprimer" (rouge)
- [x] Section "À propos du projet" avec description
- [x] Section "Résumé financier" avec 4 cards :
  - Budget initial
  - Montant financé (calculé automatiquement)
  - Montant restant (calculé)
  - Pourcentage financé (calculé)
- [x] Barre de progression visuelle
- [x] Alerte si dépassement de budget (texte rouge)
- [x] Section "Sources de financement" :
  - Liste des financements avec source, montant, date
  - Liens cliquables vers chaque financement
  - Bouton "Ajouter un financement"
  - Message "Aucun financement" si vide
- [x] Sidebar droite :
  - Section Calendrier (dates + durée calculée)
  - Section Localisation (carte Leaflet lecture seule)
  - Adresse affichée
  - Coordonnées GPS affichées
  - Message si pas géolocalisé
- [x] Confirmation avant suppression

**Tests interactifs :**
1. Vérifier calculs automatiques (financé, restant, %)
2. Vérifier que la carte affiche le bon marker
3. Cliquer "Modifier" → Redirection vers edit
4. Cliquer sur un financement → Redirection vers financement/show
5. Cliquer "Ajouter un financement" → Redirection create avec projet pré-sélectionné
6. Cliquer "Supprimer" → Confirmation → Suppression + redirection

**Projets spéciaux à tester :**
- Projet ID 16 : Sans financement
- Projet ID 17 : Totalement financé (100%)
- Projet ID 18 : Dépassement budget (>100%)
- Projet ID 19 : Sans géolocalisation

---

### ✅ **5. Modifier un projet**
**URL :** http://127.0.0.1:8000/manager/projets/1/edit
*(Remplacer 1 par l'ID d'un projet existant)*

**À vérifier :**
- [x] Formulaire identique à create
- [x] Tous les champs pré-remplis avec valeurs existantes
- [x] Carte pré-centrée sur position existante (si géolocalisé)
- [x] Marker existant visible et draggable
- [x] @method('PUT') dans le formulaire
- [x] Validation identique à create
- [x] Boutons "Annuler" et "Mettre à jour"

**Tests interactifs :**
1. Modifier le nom du projet
2. Déplacer le marker sur la carte → Coordonnées mises à jour
3. Changer le statut
4. Soumettre → Redirection show avec message succès
5. Vérifier que les modifications sont bien sauvegardées

---

### ✅ **6. Supprimer un projet**
**Action disponible depuis :**
- Liste des projets (bouton supprimer)
- Détails du projet (bouton Supprimer)

**À vérifier :**
- [x] Confirmation JavaScript avant suppression
- [x] Message de confirmation clair
- [x] Après confirmation → Suppression effective
- [x] Redirection vers liste avec message succès
- [x] Financements associés supprimés automatiquement (CASCADE)

**Tests interactifs :**
1. Créer un projet test avec un financement
2. Supprimer le projet
3. Vérifier que le financement a aussi disparu

---

## 💰 TESTS MANAGER - FINANCEMENTS (7 URLs)

### ✅ **7. Liste des financements**
**URL :** http://127.0.0.1:8000/manager/financements

**À vérifier :**
- [x] Tableau responsive avec colonnes :
  - Source
  - Projet (nom avec lien)
  - Montant (formaté)
  - Date de financement
  - Actions (3 icônes)
- [x] 37 financements affichés
- [x] Filtre par projet (dropdown)
- [x] Actions par ligne :
  - 👁️ Voir détails
  - ✏️ Modifier
  - 🗑️ Supprimer
- [x] Bouton "Ajouter un financement"
- [x] Pagination fonctionnelle
- [x] Design cohérent

**Tests interactifs :**
1. Sélectionner un projet dans le filtre → Liste filtrée
2. Cliquer "Réinitialiser" → Tous les financements
3. Cliquer sur nom de projet → Redirection vers projet/show

---

### ✅ **8. Créer un financement**
**URL :** http://127.0.0.1:8000/manager/financements/create

**À vérifier :**
- [x] Formulaire avec champs :
  - Projet (select avec liste des projets)
  - Source de financement (input text)
  - Montant (number)
  - Date de financement (date, défaut: aujourd'hui)
- [x] Section "Informations du projet" dynamique :
  - Affiche budget, financé, restant selon projet sélectionné
  - Mise à jour JavaScript à chaque changement de projet
- [x] Section "Suggestions de sources" avec boutons cliquables :
  - Banque Mondiale
  - BAD
  - Union Européenne
  - Ministère de l'Eau
  - Fonds Privé
- [x] Clic sur suggestion → Remplit le champ source
- [x] Validation et messages d'erreur
- [x] Boutons Annuler/Créer

**Tests interactifs :**
1. Sélectionner un projet → Infos projet affichées
2. Changer de projet → Infos mises à jour
3. Cliquer sur "Banque Mondiale" → Champ source rempli
4. Remplir montant supérieur au budget restant → Possible (alerte affichée après)
5. Soumettre formulaire → Redirection index avec succès
6. Essayer sans projet → Erreur validation

**Financement test à créer :**
```
Projet : (sélectionner un projet existant)
Source : Test Manuel Banque XYZ
Montant : 50000
Date : 2026-10-06
```

---

### ✅ **9. Créer financement depuis projet**
**URL :** http://127.0.0.1:8000/manager/financements/create?projet_id=1

**À vérifier :**
- [x] Identique à create normal
- [x] Projet pré-sélectionné automatiquement (query string)
- [x] Infos du projet affichées dès l'ouverture

**Tests interactifs :**
1. Depuis détails d'un projet, cliquer "Ajouter un financement"
2. Vérifier que le projet est pré-sélectionné
3. Créer le financement
4. Vérifier qu'il apparaît dans le projet

---

### ✅ **10. Voir détails d'un financement**
**URL :** http://127.0.0.1:8000/manager/financements/1
*(Remplacer 1 par l'ID d'un financement existant)*

**À vérifier :**
- [x] En-tête avec :
  - Icône financement
  - Source du financement
  - Bouton Modifier
  - Bouton Supprimer
- [x] 2 cards informations :
  - Montant (formaté)
  - Date de financement
- [x] Section "Projet associé" détaillée :
  - Nom du projet (lien cliquable)
  - Adresse
  - Description
  - Statistiques financières complètes
  - Barre de progression
  - Badge statut
- [x] Section métadonnées (created_at, updated_at)

**Tests interactifs :**
1. Cliquer sur nom du projet → Redirection projet/show
2. Vérifier cohérence montants (projet vs financement)
3. Cliquer Modifier → Formulaire edit
4. Cliquer Supprimer → Confirmation → Suppression

---

### ✅ **11. Modifier un financement**
**URL :** http://127.0.0.1:8000/manager/financements/1/edit

**À vérifier :**
- [x] Formulaire identique à create
- [x] Champs pré-remplis
- [x] Projet modifiable (select)
- [x] JavaScript infos projet fonctionnel
- [x] @method('PUT')
- [x] Validation identique

**Tests interactifs :**
1. Modifier la source
2. Modifier le montant
3. Changer de projet → Infos mises à jour
4. Soumettre → Redirection show avec succès

---

### ✅ **12. Supprimer un financement**
**Action disponible depuis :**
- Liste financements (icône supprimer)
- Détails financement (bouton Supprimer)

**À vérifier :**
- [x] Confirmation avant suppression
- [x] Suppression effective
- [x] Redirection index avec message succès
- [x] Recalcul automatique du projet associé

**Tests interactifs :**
1. Noter le % financé d'un projet
2. Supprimer un de ses financements
3. Vérifier que le % a diminué

---

## 👥 TESTS CITIZEN - PROJETS (2 URLs)

### **Se déconnecter et se reconnecter en Citizen**
1. Cliquer "Déconnexion"
2. Se connecter avec `citizen@aquasecure.tn` / `password`

---

### ✅ **13. Liste des projets (Citizen)**
**URL :** http://127.0.0.1:8000/citizen/projets

**À vérifier :**
- [x] Layout frontoffice (différent de manager)
- [x] Grille 3 colonnes responsive
- [x] Filtres disponibles :
  - Recherche
  - Statut (sans suspendu/annulé)
- [x] Cards cliquables avec :
  - Badge statut
  - Icône géolocalisation
  - Nom
  - Adresse
  - Description (tronquée à 3 lignes)
  - Barre progression
  - Budget / Financé
  - Date début
- [x] **PAS de boutons** Créer/Modifier/Supprimer
- [x] Pagination
- [x] Design mobile-friendly

**Tests interactifs :**
1. Filtrer par recherche
2. Filtrer par statut
3. Vérifier absence de boutons création
4. Cliquer sur une card → Redirection show

---

### ✅ **14. Détails projet (Citizen)**
**URL :** http://127.0.0.1:8000/citizen/projets/1

**À vérifier :**
- [x] Breadcrumb : Accueil > Projets > [Nom]
- [x] En-tête avec badges (statut, géolocalisé)
- [x] Section "À propos"
- [x] Section "Financement" transparente :
  - Budget
  - Montant financé
  - Pourcentage
  - Barre de progression
  - Alerte si totalement financé ou restant
- [x] Liste sources de financement (lecture seule)
- [x] Calendrier (dates + durée)
- [x] Carte Leaflet **lecture seule**
- [x] Coordonnées GPS affichées
- [x] **PAS de boutons** Modifier/Supprimer/Ajouter
- [x] Design transparent et accessible

**Tests interactifs :**
1. Vérifier lecture seule complète
2. Vérifier que les calculs sont corrects
3. Essayer d'accéder à manager/projets/1/edit → Doit être bloqué (session)

---

## 🔒 TESTS SÉCURITÉ

### ✅ **15. Vérifier restrictions Citizen**

**Tests à faire en tant que Citizen :**

1. Essayer d'accéder aux URLs Manager :
   - http://127.0.0.1:8000/manager/projets/create
   - http://127.0.0.1:8000/manager/projets/1/edit
   - http://127.0.0.1:8000/manager/financements/create
   
   **Résultat attendu :** Redirection ou erreur 403 (selon système auth)

2. Essayer de soumettre un formulaire de création (via DevTools)
   **Résultat attendu :** Validation serveur échoue (authorize() retourne false)

---

## 🗺️ TESTS GÉOLOCALISATION

### ✅ **16. Test carte lecture seule**
**URL :** http://127.0.0.1:8000/manager/projets/1

**À vérifier :**
- [x] Carte affichée dans sidebar
- [x] Marker fixe (non déplaçable)
- [x] Zoom fonctionnel (molette, boutons)
- [x] Popup avec nom du projet
- [x] OpenStreetMap chargé
- [x] Pas de scroll wheel zoom (désactivé pour UX)

---

### ✅ **17. Test carte éditable**
**URLs :**
- http://127.0.0.1:8000/manager/projets/create
- http://127.0.0.1:8000/manager/projets/1/edit

**À vérifier :**
- [x] Carte 400px de hauteur
- [x] Instructions visibles
- [x] Clic sur carte → Marker apparaît
- [x] Drag marker → Coordonnées mises à jour
- [x] Modifier input latitude → Marker se déplace
- [x] Modifier input longitude → Marker se déplace
- [x] Validation GPS (-90/90, -180/180)

**Tests interactifs :**
1. Cliquer plusieurs fois → Marker se déplace (un seul marker)
2. Dragging fluide
3. Synchronisation bidirectionnelle inputs ↔ carte

---

### ✅ **18. Test carte multi-projets**
**URL :** http://127.0.0.1:8000/manager/projets/map

**À vérifier :**
- [x] Tous les projets géolocalisés affichés
- [x] Markers colorés différents selon statut
- [x] Légende couleurs affichée
- [x] Popups riches avec infos
- [x] Liens dans popups fonctionnels
- [x] Auto-zoom sur tous les markers
- [x] Performance fluide (19 markers)

**Tests interactifs :**
1. Cliquer sur markers de statuts différents → Couleurs cohérentes
2. Comparer légende vs couleurs réelles
3. Zoomer/dézoomer
4. Cliquer "Voir les détails" dans popup

---

## ✅ TESTS CALCULS MÉTIER

### ✅ **19. Vérifier calculs automatiques**

**Projets spéciaux à tester :**

1. **Projet sans financement (ID 16 - Tozeur)**
   - URL : http://127.0.0.1:8000/manager/projets/16
   - Total financé : 0 DT
   - Budget restant : 100% du budget
   - Pourcentage : 0%
   - Message : "Aucun financement pour ce projet"

2. **Projet totalement financé (ID 17 - Ksar Hellal)**
   - URL : http://127.0.0.1:8000/manager/projets/17
   - Pourcentage : 100%
   - Budget restant : 0 DT
   - Barre verte complète
   - Alerte : "Ce projet est entièrement financé"

3. **Projet dépassement budget (ID 18 - Manouba)**
   - URL : http://127.0.0.1:8000/manager/projets/18
   - Total financé > Budget initial
   - Pourcentage > 100%
   - Budget restant négatif
   - Alerte rouge : "Dépassement du budget"

4. **Projet sans géolocalisation (ID 19)**
   - URL : http://127.0.0.1:8000/manager/projets/19
   - Pas de badge "Géolocalisé"
   - Section localisation : "Ce projet n'est pas géolocalisé"
   - Pas de carte affichée

---

## 📱 TESTS RESPONSIVE

### ✅ **20. Test mobile/tablette**

**À tester sur différentes tailles :**

1. **Desktop (>1024px)**
   - Grille 2 colonnes (projets manager)
   - Grille 3 colonnes (projets citizen)
   - Sidebar visible

2. **Tablette (768px-1024px)**
   - Grilles adaptées
   - Cartes fluides

3. **Mobile (<768px)**
   - Grille 1 colonne
   - Menu burger (citizen)
   - Cartes empilées
   - Formulaires adaptés

**Tests interactifs :**
1. Réduire fenêtre navigateur
2. Utiliser DevTools (F12 > Toggle device toolbar)
3. Tester sur téléphone réel

---

## 🎨 TESTS DESIGN

### ✅ **21. Vérifier cohérence visuelle**

**Éléments à vérifier :**
- [x] Palette cyan/blue cohérente
- [x] Glassmorphism (backdrop-blur)
- [x] Badges colorés selon statut :
  - Planifié : Orange
  - En cours : Bleu
  - Terminé : Vert
  - Suspendu : Jaune
  - Annulé : Rouge
- [x] Icônes Lucide cohérentes
- [x] Transitions hover
- [x] Messages toasts (succès/erreur)

---

## 🔄 TESTS FLUX COMPLETS

### ✅ **22. Flux CRUD complet Projet**

**Scénario :**
1. Connexion Manager
2. Liste projets → Vérifier 19 projets
3. Créer nouveau projet avec géolocalisation
4. Vérifier détails → Calculs corrects
5. Ajouter 2 financements
6. Vérifier % mis à jour
7. Modifier le projet
8. Voir sur carte globale
9. Supprimer le projet
10. Vérifier suppression + cascade financements

---

### ✅ **23. Flux CRUD complet Financement**

**Scénario :**
1. Liste financements → Vérifier 37 financements
2. Créer financement sur projet existant
3. Vérifier détails financement
4. Vérifier que projet associé est mis à jour
5. Modifier le financement
6. Supprimer le financement
7. Vérifier recalcul projet

---

### ✅ **24. Flux Citizen complet**

**Scénario :**
1. Déconnexion
2. Connexion Citizen
3. Liste projets → Pas de boutons création
4. Filtrer projets
5. Voir détails → Lecture seule
6. Essayer d'accéder URL manager → Bloqué
7. Vérifier transparence des données

---

## 📊 CHECKLIST FINALE

### **Fonctionnalités Projets**
- [ ] Liste projets (19 projets)
- [ ] Filtres recherche + statut
- [ ] Statistiques KPIs
- [ ] Carte globale multi-projets
- [ ] Création projet + carte éditable
- [ ] Détails projet + carte lecture seule
- [ ] Modification projet
- [ ] Suppression projet + cascade
- [ ] Calculs automatiques (financé, restant, %)
- [ ] Projet sans géolocalisation géré

### **Fonctionnalités Financements**
- [ ] Liste financements (37 financements)
- [ ] Filtre par projet
- [ ] Création financement
- [ ] Création depuis projet (pré-sélection)
- [ ] Détails financement
- [ ] Modification financement
- [ ] Suppression financement
- [ ] JavaScript infos projet dynamique
- [ ] Suggestions sources cliquables

### **Géolocalisation**
- [ ] Leaflet installé et fonctionnel
- [ ] Carte lecture seule (show)
- [ ] Carte éditable (create/edit)
- [ ] Carte multi-projets (map)
- [ ] Markers draggable
- [ ] Synchronisation inputs ↔ carte
- [ ] OpenStreetMap chargé
- [ ] Attribution affichée
- [ ] Markers colorés selon statut
- [ ] Popups riches fonctionnels

### **Sécurité**
- [ ] CSRF sur formulaires
- [ ] Validation serveur (Form Requests)
- [ ] Citizen bloqué sur routes Manager
- [ ] Pas de création/modification pour Citizen
- [ ] Confirmation suppression
- [ ] Messages erreur clairs

### **UX/Design**
- [ ] Messages flash (toasts)
- [ ] Breadcrumbs
- [ ] Empty states
- [ ] Badges colorés cohérents
- [ ] Design glassmorphism
- [ ] Responsive mobile/tablette
- [ ] Icônes Lucide
- [ ] Transitions fluides

### **Calculs & Données**
- [ ] Total financé calculé
- [ ] Budget restant calculé
- [ ] Pourcentage calculé
- [ ] Barre progression affichée
- [ ] Alerte dépassement budget
- [ ] Alerte projet financé
- [ ] 19 projets + 37 financements en base
- [ ] Données tunisiennes réalistes

---

## 🐛 BUGS À SURVEILLER

### **Bugs potentiels :**
1. Carte ne s'affiche pas → Vérifier console navigateur (F12)
2. Marker Leaflet icône cassée → Vérifier `npm run build` exécuté
3. Validation échoue silencieusement → Vérifier messages d'erreur
4. Calculs incorrects → Vérifier precision decimal
5. Suppression ne fonctionne pas → Vérifier confirmation JavaScript
6. Session expirée → Se reconnecter

### **Solutions rapides :**
```bash
# Régénérer assets
npm run build

# Vider cache
php artisan cache:clear
php artisan view:clear

# Recréer base données
php artisan migrate:fresh --seed

# Redémarrer serveur
php artisan serve
```

---

## ✅ RÉSULTAT ATTENDU

**Si tous les tests passent :**
- ✅ Module complet et fonctionnel
- ✅ CRUD opérationnel pour Projet et Financement
- ✅ Géolocalisation interactive
- ✅ Calculs métier automatiques
- ✅ Sécurité de base respectée
- ✅ Design cohérent et responsive
- ✅ Prêt pour la soutenance ! 🎓

---

## 📞 EN CAS DE PROBLÈME

**Vérifications à faire :**
1. Serveur démarré : `php artisan serve`
2. Assets compilés : `npm run build`
3. Base de données migrée : `php artisan migrate`
4. Données seeded : `php artisan db:seed`
5. Cache vidé : `php artisan cache:clear`

**Console navigateur (F12) :**
- Vérifier erreurs JavaScript
- Vérifier requêtes réseau (onglet Network)
- Vérifier messages console

---

**BON TEST ! 🚀**
