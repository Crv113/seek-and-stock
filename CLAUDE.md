# CLAUDE.md

Ce fichier fournit des instructions à Claude Code (claude.ai/code) pour travailler sur ce dépôt.

## Commandes

```bash
composer install
php artisan migrate
php artisan test
./vendor/bin/pint        # Formateur de code — à lancer avant tout commit
php artisan db:seed --class=RoleSeeder
```

## Conventions non-obvieuses

### Auth
Deux middlewares distincts — ne jamais les mélanger sur une même route :
- `auth:sanctum` — endpoints utilisateurs (token Discord OAuth)
- `VerifyApiKey` — service-to-service (live-timing)

Les routes admin sont imbriquées sous `auth:sanctum` + `RoleMiddleware:admin`.

### Architecture
La logique métier est dans `app/Actions/`, pas dans les controllers. Les controllers reçoivent les Actions via injection de dépendances, appellent `handle()`, et retournent une Resource.

### Règles métier laptimes
- Toujours exclure les entrées anonymes : `player_guid` doit exister dans `users.guid`
- Les victoires ne comptent que sur les events terminés : `events.ending_date <= NOW()`
- Tie-break sur égalité de temps : `MIN(lap_times.id)` — le plus ancien l'emporte

### File d'attente
Ne jamais traiter les données de tour de manière synchrone — passer par `SeekAndStockJob` via Horizon.
