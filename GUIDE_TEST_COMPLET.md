# 🧪 GUIDE DE TEST COMPLET - MODULE PROJET/FINANCEMENT
# AquaSecure - Tests Manager & Citizen

**Date :** 6 octobre 2026  
**Statut :** Routes corrigées ✅  
**Objectif :** Vérifier l'accès complet au module depuis les deux interfaces

---

## 🚀 PRÉPARATION

### 1️⃣ **Lancer le serveur**
```bash
php artisan serve
```

**URL de base :** http://127.0.0.1:8000

### 2️⃣ **Identifiants de test**

**Manager :**
- Email : `manager@aquasecure.tn`
- Mot de passe : `password`

**Citizen :**
- Email : `citizen@aquasecure.tn`
- Mot de passe : `password`

---

## 👨‍💼 PARTIE 1 : TESTS MANAGER

### ✅ **Test 1.1 - Accès depuis Dashboard**

**Étapes :**
1. Connexion Manager
2. Depuis dashboard, observer la **navigation supérieure**
3. **Vérifier :** Onglet "Projets" présent (icône briefcase)
4. Cliquer sur "Projets"
5. **Résultat attendu :** Liste de 19 projets affichée

**URL finale :** http://127.0.0.1:8000/manager/projets

---

### ✅ **Test 1.2 - Accès depuis liens rapides**

**Étapes :**
1. Depuis dashboard, scroller jusqu'en bas
2. Observer les **4 liens rapides** (Incidents, Équipes, Projets, Analytics)
3. Cliquer sur "Projets"
4. **Résultat attendu :** Liste de 19 projets

---

### ✅ **Test 1.3 - Accès depuis menu mobile**

**Étapes :**
1. Réduire fenêtre navigateur (<768px) OU ouvrir DevTools (F12) en mode mobile
2. Cliquer sur icône burger (☰) en haut à gauche
3. Observer le menu latéral
4. **Vérifier :** Lien "Projets" présent (icône briefcase)
5. Cliquer sur "Projets"
6. **Résultat attendu :** Liste de 19 projets

---

### ✅ **Test 1.4 - Navigation CRUD complète**

**Étapes :**
1. Depuis liste projets, cliquer **"Créer un projet"**
2. **Vérifier :** Formulaire avec carte Leaflet interactive
3. Cliquer sur la carte (placer un marker)
4. **Vérifier :** Latitude/Longitude remplies automatiquement
5. Remplir le formulaire :
   ```
   Nom : Test Navigation Routes
   Budget : 100000
   Date début : 2026-10-01
   Statut : en_cours
   ```
6. Soumettre
7. **Résultat attendu :** Redirection vers liste + message succès

---

### ✅ **Test 1.5 - Carte globale**

**Étapes :**
1. Depuis liste projets, cliquer **"Carte des projets"**
2. **Vérifier :**
   - Carte Leaflet avec markers colorés
   - Légende des statuts affichée
   - Popups cliquables
3. Cliquer sur un marker
4. **Vérifier :** Popup avec nom, statut, budget, lien "Voir les détails"
5. Cliquer "Voir les détails"
6. **Résultat attendu :** Page détails du projet

**URL carte :** http://127.0.0.1:8000/manager/projets/map

---

### ✅ **Test 1.6 - Gestion financements**

**Étapes :**
1. Navigation Manager > cliquer **"Projets"**
2. Cliquer sur un projet dans la liste
3. Dans la page détails, cliquer **"Ajouter un financement"**
4. **Vérifier :** Formulaire avec projet pré-sélectionné
5. Remplir :
   ```
   Source : Test Navigation Banque ABC
   Montant : 25000
   Date : 2026-10-06
   ```
6. Soumettre
7. **Résultat attendu :** Redirection + message succès
8. Retour au projet
9. **Vérifier :** Pourcentage financé mis à jour

---

## 👥 PARTIE 2 : TESTS CITIZEN

### ✅ **Test 2.1 - Accès depuis navigation desktop**

