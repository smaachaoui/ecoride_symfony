# EcoRide - Plateforme de Covoiturage Écologique

![Symfony](https://img.shields.io/badge/Symfony-7.x-green)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple)
![License](https://img.shields.io/badge/License-MIT-yellow)

EcoRide est une plateforme de covoiturage écologique développée avec Symfony. Elle permet aux utilisateurs de proposer et rechercher des trajets en covoiturage, avec une attention particulière portée aux véhicules électriques.

## Table des matières

- [Fonctionnalités](#-fonctionnalités)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Lancement](#-lancement)
- [Structure du projet](#-structure-du-projet)
- [Identifiants de test](#-identifiants-de-test)

## Fonctionnalités

### Visiteurs
- Recherche de covoiturages par ville et date
- Filtres avancés (écologique, prix max, note chauffeur)
- Vue détaillée des trajets
- Inscription avec 20 crédits offerts

### Utilisateurs (Passagers)
- Participation aux covoiturages avec système de crédits
- Historique des trajets
- Système de notation et avis

### Utilisateurs (Chauffeurs)
- Gestion des véhicules
- Création de covoiturages
- Définition des préférences (fumeur, animaux...)
- Historique des trajets effectués

### Employés
- Validation/refus des avis
- Visualisation des covoiturages du jour
- Signalement de problèmes avec remboursement automatique

### Administrateurs
- Gestion des utilisateurs (suspension/réactivation)
- Création/suppression des comptes employés
- Statistiques de la plateforme
- Suivi des crédits gagnés

## Prérequis

- **PHP** 8.2 ou supérieur
- **Composer** 2.x
- **Node.js** 18.x ou supérieur
- **npm** ou **yarn**
- **MySQL** 8.0 ou **MariaDB** 10.6+
- **Symfony CLI** (recommandé)

### Extensions PHP requises
```
pdo_mysql, intl, mbstring, zip, gd, xml
```

## Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/votre-username/ecoride.git
cd ecoride
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Installer les dépendances JavaScript

```bash
npm install
```

### 4. Configurer l'environnement

Copier le fichier `.env` en `.env.local` :

```bash
cp .env .env.local
```

Modifier les variables dans `.env.local` :

```env
# Base de données
DATABASE_URL="mysql://utilisateur:motdepasse@127.0.0.1:3306/ecoride?serverVersion=8.0"

# Mailer (pour reset password)
MAILER_DSN=smtp://localhost:1025
```

### 5. Créer la base de données

```bash
# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate --no-interaction
```

### 6. Créer le dossier d'uploads

```bash
mkdir -p public/uploads/photos
chmod 775 public/uploads/photos
```

### 7. Compiler les assets

```bash
# Développement
npm run dev

# Ou en mode watch
npm run watch

# Production
npm run build
```

## Configuration

### Configuration du mailer (optionnel)

Pour tester les emails en local, j'ai utilisé Mailpit :

```bash
# Installation
sudo bash < <(curl -sL https://raw.githubusercontent.com/axllent/mailpit/develop/install.sh)

# Lancement
mailpit
```

Interface web accessible sur : http://localhost:8025

### Configuration des paramètres

Dans `config/services.yaml` :

```yaml
parameters:
    photos_directory: '%kernel.project_dir%/public/uploads/photos'
```

## Lancement

### Avec Symfony CLI (recommandé)

```bash
symfony serve
```

L'application sera accessible sur : https://127.0.0.1:8000

### Avec le serveur PHP intégré

```bash
php -S localhost:8000 -t public
```

### Compiler les assets en continu

```bash
npm run watch
```

## Structure du projet

```
ecoride/
├── assets/                 # Sources CSS/JS
│   ├── app.js             # JavaScript principal
│   └── styles/
│       └── app.css        # Styles personnalisés
├── config/                 # Configuration Symfony
│   ├── packages/
│   │   └── security.yaml  # Authentification & autorisation
│   └── services.yaml      # Services & paramètres
├── migrations/             # Migrations Doctrine
├── public/                 # Point d'entrée web
│   └── uploads/           # Fichiers uploadés
├── src/
│   ├── Controller/        # Contrôleurs
│   ├── Entity/            # Entités Doctrine
│   ├── Form/              # Types de formulaires
│   └── Repository/        # Repositories
├── templates/              # Templates Twig
│   ├── admin/             # Espace administrateur
│   ├── covoiturage/       # Pages covoiturage
│   ├── employe/           # Espace employé
│   ├── espace_utilisateur/# Espace utilisateur
│   ├── partials/          # Composants réutilisables
│   └── security/          # Pages auth
└── README.md
```

## Identifiants de test

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Administrateur | admin@ecoride.fr | Admin123! |
| Employé | employe@ecoride.fr | Employe123! |
| Chauffeur | chauffeur@test.fr | Chauffeur123! |
| Passager | passager@test.fr | Passager123! |

## Charte graphique

| Couleur | Code HEX | Utilisation |
|---------|----------|-------------|
| Vert foncé | #114B1E | Titres, boutons, footer |
| Vert moyen | #23953C | Liens actifs, hover |
| Vert clair | #C7FBD3 | Fonds, alertes succès |

**Police** : Poppins (Google Fonts)

## Technologies utilisées

- **Back-end** : Symfony 7, PHP 8.2, Doctrine ORM
- **Front-end** : Bootstrap 5, Twig, Webpack Encore
- **Base de données** : MySQL / MariaDB
- **Outils** : Composer, npm, Git

## Les commandes utiles

```bash
# Vider le cache
php bin/console cache:clear

# Lister les routes
php bin/console debug:router

# Créer une migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Exporter le schéma SQL
php bin/console doctrine:schema:create --dump-sql
```

## L'auteur

Développé dans le cadre du titre professionnel Développeur Web et Web Mobile.

## La licence

Ce projet est sous licence MIT.
