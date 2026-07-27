# JobConnect — Plateforme d'offres d'emploi (Symfony)

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
  les enregistre en brouillon ou les soumet, gère son propre dashboard
  (uniquement ses offres). Ne peut PAS valider ses propres offres.
- **DAIP** (`ROLE_DAIP`) — voit toutes les offres de toutes les entreprises,
  peut uniquement changer leur statut (valider / rejeter avec motif /
  retirer une offre publiée). Ne peut JAMAIS créer ni modifier le contenu
  d'une offre.

Le grand public consulte librement les offres publiées, sans compte.
Il n'y a pas de système de candidature dans ce périmètre.

## Structure des entités livrées

- `User` — authentification (email, password, roles : ROLE_ENTREPRISE ou ROLE_DAIP)
- `Entreprise` — profil entreprise, lié 1-1 à `User`
- `Secteur` — catégories d'offres
- `Competence` — compétences, relation many-to-many avec `Offre`
- `Offre` — l'offre d'emploi elle-même (voir enums `StatutOffre`, `TypeContrat`).
  `StatutOffre` inclut : brouillon, en_attente, publiee, rejetee (avec
  `motifRejet`), retiree, expiree.

## Règle métier importante à implémenter avec un Voter

`OffreVoter` doit distinguer deux permissions bien séparées :

- `EDIT` / `DELETE` : uniquement l'entreprise propriétaire, et seulement
  si l'offre est encore en `brouillon` (pas après soumission).
- `CHANGE_STATUT` : uniquement `ROLE_DAIP`, jamais l'entreprise elle-même.

C'est ce qui garantit dans le code que "la DAIP ne crée jamais d'offre" et
que "l'entreprise ne peut jamais s'auto-valider".
