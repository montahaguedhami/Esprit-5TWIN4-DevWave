# AquaSecure 💧

> **Plateforme SaaS de gestion intelligente des infrastructures hydrauliques en Tunisie**

Interface frontend complète avec 4 espaces role-based, composants réutilisables, et design system professionnel.

---

## 🎯 Vue d'ensemble

AquaSecure est une plateforme web moderne pour la surveillance et la gestion des infrastructures d'eau. Cette version représente le **prototype UI/UX complet** avec données statiques pour démonstration.

### ✨ Caractéristiques principales

- **4 espaces utilisateurs** avec interfaces dédiées (Citoyen, Technicien, Manager, Admin)
- **23 composants UI réutilisables** avec design system cohérent
- **35+ pages complètes** avec états visuels et interactions
- **AI Assistant conversationnel** intégré (purple/pink gradient)
- **Responsive design** mobile-first avec navigation hamburger
- **Accessibilité WCAG 2.1 AA** avec ARIA labels complets
- **Glassmorphism** et animations fluides pour une UX premium

---

## 🚀 Démarrage rapide

### Prérequis

- PHP 8.2+
- Composer
- Node.js 18+ & npm
- SQLite (inclus)

### Installation

```bash
# 1. Cloner et installer les dépendances
cd aquasecure
composer install
npm install

# 2. Configuration environnement
cp .env.example .env
php artisan key:generate

# 3. Base de données (SQLite)
touch database/database.sqlite
php artisan migrate

# 4. Compiler les assets
npm run dev

# 5. Lancer le serveur
php artisan serve
```

Accédez à: **http://localhost:8000**

---

## 👥 Comptes démo

Utilisez ces comptes pour explorer les différents espaces:

| Email | Mot de passe | Rôle | Accès |
|-------|-------------|------|-------|
| `citoyen@aquasecure.tn` | `demo123` | **Citoyen** | Signalements, factures, notifications |
| `amira@aquasecure.tn` | `demo123` | **Technicien** | Interventions, équipements, rapports terrain |
| `gestionnaire@aquasecure.tn` | `demo123` | **Manager** | Incidents, équipes, projets, analytics |
| `admin@aquasecure.tn` | `demo123` | **Admin** | Utilisateurs, rôles, système, sécurité |

---

## 📂 Structure du projet

```
aquasecure/
├── app/
│   └── Data/
│       └── PlaceholderData.php          # Toutes les données statiques
├── resources/
│   ├── css/
│   │   └── app.css                      # Design system + animations
│   ├── views/
│   │   ├── components/                  # 23 composants réutilisables
│   │   │   ├── ui/                      # 14 composants UI
│   │   │   ├── forms/                   # 3 composants forms
│   │   │   ├── dashboard/               # 2 composants dashboard
│   │   │   ├── mobile-nav.blade.php     # Navigation mobile
│   │   │   ├── notification-center.blade.php
│   │   │   ├── user-menu.blade.php
│   │   │   └── ai-assistant.blade.php   # AI Assistant
│   │   ├── citizen/                     # 5 pages citoyen
│   │   ├── technician/                  # 4 pages technicien
│   │   ├── manager/                     # 1+ pages manager
│   │   ├── admin/                       # 7 pages admin
│   │   ├── auth/                        # 3 pages authentification
│   │   ├── profile/                     # Page profil
│   │   ├── settings/                    # Pages paramètres
│   │   ├── notifications/               # Centre notifications
│   │   ├── layouts/                     # Layouts réutilisables
│   │   ├── landing.blade.php            # Landing page
│   │   └── ai-demo.blade.php            # Démo AI Assistant
│   └── js/
│       ├── app.js
│       └── bootstrap.js
├── routes/
│   └── web.php                          # 47+ routes
├── ACCESSIBILITY.md                     # Guide accessibilité
└── README.md                            # Ce fichier
```

---

## 🎨 Design System

### Palette de couleurs

```css
/* Backgrounds */
--bg-primary: #061525      /* Navy foncé */
--bg-secondary: #0a2740    /* Navy moyen */
--bg-card: rgba(255,255,255,0.065)  /* Glass effect */

/* Accents */
--accent-cyan: #05bfdb     /* Cyan principal */
--accent-turquoise: #2dd4bf /* Turquoise */
--accent-blue: #088395     /* Bleu profond */

/* Texte */
--text-primary: #f0fdff    /* Blanc très clair */
--text-secondary: #9cc8d8  /* Gris-bleu */

/* États */
--positive: #2dd4bf        /* Success */
--warning: #fbbf24         /* Warning */
--critical: #fb7185        /* Danger */
```

### Typographie

- **Body**: Plus Jakarta Sans (300-700)
- **Display**: Space Grotesk (400-700)
- **Icons**: Lucide Icons

### Classes utilitaires

```css
.glass              /* Glassmorphism standard */
.glass-strong       /* Glassmorphism renforcé */
.text-gradient      /* Gradient cyan→teal→blue */
.hover-lift         /* Animation lift au hover */
.pulse-glow         /* Animation glow pulsante */
```

---

