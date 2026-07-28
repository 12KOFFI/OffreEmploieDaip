# DAIP Emploi — Plateforme d'offres d'emploi (Symfony)

## Pourquoi ces fichiers seulement ?

Je n'ai pas accès à Packagist depuis mon environnement, donc je ne peux pas
exécuter `composer create-project` moi-même. Les fichiers fournis ici
(entités Doctrine, enums, repositories, config sécurité) sont à copier dans
un projet Symfony fraîchement créé chez vous. Ça prend 5 minutes.

## 1. Prérequis

- PHP 8.2 ou plus
- Composer
- Symfony CLI (recommandé) : https://symfony.com/download
- MySQL / MariaDB ou PostgreSQL

## 2. Créer le projet Symfony

```bash
symfony new jobboard --version="7.*" --webapp
cd jobboard
```

Le flag `--webapp` installe déjà : Twig, Doctrine ORM, Doctrine Migrations,
Security Bundle, Form, Validator, Mailer, Maker Bundle.

## 3. Installer les paquets supplémentaires dont on aura besoin

```bash
composer require symfony/ux-turbo
composer require knplabs/knp-paginator-bundle
composer require easycorp/easyadmin-bundle
composer require --dev symfony/maker-bundle
```

## 4. Copier les fichiers fournis

Copiez le contenu de ce dossier dans votre projet fraîchement créé
(en écrasant les fichiers de config équivalents) :

```
src/Entity/*.php        → jobboard/src/Entity/
src/Repository/*.php    → jobboard/src/Repository/
src/Enum/*.php          → jobboard/src/Enum/
config/packages/security.yaml → jobboard/config/packages/security.yaml
```

## 5. Configurer la base de données

Dans `.env`, adaptez la ligne :

```
DATABASE_URL="mysql://user:password@127.0.0.1:3306/jobboard?serverVersion=8.0"
```

Puis créez la base :

```bash
symfony console doctrine:database:create
```

## 6. Générer et lancer la migration

Comme les entités sont déjà écrites, Doctrine peut générer directement la
migration SQL correspondante :

```bash
symfony console make:migration
symfony console doctrine:migrations:migrate
```

Vérifiez le fichier de migration généré dans `migrations/` avant de
l'exécuter — c'est une bonne habitude à prendre.

## 7. Vérifier que tout est bien mappé

```bash
symfony console doctrine:schema:validate
```

Vous devriez voir "The mapping files are correct" et "The database schema
is in sync".

## 8. Lancer le serveur

```bash
symfony server:start
```

## Prochaine étape

Une fois ces 8 étapes faites et validées chez vous, on enchaîne sur
l'authentification : `make:auth` pour générer le contrôleur de connexion,
puis un contrôleur d'inscription personnalisé qui gère le choix
Candidat / Entreprise et crée le bon profil associé.

## Périmètre du projet (mis à jour)

Deux acteurs uniquement :

- **Entreprise** (`ROLE_ENTREPRISE`) — s'inscrit librement, crée des offres,
  et gère **elle-même tout le cycle de vie** de ses offres : brouillon,
  publication, retrait. Aucune validation externe requise.
- **DAIP** (`ROLE_DAIP`) — voit toutes les offres de toutes les entreprises,
  en **lecture seule uniquement**. Ne crée, ne modifie, et ne change JAMAIS
  le statut d'une offre. Son rôle se limite à la supervision/consultation
  globale (utile par exemple pour des statistiques ou un suivi d'activité).

Le grand public consulte librement les offres publiées, sans compte.
Il n'y a pas de système de candidature dans ce périmètre.

## Étape 9 — Authentification (nouveau)

Fichiers ajoutés, à copier dans votre projet :

```
src/Controller/SecurityController.php      → src/Controller/
src/Controller/RegistrationController.php  → src/Controller/
src/Form/EntrepriseRegistrationType.php    → src/Form/
src/Command/CreateDaipCommand.php          → src/Command/
templates/security/login.html.twig         → templates/security/
templates/registration/register.html.twig  → templates/registration/
```

Ces fichiers utilisent `{% extends 'base.html.twig' %}` avec un bloc
`{% block body %}` — c'est le template généré par défaut par
`symfony new --webapp`, vous n'avez rien à faire dessus pour l'instant.

### Créer le tout premier compte DAIP

```bash
symfony console app:create-daip daip@example.com "un-mot-de-passe-solide"
```

### Tester

```bash
symfony server:start
```

- `http://localhost:8000/inscription` — créer un compte entreprise
- `http://localhost:8000/connexion` — se connecter (entreprise ou DAIP)

⚠️ **Attention** : `SecurityController::dashboardRedirect()` redirige vers
`entreprise_offres_index` et `daip_offres_index`, qui **n'existent pas
encore** — c'est normal, on les crée à la prochaine étape (dashboards +
CRUD des offres). Tant que ces routes ne sont pas créées, la connexion
réussira mais la redirection donnera une erreur 500 sur cette dernière
étape. Rien de cassé, juste la suite à venir.

