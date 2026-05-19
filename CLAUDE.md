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

- **Validation** : toujours via Form Request (`php artisan make:request`) — ne jamais utiliser `Validator::make()` directement dans un controller
- **Controllers** : fins — reçoivent une Action injectée, appellent `handle()`, retournent une Resource. Aucune logique SQL ou métier
- **Resources** : toute transformation de données vers JSON passe par une Resource (`app/Http/Resources/`)
- **Pas de `dd()` / `dump()` / `var_dump()`** dans le code rendu

### Règles métier laptimes

- Toujours exclure les entrées anonymes : `player_guid` doit exister dans `users.guid`
- Les victoires ne comptent que sur les events terminés : `events.ending_date <= NOW()`
- Tie-break sur égalité de temps : `MIN(lap_times.id)` — le plus ancien l'emporte

### Tests

- Un test Feature par endpoint dans `tests/Feature/`
- Utiliser `RefreshDatabase` + factories — pas de fixtures statiques
- Couvrir : cas nominal, 401, 403, 422, et règles métier critiques (filtrage anonymous, events terminés)

### Autres informations

- Les laptimes sont reçus via la route `POST /api/laptimes` depuis live-timing (plus via XmlDataService)
- Ne jamais importer ou suggérer l'utilisation de `anthropic`, `openai` ou tout SDK IA/tiers payant dans le code applicatif
