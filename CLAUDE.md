# CLAUDE.md

Ce fichier fournit des instructions à Claude Code (claude.ai/code) pour travailler sur ce dépôt.
C'est la partie backend du projet mxbtiming.com.

## Stack

Laravel 11, PHP 8.2, MySQL, Laravel Sanctum, Spatie Permission, Laravel Horizon.

## Commandes

```bash
composer install
php artisan migrate
php artisan test                  # PHPUnit
./vendor/bin/pint                 # Formateur de code — à lancer avant tout commit
php artisan key:generate
php artisan db:seed --class=RoleSeeder
```

## Architecture

### Auth — deux middlewares distincts

| Middleware     | Usage                                        |
| -------------- | -------------------------------------------- |
| `auth:sanctum` | Endpoints utilisateurs (token Discord OAuth) |
| `VerifyApiKey` | Service-to-service (utilisé par live-timing) |

Les routes admin sont imbriquées sous `auth:sanctum` + `RoleMiddleware:admin`. Ne jamais mélanger les deux types d'auth sur une même route.

### Actions (`app/Actions/`)

La logique métier est dans des classes Action, pas dans les contrôleurs. Les contrôleurs sont minces : ils résolvent les modèles, appellent l'Action et retournent une Resource.

| Action                        | Rôle                                                                      |
| ----------------------------- | ------------------------------------------------------------------------- |
| `GetEventResults`             | Meilleur tour par joueur par événement (utilisateurs inscrits uniquement) |
| `GetTrackResults`             | Meilleurs tours par track (classement global)                             |
| `GetUserBestLapTimes`         | Meilleur tour personnel par track                                         |
| `GetUsersFavoriteBikes`       | Moto la plus utilisée par utilisateur                                     |
| `GetUsersParticipationCounts` | Nombre d'événements auxquels un utilisateur a participé                   |
| `GetUsersVictoryCounts`       | Nombre de victoires par utilisateur                                       |

### Règles métier pour les requêtes de laptimes

Toujours filtrer `lap_times` pour n'inclure que les lignes dont `player_guid` existe dans `users.guid`. Les entrées anonymes du serveur de jeu doivent être exclues. Utiliser `whereExists` ou `whereIn` contre la table `users`.

Le comptage des victoires ne s'applique qu'aux événements **terminés** (`events.ending_date <= NOW()`). Ne jamais compter un événement en cours comme une victoire.

En cas d'égalité de temps : `MIN(lap_times.id)` — le tour enregistré le plus tôt l'emporte.

### Modèles

`User` est lié aux données du jeu via `guid` (renseigné manuellement par l'utilisateur après connexion Discord). `Event` a `starting_date` / `ending_date`. `LapTime` appartient à un événement et référence un joueur via `player_guid`.

### File d'attente

`SeekAndStockJob` traite les données de tour XML de manière asynchrone. Laravel Horizon gère la file. Ne jamais traiter les données de tour de manière synchrone.

## Variables d'environnement

`.env` Laravel standard — identifiants DB, Discord OAuth (`DISCORD_CLIENT_ID`, `DISCORD_CLIENT_SECRET`, `DISCORD_REDIRECT_URI`), `FRONTEND_URL`, `API_KEY` (pour le middleware VerifyApiKey).