## Étape 10 — Page d'accueil professionnelle (nouveau)

Fichiers ajoutés :

```
src/Controller/HomeController.php   → src/Controller/
templates/base.html.twig            → templates/  (remplace le base.html.twig par défaut)
templates/home/index.html.twig      → templates/home/
```

⚠️ **Important** : `templates/base.html.twig` **remplace entièrement**
celui généré par `symfony new --webapp`. Il contient tout le système de
design (couleurs, typographie, nav, footer) utilisé par toutes les pages,
y compris `login.html.twig` et `register.html.twig` déjà livrés — pas
besoin de les retoucher, ils héritent automatiquement du nouveau style.

Les offres affichées dans la section "Dans le registre en ce moment" sont
des **données d'exemple codées en dur** dans `HomeController` — le temps
qu'on branche le vrai CRUD `Offre` à l'étape suivante, qui remplacera ça
par une vraie requête sur les offres publiées.

### Tester

```bash
symfony server:start
```

Ouvrez `http://localhost:8000/` — vous devriez voir la page d'accueil
complète (hero, "comment ça marche", registre d'exemple, chiffres clés).

## Étape 10 — Design system aligné sur l'identité DAIP (mis à jour)

Suite à l'analyse de captures d'écran du vrai site
`1jeune1metier.daip.ci`, le design a été aligné sur leur identité
réelle :

- **Dégradé violet → bleu** (`#8258FF` → `#4A63F2`) pour le hero, les
  boutons principaux et les bandeaux d'appel à l'action
- **Orange** (`#F7941D`) pour le bouton "Publier une offre" (équivalent
  de leur bouton "Connexion")
- **Fond blanc/gris très clair** pour le contenu, **footer bleu marine
  très foncé** (`#10173A`)
- **Poppins** (titres) + **Inter** (texte courant), boutons et champs de
  formulaire en pilule/coins très arrondis, comme sur le site DAIP

Fichiers concernés (remplacent les versions précédentes) :

```
templates/base.html.twig
templates/home/index.html.twig
templates/security/login.html.twig
templates/registration/register.html.twig
```

## Étape 11 — CRUD des offres (nouveau)

Fichiers ajoutés :

```
src/Security/OffreVoter.php                        → src/Security/
src/Form/OffreType.php                              → src/Form/
src/Controller/Entreprise/OffreController.php       → src/Controller/Entreprise/
src/Controller/Daip/OffreController.php             → src/Controller/Daip/
templates/entreprise/offres/index.html.twig         → templates/entreprise/offres/
templates/entreprise/offres/form.html.twig          → templates/entreprise/offres/
templates/daip/offres/index.html.twig                → templates/daip/offres/
```

`OffreVoter` n'a rien à enregistrer manuellement : Symfony détecte
automatiquement les classes qui étendent `Voter` grâce à
`autoconfigure: true` (activé par défaut dans `services.yaml`).

### Ce que fait chaque contrôleur

- **`Entreprise\OffreController`** (préfixe `/entreprise/offres`) : liste,
  création, édition, suppression, et les deux transitions de statut
  (`publier`, `retirer`) — toutes protégées par `OffreVoter`, qui vérifie
  que l'offre appartient bien à l'entreprise connectée.
- **`Daip\OffreController`** (préfixe `/daip/offres`) : **une seule
  route**, en lecture seule, listant les offres de toutes les
  entreprises avec un filtre par statut. Il n'existe **aucune** route
  d'édition dans ce contrôleur : c'est une garantie architecturale que
  la DAIP ne peut techniquement rien modifier, pas seulement une
  question d'interface cachée.

### Tester

```bash
symfony console doctrine:schema:validate
symfony server:start
```

1. Connectez-vous avec un compte entreprise → `/entreprise/offres`,
   créez une offre (elle apparaît en `Brouillon`), publiez-la.
2. Connectez-vous avec le compte DAIP créé à l'étape 9 → `/daip/offres`,
   vérifiez que l'offre publiée apparaît, et qu'aucun bouton de
   modification n'est disponible.
3. Retournez sur `/` : la section "Dans le registre" affiche encore les
   3 offres d'exemple codées en dur — la prochaine amélioration possible
   est de les remplacer par une vraie requête sur les offres publiées.

## Étape 12 — Filtres publics sur la page d'accueil (nouveau)

Fichiers modifiés :

```
src/Repository/OffreRepository.php   → ajout de rechercherOffresPubliees()
src/Controller/HomeController.php    → lit les filtres depuis l'URL (?q=&ville=&secteur=&typeContrat=)
templates/home/index.html.twig       → barre de filtres + vraies offres (fini les exemples codés en dur)
```

La recherche filtre uniquement sur les offres au statut `publiee` — une
entreprise ne verra jamais ses brouillons apparaître publiquement, et les
filtres se combinent (mot-clé + ville + secteur + type de contrat en même
temps).

⚠️ Le lien "Voir le dossier complet" sur chaque carte pointe encore vers
`#` — il n'y a pas encore de page de détail d'offre publique. À prévoir
si vous voulez que le public consulte le détail complet d'une offre.

## Étape 13 — Page de détail publique d'une offre (nouveau)

Fichiers ajoutés/modifiés :

```
src/Controller/OffreController.php    → src/Controller/  (nouveau, route /offres/{id})
templates/offres/show.html.twig       → templates/offres/  (nouveau)
templates/home/index.html.twig        → le lien "Voir le dossier complet" pointe maintenant vers cette page
```

Protection via `OffreVoter::VIEW` : le grand public ne peut consulter que
les offres au statut `publiee`. Si quelqu'un tente d'accéder à l'URL
d'un brouillon ou d'une offre retirée sans être ni le propriétaire ni la
DAIP, Symfony renvoie une 403 automatiquement.

### Tester

```bash
symfony console doctrine:schema:validate
symfony server:start
```

Publiez une offre depuis votre compte entreprise, puis cliquez sur
"Voir le dossier complet" depuis la page d'accueil.

## Étape 14 — Tailwind CSS + animations au scroll (build de production)

Tout le front utilise **Tailwind CSS**, compilé via
`symfonycasts/tailwind-bundle` (intégré à AssetMapper, déjà présent
dans Symfony 7 `--webapp` — pas besoin de Node/npm), avec des
animations au scroll via **AOS** (Animate On Scroll, via CDN, léger et
sans étape de build nécessaire pour celui-ci).

Fichiers fournis :

```
assets/styles/app.css     → source Tailwind (@tailwind + @layer components)
tailwind.config.js        → couleurs/polices/dégradé de la marque DAIP Emploi
templates/base.html.twig  → référence le CSS compilé via {{ asset('styles/app.css') }}
```

### Installation chez vous

```bash
composer require symfonycasts/tailwind-bundle
```

Le bundle télécharge automatiquement le binaire Tailwind CLI (pas besoin
de Node.js). Copiez ensuite `assets/styles/app.css` et
`tailwind.config.js` fournis ici dans votre projet (ils remplacent ceux
générés par défaut si vous avez lancé `tailwind:init` avant).

Compilez le CSS :

```bash
php bin/console tailwind:build
```

Vous devriez voir un message de succès et un fichier généré (compilé)
récupéré automatiquement par AssetMapper via `{{ asset('styles/app.css') }}`.

### Pendant que vous développez

Le CSS compilé ne se met pas à jour tout seul quand vous modifiez les
classes Tailwind dans vos templates. Lancez, dans un terminal séparé,
laissé ouvert pendant que vous travaillez :

```bash
php bin/console tailwind:build --watch
```

### Tester

```bash
symfony server:start
```

Si la page s'affiche sans style (police par défaut, pas de couleurs),
c'est presque toujours signe que `tailwind:build` n'a pas tourné, ou que
le `--watch` n'est pas actif après une modification de template.

### Ce qui a changé par rapport à la version précédente (CDN)

Uniquement le `<head>` de `base.html.twig` : le `<script
src="https://cdn.tailwindcss.com">` et son `<style
type="text/tailwindcss">` ont été remplacés par un simple `<link
rel="stylesheet" href="{{ asset('styles/app.css') }}">`. Tous les autres
fichiers (`home/index.html.twig`, `login.html.twig`, etc.) restent
identiques — les classes Tailwind utilisées dans les templates ne
changent pas entre le mode CDN et le mode compilé.

### Animations

Chaque section importante a un attribut `data-aos="fade-up"` (ou
`fade-down`, `zoom-in`) avec des `data-aos-delay` échelonnés sur les
listes (offres, étapes) pour un effet de cascade au scroll. AOS
s'initialise une seule fois dans `base.html.twig`.

## Structure des entités livrées

- `User` — authentification (email, password, roles : ROLE_ENTREPRISE ou ROLE_DAIP)
- `Entreprise` — profil entreprise, lié 1-1 à `User`
- `Secteur` — catégories d'offres
- `Competence` — compétences, relation many-to-many avec `Offre`
- `Offre` — l'offre d'emploi elle-même (voir enums `StatutOffre`, `TypeContrat`).
  `StatutOffre` inclut : brouillon, publiee, retiree, expiree.

## Règle métier importante à implémenter avec un Voter

`OffreVoter` doit être simple mais stricte sur la propriété :

- `EDIT` / `DELETE` / `CHANGE_STATUT` : réservés à `ROLE_ENTREPRISE`, et
  uniquement si l'offre lui appartient (`offre.entreprise.user === user`
  connecté). Une entreprise ne doit jamais pouvoir agir sur l'offre d'une
  autre entreprise.
- `ROLE_DAIP` n'a accès à **aucune** de ces trois permissions, sur aucune
  offre — uniquement `VIEW`.

C'est ce qui garantit dans le code que "la DAIP ne modifie jamais rien" et
que "une entreprise ne touche jamais aux offres d'une autre entreprise".