**Étapes :**
1. **Déconnexion** puis connexion Citizen
2. Observer la **navigation supérieure** (header)
3. **Vérifier :** 3 onglets visibles : Accueil, **Projets**, Factures
4. Cliquer sur "Projets"
5. **Résultat attendu :** Liste de 19 projets (grille 3 colonnes)

**URL :** http://127.0.0.1:8000/citizen/projets

**Note :** Navigation desktop visible uniquement si largeur > 768px

---

### ✅ **Test 2.2 - Accès depuis navigation mobile**

**Étapes :**
1. Réduire fenêtre navigateur (<768px) OU mode mobile DevTools
2. Observer la **bottom navigation bar** (barre fixe en bas)
3. **Vérifier :** 6 icônes dont "Projets" (briefcase) entre Accueil et Signaler
4. Taper sur "Projets"
5. **Résultat attendu :** Liste responsive des projets

---

### ✅ **Test 2.3 - Accès depuis dashboard (CTA)**

**Étapes :**
1. Depuis dashboard citizen
2. Scroller jusqu'à la section **"Projets d'infrastructure"**
3. **Vérifier :**
   - Card avec gradient cyan/blue
   - Texte explicatif présent
   - Bouton "Explorer les projets" avec icône carte
4. Cliquer sur "Explorer les projets"
5. **Résultat attendu :** Liste des projets

---

### ✅ **Test 2.4 - Active states**

**Étapes :**
1. Depuis liste projets, observer la **navigation**
2. **Vérifier :** Onglet/icône "Projets" est **actif** (surligné en cyan)
3. Cliquer sur un projet pour voir les détails
4. **Vérifier :** Active state toujours présent
5. Naviguer vers Dashboard
6. **Vérifier :** Active state retiré de "Projets"

---

### ✅ **Test 2.5 - Interface lecture seule**

**Étapes :**
1. Depuis liste projets citizen
2. **Vérifier :**
   - ❌ PAS de bouton "Créer un projet"
   - ❌ PAS de bouton "Modifier"
   - ❌ PAS de bouton "Supprimer"
   - ✅ Filtres présents (recherche, statut)
3. Cliquer sur un projet
4. **Vérifier :**
   - ❌ PAS de boutons modification/suppression
   - ✅ Carte Leaflet en **lecture seule** (marker fixe)
   - ✅ Financements affichés (lecture seule)
   - ✅ Calculs visibles (budget, %, restant)

---

### ✅ **Test 2.6 - Tentative accès non autorisé**

**Étapes :**
1. Connecté en tant que Citizen
2. Essayer d'accéder manuellement à :
   ```
   http://127.0.0.1:8000/manager/projets/create
   http://127.0.0.1:8000/manager/projets/1/edit
   ```
3. **Résultat attendu :** Redirection ou erreur 403

**Sécurité :** ✅ Citizen ne peut pas accéder aux routes Manager

---

## 🗺️ PARTIE 3 : TESTS GÉOLOCALISATION

### ✅ **Test 3.1 - Carte éditable (Manager)**

**Étapes :**
1. Manager > Créer un projet
2. Observer la **carte Leaflet** (400px)
3. **Vérifier :** Instructions "Cliquez pour placer un marker"
4. Cliquer sur la carte
5. **Vérifier :** Marker apparaît + coordonnées remplies
6. Drag le marker (déplacer)
7. **Vérifier :** Coordonnées mises à jour en temps réel
8. Modifier manuellement latitude (input)
9. **Vérifier :** Marker se déplace sur la carte

**Synchronisation :** ✅ Bidirectionnelle inputs ↔ carte

---

### ✅ **Test 3.2 - Carte lecture seule (Citizen)**

**Étapes :**
1. Citizen > Ouvrir détails d'un projet géolocalisé
2. Observer la carte dans la sidebar
3. **Vérifier :**
   - Marker fixe (pas déplaçable)
   - Zoom fonctionnel (boutons +/-)
   - Scroll wheel désactivé
   - Popup avec nom du projet
4. Essayer de déplacer le marker
5. **Résultat attendu :** Aucun effet (lecture seule)

---

### ✅ **Test 3.3 - Carte multi-projets (Manager)**

