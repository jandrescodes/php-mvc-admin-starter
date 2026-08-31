# Testing

This project uses **PHPUnit 11** with two independent suites.

---

## Quick start

```bash
# All suites
vendor/bin/phpunit

# Unit only (no DB, ~2 s)
vendor/bin/phpunit --testsuite=Unit

# Integration only (requires test DB)
vendor/bin/phpunit --testsuite=Integration
```

Or via Composer scripts:

```bash
composer test
composer test:unit
composer test:integration
```

---

## Test DB setup (one time)

```bash
# 1. Create the database
mysql -u root -p -e "CREATE DATABASE php_mvc_admin_starter_test CHARACTER SET utf8;"

# 2. Configure credentials
cp .env.testing.example .env.testing
# Edit .env.testing: set DB_HOST, DB_USER, DB_PASS
```

The `IntegrationTestCase` loads `database/schema.sql` and `tests/fixtures/sql/minimal_seed.sql`
automatically on the first run of the Integration suite. No manual schema import needed.

---

## Suites

### Unit (`tests/Unit/`)

No DB, no HTTP context. Each test class maps to a production class:

| Test class                                   | Production class                  |
| -------------------------------------------- | --------------------------------- |
| `tests/Unit/Core/HelpersTest.php`            | `app/Core/helpers.php`            |
| `tests/Unit/Core/AuthTest.php`               | `app/Core/Auth.php`               |
| `tests/Unit/Core/RouterTest.php`             | `app/Core/Router.php`             |
| `tests/Unit/Services/ImageServiceTest.php`   | `app/Services/ImageService.php`   |
| `tests/Unit/Services/DashboardCacheTest.php` | `app/Services/DashboardCache.php` |

### Integration (`tests/Integration/`)

Requires a live MySQL connection. Each test runs inside a transaction that is rolled back on
teardown, so the DB is always clean.

| Test class                                                      | What it covers                                                                                         |
| --------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| `tests/Integration/Models/UserTest.php`                         | `User` CRUD (including `role_id`), remember-me token, permissions timestamp, `getAll` JOIN             |
| `tests/Integration/Models/PermissionTest.php`                   | `Permission` read, assign, revoke, sync                                                                |
| `tests/Integration/Models/RoleTest.php`                         | `Role` CRUD, status toggle, user count, statistics                                                     |
| `tests/Integration/Models/RolePermissionTest.php`               | `syncPermissions`, `getAssignedPermissionIds`, `getPermissionNames`, `getUserIdsByRole`                |
| `tests/Integration/Core/AuthIntegrationTest.php`                | `refreshPermissionsIfStale` (including UNION merge and dedup), `attemptRememberLogin`                  |
| `tests/Integration/Models/UserDashboardTest.php`                | `getUsersByStatus`, `getUsersByMonth`                                                                  |
| `tests/Integration/Models/UserStatsTest.php`                    | `getStatistics` and `getUsersByStatus` include `pending` (status=2) count                             |
| `tests/Integration/Models/PermissionDashboardTest.php`          | `getTopAssigned`                                                                                       |
| `tests/Integration/Services/DashboardCacheInvalidationTest.php` | Cache invalidation after User/Permission/Role mutations                                                |
| `tests/Integration/Auth/LoginThrottleTest.php`                  | `recordFailure`, `getLockStatus`, `clearAttempts`, `unlock`, `LoginThrottleService`, `formatRemaining` |
| `tests/Integration/Models/PasswordResetTest.php`                | `PasswordReset::create`, `findValidByToken`, `markUsed`, TTL, hash storage, invalidation, `getPendingInvitationsCount`, `getResetRequestsThisWeek` |
| `tests/Integration/Auth/PasswordResetFlowTest.php`              | Full reset flow: token creation, password change, used/expired token rejection, pending user guard      |
| `tests/Integration/Auth/PendingUserBlockTest.php`               | `User::STATUS_*` constants, pending login block, pending user cannot request reset                     |
| `tests/Integration/Models/InvitationCreateTest.php`             | Invited user status=pending, unusable password, 48 h token, normal user regression, `validateData`    |
| `tests/Integration/Auth/AcceptInvitationTest.php`               | Accept flow: activates user, marks token used, reused/expired token rejection, type mismatch, cross-flow |
| `tests/Integration/Auth/ResendInvitationTest.php`               | Resend invalidates previous token, new 48 h token, active user is rejected                             |

