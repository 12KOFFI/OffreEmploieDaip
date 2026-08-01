# DAIP Emploi - Registre d'offres d'emploi supervisé par la DAIP

Application Symfony de gestion et consultation d'offres d'emploi, avec deux espaces principaux :
- **Espace public** : consultation du registre des offres
- **Espace sécurisé** : tableaux de bord pour la DAIP et les entreprises inscrites

## 📋 Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Architecture](#architecture)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
- [Structure du projet](#structure-du-projet)
- [Détail des fonctionnalités](#détail-des-fonctionnalités)
- [Design & UX](#design--ux)
- [Sécurité](#sécurité)

## ✨ Fonctionnalités

### Espace public
- Accueil avec présentation du registre
- Consultation des offres publiées
- Page de détail d'une offre (référence, description, compétences, rémunération, dates, partage)
- Système d'authentification (connexion / inscription entreprise)
- Mot de passe oublié avec réinitialisation par email
- Footer et navigation responsive

### Espace DAIP (ROLE_DAIP)
- Tableau de bord avec KPIs (total, publiées, brouillons, retirées/expirées)
- Registre des offres avec filtres (statut, secteur, ville, dates) et pagination
- Registre des entreprises avec pagination
- Page de détail d'une entreprise (infos, description, offres publiées)
- Journal d'activité
- Gestion des comptes entreprises

### Espace Entreprise (ROLE_ENTREPRISE)
- Tableau de bord personnel avec KPIs
- Mes offres : création, modification, duplication, publication, retrait, suppression
- Pagination sur la liste des offres
- Formulaire d'offre avec image, date d'expiration et compétences
- Page de profil entreprise (nom, SIRET, site web, logo, description)
- Redirection automatique vers le bon dashboard selon le rôle

## 🏗️ Architecture

- **Framework** : Symfony 7.x
- **Langage** : PHP 8.2+
- **Base de données** : MySQL / MariaDB
- **Frontend** : Tailwind CSS via AssetMapper
- **Animations** : AOS (Animate On Scroll)
- **Uploads** : images d'offres stockées dans `public/uploads/offres`
- **Email** : Symfony Mailer (pour la réinitialisation de mot de passe)

## 📦 Prérequis

- PHP 8.2 ou supérieur
- Composer
- Node.js / npm (pour Tailwind CSS)
- MySQL ou MariaDB
- Extension PHP : pdo_mysql, intl, zip, gd (optionnel pour le traitement d'images)

## 🚀 Installation

```bash
# 1. Cloner le dépôt
git clone <url-du-depot>
cd JobBoard-DAIP

# 2. Installer les dépendances PHP
composer install

# 3. Copier le fichier d'environnement
cp .env.example .env

# 4. Configurer la base de données dans .env
# DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/jobboard"

# 5. Créer la base de données et exécuter les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 6. Charger les données de test (optionnel)
php bin/console doctrine:fixtures:load

# 7. Installer les assets Tailwind
php bin/console tailwind:build

# 8. Compiler les assets
php bin/console asset-map:compile

# 9. Lancer le serveur local
php bin/console server:start
# OU
symfony server:start
```

## ⚙️ Configuration

### Variables d'environnement (.env)

```env
APP_ENV=dev
APP_SECRET=<secret-generé-par-symfony>

DATABASE_URL="mysql://root:root@127.0.0.1:3306/jobboard?serverVersion=8.0"

MAILER_DSN=smtp://localhost:1025
```

### Comptes par défaut

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| DAIP | daip@daip-emploi.local | password |
| Entreprise | entreprise@example.com | password |

## 📖 Utilisation

### Workflow entreprise

1. **Inscription** : créer un compte entreprise sur la page d'inscription
2. **Connexion** : se connecter avec ses identifiants
3. **Tableau de bord** : vue d'ensemble des KPIs (total, publiées, brouillons, retirées)
4. **Mes offres** :
   - Créer une nouvelle offre (brouillon par défaut)
   - Modifier / Dupliquer / Publier / Retirer / Supprimer
   - Pagination automatique (10 par page)
5. **Profil** : modifier les informations de l'entreprise

### Workflow DAIP

1. **Connexion** : se connecter avec un compte DAIP
2. **Tableau de bord** : supervision globale
3. **Registre des offres** :
   - Filtrer par statut, secteur, ville, dates
   - Consulter le détail d'une offre
   - Pagination
4. **Registre des entreprises** :
   - Rechercher par nom ou email
   - Consulter le détail d'une entreprise et ses offres publiées
5. **Journal d'activité** : trace des actions

## 📁 Structure du projet

```
jobboard/
├── assets/
│   ├── controllers.json
│   ├── styles/
│   │   └── app.css              # Styles Tailwind + composants personnalisés
│   └── ...
├── config/
│   ├── packages/
│   │   └── security.yaml        # Configuration sécurité et rôles
│   └── routes/
├── migrations/
├── public/
│   ├── uploads/
│   │   └── offres/              # Images uploadées des offres
│   └── ...
├── src/
│   ├── Controller/
│   │   ├── Daip/
│   │   │   ├── DashboardController.php
│   │   │   ├── EntrepriseController.php
│   │   │   ├── JournalController.php
│   │   │   ├── OffreController.php
│   │   │   └── ProfilController.php
│   │   ├── Entreprise/
│   │   │   ├── DashboardController.php
│   │   │   ├── OffreController.php
│   │   │   └── ProfilController.php
│   │   ├── ForgotPasswordController.php
│   │   ├── HomeController.php
│   │   ├── OffreController.php
│   │   ├── RegistrationController.php
│   │   └── SecurityController.php
│   ├── Entity/
│   │   ├── Offre.php
│   │   ├── Entreprise.php
│   │   ├── User.php
│   │   └── ...
│   ├── Enum/
│   │   ├── NiveauEtude.php
│   │   └── StatutOffre.php
│   ├── Form/
│   │   ├── OffreType.php
│   │   ├── DaipProfilType.php
│   │   ├── ForgotPasswordRequestFormType.php
│   │   └── ResetPasswordFormType.php
│   ├── Repository/
│   │   ├── OffreRepository.php
│   │   └── ...
│   ├── Security/
│   │   └── OffreVoter.php
│   └── Service/
│       └── OffreManager.php
├── templates/
│   ├── base.html.twig
│   ├── dashboard_base.html.twig
│   ├── offres/
│   │   ├── show.html.twig
│   │   ├── _card.html.twig
│   │   └── ...
│   ├── daip/
│   │   ├── dashboard.html.twig
│   │   ├── entreprises/
│   │   │   ├── index.html.twig
│   │   │   ├── show.html.twig
│   │   │   └── _tableau.html.twig
│   │   └── offres/
│   │       ├── index.html.twig
│   │       └── _tableau.html.twig
│   ├── entreprise/
│   │   ├── dashboard.html.twig
│   │   ├── offres/
│   │   │   ├── form.html.twig
│   │   │   └── index.html.twig
│   │   └── profil/
│   │       └── edit.html.twig
│   ├── security/
│   │   ├── login.html.twig
│   │   ├── forgot_password.html.twig
│   │   └── reset_password.html.twig
│   └── partials/
│       ├── _flashes.html.twig
│       └── _scripts.html.twig
└── tailwind.config.js
```

## 🔍 Détail des fonctionnalités

### Authentification
- Connexion avec redirection automatique selon le rôle (DAIP / Entreprise)
- Inscription entreprise
- Mot de passe oublié avec token et lien de réinitialisation
- Effet de chargement sur les boutons

### Offres
- Création en brouillon avec image, date d'expiration, compétences
- Sélection multiple de compétences existantes
- Statuts : Brouillon, Publiée, Retirée, Expirée
- Badges colorés par statut
- Pagination sur les listes (10 par page)
- Cards cliquables vers le détail
- Détail complet avec image, description, compétences, rémunération, dates, partage

### Entreprises
- Profil entreprise modifiable
- Registre des entreprises pour la DAIP
- Page de détail entreprise avec offres publiées

### Design
- Thème professionnel sans mode sombre
- Sidebar responsive avec menu hamburger
- Effets de survol sur les cards
- Notifications flash avec disparition automatique après 4 secondes
- Animations AOS
- Typographie : Poppins (titres) + Inter (corps)

## 🎨 Design & UX

- **Palette** : navy, indigo, orange, vert
- **Badges** : brouillon (slate), publiée (vert), retirée (rouge), expirée (slate)
- **Cards** : bordures colorées, ombres portées, effet de zoom au survol
- **Boutons** : styles variés (orange, gradient, ghost, danger)
- **Inputs** : icônes intégrées, états de focus

## 🔒 Sécurité

- Authentification par formulaire CSRF protégé
- Rôles : ROLE_USER, ROLE_DAIP, ROLE_ENTREPRISE
- Voters Symfony pour les actions sur les offres
- Mots de passe hashés
- Token de réinitialisation de mot de passe avec expiration
- Accès refusé si l'utilisateur n'a pas de profil entreprise associé

## 📝 Notes

- Les images d'offres sont stockées dans `public/uploads/offres/`
- Les migrations sont dans le dossier `migrations/`
- Les fixtures de test sont disponibles pour peupler la base de données

## 🤝 Contribution

Ce projet est privé. Toute contribution doit être validée par le responsable technique.

## 📄 Licence

Propriétaire - Tous droits réservés.