**Étapes :**
1. Manager > Carte des projets
2. **Vérifier :**
   - Markers colorés selon statut
   - Légende affichée (5 couleurs)
   - Auto-zoom sur tous les markers
3. Cliquer sur markers de **différents statuts**
4. **Vérifier :** Couleurs cohérentes avec légende :
   - 🟢 Vert : Terminé
   - 🔵 Bleu : En cours
   - 🟠 Orange : Planifié
   - 🟡 Jaune : Suspendu
   - 🔴 Rouge : Annulé
5. Zoomer/Dézoomer
6. **Vérifier :** Carte réactive et fluide

---

## 🔢 PARTIE 4 : TESTS CALCULS MÉTIER

### ✅ **Test 4.1 - Projet sans financement**

**URL :** http://127.0.0.1:8000/manager/projets/16

**Vérifications :**
- Total financé : **0 DT**
- Budget restant : **100% du budget**
- Pourcentage : **0%**
- Barre de progression : **vide**
- Message : "Aucun financement pour ce projet"

---

### ✅ **Test 4.2 - Projet totalement financé**

**URL :** http://127.0.0.1:8000/manager/projets/17

**Vérifications :**
- Pourcentage : **100%**
- Budget restant : **0 DT**
- Barre de progression : **pleine (verte)**
- **Alerte verte :** "Ce projet est entièrement financé"

---

### ✅ **Test 4.3 - Projet dépassement budget**

**URL :** http://127.0.0.1:8000/manager/projets/18

**Vérifications :**
- Total financé : **> Budget initial**
- Pourcentage : **> 100%**
- Budget restant : **négatif**
- Barre de progression : **pleine (rouge)**
- **Alerte rouge :** "Dépassement du budget de XX DT"

---

### ✅ **Test 4.4 - Projet sans géolocalisation**

**URL :** http://127.0.0.1:8000/manager/projets/19

**Vérifications :**
- ❌ Pas de badge "Géolocalisé"
- Section localisation : "Ce projet n'est pas géolocalisé"
- ❌ Pas de carte affichée
- ✅ Autres informations complètes

---

## 🎨 PARTIE 5 : TESTS RESPONSIVE

### ✅ **Test 5.1 - Desktop (>1024px)**

**Vérifications Manager :**
- Liste projets : **grille 2 colonnes**
- Navigation : tab bar horizontale
- Sidebar détails : visible

**Vérifications Citizen :**
- Liste projets : **grille 3 colonnes**
- Navigation : onglets horizontaux visibles
- Bottom bar : cachée

---

### ✅ **Test 5.2 - Tablette (768-1024px)**

**Vérifications :**
- Grilles adaptatives
- Navigation compacte
- Cards fluides

---

### ✅ **Test 5.3 - Mobile (<768px)**

**Vérifications Manager :**
- Liste projets : **grille 1 colonne**
- Navigation : burger menu
- Cartes empilées verticalement

**Vérifications Citizen :**
- Liste projets : **grille 1 colonne**
- Navigation desktop : cachée
- Bottom bar : visible et fixe

**Test pratique :**
1. Ouvrir DevTools (F12)
2. Toggle device toolbar (Ctrl+Shift+M)
3. Sélectionner "iPhone 12 Pro" ou "Pixel 5"
4. Naviguer dans l'interface
5. **Vérifier :** Tout est cliquable et lisible

---

## ✅ CHECKLIST FINALE GLOBALE

### **Accès Manager**
- [ ] Dashboard > Navigation projets → OK
- [ ] Dashboard > Liens rapides → OK
- [ ] Menu burger mobile → OK
- [ ] Layout Manager tab bar → OK
- [ ] Ancienne route /manager/projects → Redirigée

### **Accès Citizen**
- [ ] Navigation desktop (header) → OK
- [ ] Bottom navigation mobile → OK
- [ ] Dashboard CTA card → OK
- [ ] Menu burger mobile → OK
- [ ] Active states fonctionnels → OK

