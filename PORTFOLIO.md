# Engineering guide

Laravel authentication starter exploring Sanctum bearer tokens, role-based access and TOTP two-factor enrollment.

## Code tour

- [routes/api.php](routes/api.php)
- [app/Http/Controllers/Api/AuthController.php](app/Http/Controllers/Api/AuthController.php)
- [app/Http/Controllers/Api/TwoFactorController.php](app/Http/Controllers/Api/TwoFactorController.php)
- [tests](tests)

## Scope and verification

Authentication development example using Sanctum opaque bearer tokens. Email verification completion, refresh rotation and full recovery/2FA lifecycle hardening remain.

[Current CI runs](https://github.com/umairjutt/auth-jwt-rbac-laravel/actions). A workflow definition or build result does not establish that all runtime scenarios pass. The README contains development commands; unfinished setup and behavior need completion before production deployment.

## Maintainer

[Umair Jutt](https://github.com/umairjutt). Frameworks and dependencies retain their own ownership and licensing.
