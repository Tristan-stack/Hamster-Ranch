# Hamster Ranch - API REST

API REST pour un jeu d'élevage de hamsters développée avec Symfony 7.3.

## Prérequis

- PHP 8.2+
- Composer
- MySQL/MariaDB
- OpenSSL

## Installation

### 1. Configurer l'environnement

```bash
cp .env.example .env.local
```

### 2. Installer les dépendances

Copiez le fichier `.env.example` vers `.env.local` :

```bash
composer install
```

Modifiez `.env.local` avec vos paramètres (base de données, passphrase JWT, etc.)

### 3. Générer les clés JWT

```bash
mkdir -p config/jwt
```

```bash
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
```

```bash
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

**Important** : Utilisez la même passphrase dans `.env.local` que celle utilisée lors de la génération de la clé privée.

### 4. Créer la base de données

```bash
php bin/console doctrine:database:create
```

### 5. Exécuter les migrations

```bash
php bin/console doctrine:migrations:migrate
```

### 6. Charger les données de test

```bash
php bin/console doctrine:fixtures:load
```

### 7. Lancer le serveur

```bash
symfony server:start
```

Ou avec PHP intégré :

```bash
php -S localhost:8000 -t public
```

## Utilisation

### Authentification

1. **Créer un compte** : `POST /api/register`
```json
{
  "email": "user@example.com",
  "password": "motdepasse"
}
```

2. **Se connecter** : `POST /api/login_check`
```json
{
  "email": "user@example.com",
  "password": "motdepasse"
}
```
Retourne un token JWT à utiliser dans le header : `Authorization: Bearer VOTRE_TOKEN`

### Routes principales

- `GET /api/user` - Informations de l'utilisateur connecté
- `GET /api/hamsters` - Liste des hamsters de l'utilisateur
- `GET /api/hamsters/{id}` - Détails d'un hamster
- `POST /api/hamsters/reproduce` - Reproduction (body: `{"idHamster1": 1, "idHamster2": 2}`)
- `POST /api/hamsters/{id}/feed` - Nourrir un hamster (coût = 100 - faim actuelle)
- `POST /api/hamsters/{id}/sell` - Vendre un hamster (300 gold)
- `POST /api/hamster/sleep/{nbDays}` - Faire vieillir tous les hamsters
- `PUT /api/hamsters/{id}/rename` - Renommer (body: `{"name": "NouveauNom"}`)

### Données de test

Après `doctrine:fixtures:load` :
- 10 utilisateurs créés (premier = admin)
- 4 hamsters par utilisateur (2 mâles, 2 femelles)
- Mot de passe par défaut : `password`
- 500 gold par utilisateur

## Notes

- À chaque transaction réussie (feed, sell, reproduce), tous les hamsters vieillissent de 5 jours et perdent 5 points de faim
- Un hamster naît avec 100 de faim et 0 d'âge
- Un hamster se vend 300 gold

