# Hamster Ranch - API REST

API REST pour un jeu d'élevage de hamsters développée avec Symfony 7.3.

## Prérequis

- PHP 8.2+
- Composer
- MySQL/MariaDB
- OpenSSL

## Installation

```bash
# 1. Installer les dépendances
composer install

# 2. Configurer la base de données dans .env.local
DATABASE_URL="mysql://root:password@127.0.0.1:3306/hamsterranch?serverVersion=8.0.32&charset=utf8mb4"

# 3. Générer les clés JWT
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout

# Ajouter dans .env.local :
JWT_PASSPHRASE=votre_passphrase_ici
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem

# 4. Créer la base de données et charger les données
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

# 5. Lancer le serveur
symfony server:start
# ou
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

