# Application de Traitement des Paiements

Une application sécurisée basée sur Symfony pour gérer les informations de paiement et le traitement des cartes de crédit avec chiffrement.

## Fonctionnalités

- Stockage des informations de carte de crédit avec chiffrement
- Détection du type de carte (Visa, Mastercard, Amex, Discover)
- Historique des paiements par utilisateur
- Chiffrement sécurisé utilisant la cryptographie Sodium

## Prérequis

- PHP 8.1+
- Symfony 7+
- MySQL 8.0+ / MariaDB 11.0+
- Composer
- Yarn

## Installation

```bash
# Cloner le dépôt
git clone https://github.com/Aiizon/paiement.git
cd paiement

# Installer les dépendances
composer install
yarn

# Créer la base de données
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Charger les données de test
php bin/console doctrine:fixtures:load
```

## Configuration

Copiez le fichier `.env` vers `.env.local` et ajustez les paramètres:

```bash
cp .env .env.local
```

Paramètres de configuration clés:
- `APP_ENV`: Définir à `prod` pour la production
- `DATABASE_URL`: Configurer votre connexion à la base de données
- `ENCRYPTION_KEY`: Clé encodée en Base64 pour chiffrer les données sensibles

## Utilisation

```bash
# Démarrer le serveur de développement
symfony server:start -d
```

Accédez à l'application à l'adresse `https://localhost:8000`

## Notes de Sécurité

- Les données des cartes de crédit sont chiffrées au repos
- L'application utilise le système de sécurité de Symfony pour l'authentification
- NE JAMAIS commit `.env.local` ou tout fichier contenant des identifiants réels

## Développement

```bash
# Compiler les assets
yarn dev

# Construire les assets
yarn build
```