## 📱 Composants UI

### Composants de base (14)

1. **Card** - Container avec glassmorphism
2. **Modal** - Modal accessible avec focus trap
3. **Stat Card** - KPI avec icône et variation
4. **Status Badge** - Badge coloré par statut
5. **Empty State** - État vide avec illustration
6. **Loading Skeleton** - Skeleton loader animé
7. **Alert** - Alert box (success/warning/danger/info)
8. **Dropdown** - Menu déroulant avec animation
9. **Tabs** - Onglets avec indicateur animé
10. **Pagination** - Pagination stylée
11. **Button** - Bouton avec 5 variants
12. **Avatar** - Avatar avec initiales
13. **Search Input** - Input de recherche avec icône
14. **Tooltip** - Tooltip au hover

### Composants Forms (3)

15. **Input** - Input avec label, icône, erreur
16. **Select** - Select stylé
17. **Textarea** - Textarea avec auto-resize

### Composants Dashboard (2)

18. **KPI Card** - Card de métrique avec graphique
19. **Activity Feed** - Timeline d'activités

### Composants Globaux (4)

20. **Mobile Nav** - Navigation hamburger responsive
21. **Notification Center** - Dropdown notifications
22. **User Menu** - Menu utilisateur avec profil
23. **AI Assistant** - Chat assistant IA (purple/pink)

---

## 🌊 Fonctionnalités par rôle

### 🏠 Citoyen (FrontOffice)

**Dashboard**
- Vue d'ensemble qualité de l'eau
- Signalements en cours
- Dernière facture
- Alertes actives

**Signalements** (`/citizen/reports`)
- Créer un signalement multi-étapes
- Consulter l'historique
- Suivre le statut en temps réel
- Ajouter des photos
- Timeline d'intervention

**Factures** (`/citizen/invoices`)
- Liste des factures
- Détail avec graphique de consommation
- Téléchargement PDF (placeholder)
- Paiement en ligne (placeholder)

**Notifications** (`/citizen/notifications`)
- Alertes qualité de l'eau
- Mises à jour signalements
- Rappels factures
- Infos maintenance

---

### 🔧 Technicien (BackOffice Terrain)

**Dashboard**
- Interventions du jour
- Zones assignées
- Équipement disponible
- Statistiques de performance

**Interventions** (`/technician/interventions`)
- Liste avec filtres (programmées/en cours/terminées)
- Détail avec timer temps réel
- Formulaire de rapport terrain
- Upload photos avant/après
- Gestion pièces utilisées

**Équipements** (`/technician/equipment`)
- Inventaire disponible
- Demandes d'équipement
- Statuts (disponible/en cours/maintenance/stock bas)
- Filtres par catégorie

---

### 📊 Manager (BackOffice Gestion)

**Dashboard**
- KPIs globaux
- Carte des incidents
- Performance des équipes
- Projets en cours

**Gestion** (Routes créées, pages à compléter)
- `/manager/incidents` - Gestion des incidents
- `/manager/teams` - Gestion des équipes
- `/manager/map` - Carte du réseau
- `/manager/projects` - Suivi des projets
- `/manager/reports` - Centre de rapports
- `/manager/analytics` - Tableaux analytiques

---

### ⚙️ Admin (BackOffice Système)

**Dashboard**
- Statistiques utilisateurs
- Santé du système
- Alertes sécurité
- Actions rapides

**Utilisateurs** (`/admin/users`)
- Liste complète avec filtres
- CRUD utilisateurs (UI only)
- Assignation de rôles
- Gestion des statuts

**Rôles & Permissions** (`/admin/roles`)
- Matrice de permissions complète
- 4 rôles prédéfinis (Citoyen, Technicien, Manager, Admin)
- Configuration granulaire par module

**Système** (`/admin/system`)
- Métriques temps réel (CPU, RAM, disque)
- Statistiques API
- Statut des services
- Sessions actives

**Logs** (`/admin/logs`)
- Visualisation des logs système
- Filtres par niveau (info/warning/error)
- Recherche full-text
- Export (placeholder)

**Sécurité** (`/admin/security`)
- Score de sécurité global
- Alertes actives/résolues
- Activités suspectes
- Protections actives (2FA, IP whitelist, etc.)

**Sauvegardes** (`/admin/backups`)
- Historique des backups
- Configuration auto-backup
- Téléchargement manuel
- Stockage local/cloud

---

## 🤖 AI Assistant

**Fonctionnalités**
- Floating button animé (gradient purple/pink)
- Chat panel 396x600px avec glassmorphism
- 6 réponses pré-programmées par keywords matching:
  - Qualité de l'eau
  - Créer un signalement
  - Consulter factures
  - Maintenance programmée
  - Aide générale
  - Réponse par défaut

**Interactions**
- Quick actions (3 suggestions)
- Typing indicator animé
- Auto-scroll messages
- Enter pour envoyer (Shift+Enter pour nouvelle ligne)
- Escape pour fermer
- Responsive (fullscreen sur mobile)

**Page démo**: `/ai-demo`

---

## ♿ Accessibilité

### Standards respectés