### **Fonctionnalités Manager**
- [ ] Liste 19 projets → OK
- [ ] CRUD projets complet → OK
- [ ] CRUD financements complet → OK
- [ ] Carte globale interactive → OK
- [ ] Carte éditable create/edit → OK
- [ ] Calculs automatiques → OK

### **Fonctionnalités Citizen**
- [ ] Liste 19 projets (lecture seule) → OK
- [ ] Détails projets (lecture seule) → OK
- [ ] Carte lecture seule → OK
- [ ] Filtres fonctionnels → OK
- [ ] PAS de création/modification → OK ✅

### **Géolocalisation**
- [ ] Leaflet chargé → OK
- [ ] Markers draggables (Manager) → OK
- [ ] Markers fixes (Citizen) → OK
- [ ] Synchronisation inputs ↔ carte → OK
- [ ] OpenStreetMap tiles → OK
- [ ] Markers colorés selon statut → OK

### **Calculs & Données**
- [ ] Total financé calculé → OK
- [ ] Budget restant calculé → OK
- [ ] Pourcentage calculé → OK
- [ ] Protection division par zéro → OK
- [ ] Alertes dépassement budget → OK
- [ ] 19 projets en base → OK
- [ ] 37 financements en base → OK

### **Sécurité**
- [ ] CSRF protection → OK
- [ ] Validation serveur → OK
- [ ] Citizen bloqué routes Manager → OK
- [ ] Confirmations suppression → OK
- [ ] Messages erreur clairs → OK

### **UX/Design**
- [ ] Messages flash (toasts) → OK
- [ ] Breadcrumbs → OK
- [ ] Empty states → OK
- [ ] Badges colorés cohérents → OK
- [ ] Responsive mobile/desktop → OK
- [ ] Transitions fluides → OK

---

## 🎯 SCORE DE RÉUSSITE

**Total points :** 50  
**Points validés :** _____ / 50

**Seuil de validation :** 45/50 (90%)

---

## 🐛 PROBLÈMES CONNUS (RÉSOLUS)

### ✅ **Problème 1 : Routes Manager**
- **Description :** Liens "Projets" pointaient vers placeholder
- **Cause :** Route `manager.projects` au lieu de `manager.projets.index`
- **Correctif :** 4 fichiers modifiés (dashboard, layout, mobile-nav, projects.blade.php)
- **Statut :** ✅ RÉSOLU

### ✅ **Problème 2 : Navigation Citizen absente**
- **Description :** Aucun lien visible vers projets
- **Cause :** Liens non ajoutés dans frontoffice layout et dashboard
- **Correctif :** 3 fichiers modifiés (frontoffice layout, dashboard, mobile-nav)
- **Statut :** ✅ RÉSOLU

---

## 📞 EN CAS DE PROBLÈME

### **Commandes de dépannage :**
```bash
# Vider les caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Régénérer assets
npm run build

# Recréer base de données
php artisan migrate:fresh --seed

# Redémarrer serveur
php artisan serve
```

### **Console navigateur (F12) :**
- Onglet **Console** : Erreurs JavaScript
- Onglet **Network** : Requêtes HTTP (codes 404, 500)
- Onglet **Application** : Session storage

---

## ✅ CONCLUSION

Si **tous les tests passent**, votre module est :
- ✅ **Fonctionnel** : CRUD complet Manager + lecture seule Citizen
- ✅ **Sécurisé** : Validation, CSRF, autorisation
- ✅ **Accessible** : Navigation claire sur desktop + mobile
- ✅ **Performant** : Eager loading, indexes, pagination
- ✅ **Géolocalisé** : Leaflet/OSM parfaitement intégré
- ✅ **Responsive** : Design adaptatif multi-écrans

**🎓 PRÊT POUR LA SOUTENANCE !**

---

**BON TEST ! 🚀**

**Documents complémentaires :**
- `AUDIT_COMPLET.md` - Analyse détaillée du module
- `CORRECTIF_ROUTES.md` - Corrections routes Manager
- `CORRECTIF_CITIZEN_ROUTES.md` - Corrections routes Citizen
- `GUIDE_TEST.md` - Guide original (URLs)