---

## Infrastructure

### Base classes

**`tests/TestCase.php`** — used by Unit tests:

- Resets `$_SESSION`, `$_POST`, `$_GET`, `$_COOKIE` before each test.
- Snapshots and restores `$_SERVER` in teardown.
- Provides `invokePrivate($object, $method, $args)` for testing protected/private methods via reflection.

**`tests/IntegrationTestCase.php`** — used by Integration tests:

- Loads `.env.testing` with phpdotenv before the first test.
- Resets the `Connection` singleton via reflection so it picks up the test DB credentials.
- Loads schema + seed once per suite run (`setUpBeforeClass`).
- Wraps each test in `beginTransaction` / `rollBack` by default.
- Set `protected bool $useTransactions = false` in a subclass when the SUT manages its own
  transaction (e.g. `Role::syncPermissions`, `Permission::syncForUser`); call `self::reloadSeed()` in `setUp/tearDown` instead.

### Fixtures

- **`tests/fixtures/images/`** — `sample.jpg` (50×50 JPEG), `sample.png` (80×40 PNG with alpha),
  `corrupt.txt` (non-image file). Used by `ImageServiceTest`.
- **`tests/fixtures/sql/minimal_seed.sql`** — inserts in dependency order (roles → permissions → users → assignments):
  - 3 roles: `Administrator` (is_system=1, active), `Editor` (active), `Auditor` (inactive)
  - 2 permissions: `users`, `permissions`
  - 1 admin user (role = Administrator), 1 normal user (role = Editor, status=active)
  - 1 direct permission assignment (user 2 → `users`)
  - 1 role permission assignment (Editor → `users`)
  - `TRUNCATE TABLE password_resets` — always clean between runs

---

## Conventions

- **Test method names:** `test_[method]_[condition]_[expected result]`
  — e.g. `test_check_timeout_returns_false_and_destroys_when_exceeded`
- **No mocking PDO** — Integration tests use a real DB connection.
- **No mocking `move_uploaded_file()`** — `ImageService::processImage()` is tested via its
  validation path (MIME, size, error code); `resizeImage()` and `deleteImage()` use real files in `/tmp`.
- **`$_SESSION` in Unit tests** — `Auth` reads/writes `$_SESSION` as a plain array in CLI without
  `session_start()`. Start a real session only when the SUT calls `session_destroy()` (e.g. `AuthTest`).
- **`set_error_handler()`** — always paired with `restore_error_handler()` immediately after to avoid
  PHPUnit risky-test warnings.
- **Do not test controllers** — they are covered by manual browser testing.

---

## CI

`.github/workflows/tests.yml` runs three independent jobs:

| Job           | What it runs                                  | DB  |
| ------------- | --------------------------------------------- | --- |
| `quality`     | `pint --test` + `phpstan analyse --no-progress` | no  |
| `unit`        | `phpunit --testsuite=Unit`                    | no  |
| `integration` | `phpunit --testsuite=Integration`             | yes |

The `integration` job spins up a MySQL 8.0 service container and generates `.env.testing` at runtime
— no secrets are committed.

**Triggers:** `pull_request` against any branch, and `push` to `main` or tags only. Feature branches
are validated through the PR event, not through their own pushes (avoids the duplicate push+PR run).
`paths-ignore` skips runs when only docs or static assets (`public/css`/`js`/`img`, `*.md`, `LICENSE`,
`.gitignore`) changed. A `concurrency` group cancels superseded runs on the same ref.

## Static analysis & code style

Both are `require-dev` dependencies and run in the `quality` CI job:

```bash
composer lint        # pint --test   — PSR-12 check, no changes
composer lint:fix    # pint          — apply fixes
composer stan        # phpstan analyse (level 5)
composer check       # lint + stan + full phpunit
```

- **Pint** (`pint.json`) — `psr12` preset, excludes `views/` (HTML-heavy templates). Covers `app/`, `tests/`, `routes/`, `public/`.
- **PHPStan** (`phpstan.neon`) — level 5, analyses `app/` + `routes/`, excludes `app/Config/config.php`.
  Bootstrap `define()` constants (`URL`, `APP_VERSION`, `APP_BASE_PATH`) are declared in
  `phpstan-constants.php` (`scanFiles`) and listed under `dynamicConstantNames`. Add any new bootstrap
  constant there too. Do not suppress errors with `@phpstan-ignore` or a baseline — fix the cause.