- **WCAG 2.1 niveau AA**
- Navigation clavier complète
- ARIA labels sur tous les composants interactifs
- Focus visible et trap dans les modals
- Ratios de contraste validés (14.2:1 pour texte principal)
- Support `prefers-reduced-motion`
- Touch targets minimum 44x44px

### Navigation clavier

- **Tab**: Élément suivant
- **Shift+Tab**: Élément précédent
- **Enter/Space**: Activer
- **Escape**: Fermer modals/dropdowns
- **Flèches**: Navigation dans menus

Voir **[ACCESSIBILITY.md](./ACCESSIBILITY.md)** pour le guide complet.

---

## 📱 Responsive Design

### Breakpoints

- **Mobile**: < 640px (navigation hamburger, full-width cards)
- **Tablet**: 640px - 1024px (grilles 2 colonnes)
- **Desktop**: > 1024px (grilles 3-4 colonnes, expérience complète)

### Classes utilitaires

```html
<!-- Visibilité -->
<div class="mobile-only">Visible uniquement sur mobile</div>
<div class="desktop-only">Visible uniquement sur desktop</div>

<!-- Layout -->
<div class="mobile-stack">Stack sur mobile</div>
<div class="mobile-full">Pleine largeur sur mobile</div>

<!-- Grilles -->
<div class="tablet-grid-2">2 colonnes sur tablet</div>
<div class="desktop-grid-3">3 colonnes sur desktop</div>
```

---

## 🧪 Tests

### Tests manuels recommandés

1. **Navigation**
   - Tester tous les liens entre pages
   - Vérifier les redirections selon rôle
   - Confirmer les breadcrumbs

2. **Composants**
   - Tester tous les états (loading, error, empty, success)
   - Vérifier les animations
   - Tester sur différents navigateurs

3. **Responsive**
   - Tester sur mobile (< 640px)
   - Tester sur tablet (768px)
   - Tester sur desktop (1024px+)

4. **Accessibilité**
   - Navigation clavier complète
   - Test avec lecteur d'écran (NVDA, VoiceOver)
   - Vérifier avec axe DevTools

---

## 🔧 Technologies utilisées

### Backend
- **Laravel 11.x** - Framework PHP
- **Blade Templates** - Moteur de templates
- **SQLite** - Base de données (dev)

### Frontend
- **Tailwind CSS v4** - Framework CSS avec @theme syntax
- **Lucide Icons** - Bibliothèque d'icônes
- **Vanilla JavaScript** - Interactions (pas de framework JS)

### Fonts
- **Plus Jakarta Sans** - Police principale (Google Fonts)
- **Space Grotesk** - Police display (Google Fonts)

---

## 📝 Notes importantes

### ⚠️ Frontend uniquement

Ce projet est un **prototype UI/UX complet** avec:
- ✅ **Toutes les interfaces visuelles**
- ✅ **Composants réutilisables**
- ✅ **Données statiques (PlaceholderData.php)**
- ✅ **Routes GET pour navigation**
- ✅ **Authentification simulée (session-based)**

**Non inclus** (backend à implémenter):
- ❌ Eloquent Models & migrations réelles
- ❌ CRUD backend fonctionnel
- ❌ API REST
- ❌ Authentification Laravel (Breeze/Fortify)
- ❌ Validation serveur
- ❌ Base de données réelle avec données

### 🎯 Prochaines étapes (backend)

Pour transformer ce prototype en application fonctionnelle:

1. **Models & Migrations**
   ```bash
   php artisan make:model Report -m
   php artisan make:model Intervention -m
   php artisan make:model Invoice -m
   ```

2. **Controllers**
   ```bash
   php artisan make:controller Citizen/ReportController --resource
   php artisan make:controller Technician/InterventionController --resource
   ```

3. **Authentication**
   ```bash
   composer require laravel/breeze --dev
   php artisan breeze:install blade
   ```

4. **API (optionnel)**
   ```bash
   php artisan make:controller Api/V1/ReportController --api
   ```

---

## 📚 Documentation additionnelle

- **[ACCESSIBILITY.md](./ACCESSIBILITY.md)** - Guide complet d'accessibilité WCAG 2.1
- **Routes**: Voir `routes/web.php` (47+ routes)
- **Composants**: Voir `resources/views/components/`
- **Data**: Voir `app/Data/PlaceholderData.php`

---

## 🤝 Contribuer

Ce projet est un prototype. Pour contribuer:

1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

---

## 📜 Licence

Projet éducatif - Tous droits réservés

---

## 👨‍💻 Auteur

**AquaSecure Team**  
Plateforme de gestion des infrastructures hydrauliques en Tunisie

---

## 🙏 Remerciements

- **Tailwind CSS** - Framework CSS moderne
- **Lucide Icons** - Icônes open-source
- **Laravel** - Framework PHP élégant
- **Google Fonts** - Typographies web

---

**Version**: 1.0.0 (Frontend Prototype)  
**Dernière mise à jour**: 2026-09-26  
**Status**: ✅ Prototype UI/UX complet - Prêt pour intégration backend
