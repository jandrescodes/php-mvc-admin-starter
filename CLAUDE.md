# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP MVC admin starter with authentication, user management, role management, and permission-based access control. Uses AdminLTE 3 UI, PDO/MySQL, and Composer with native PSR-4 autoloading.

> **Note for AI Agents:** See `docs/AI_SETUP.md` for details on how this project orchestrates AI skills and MCP servers. Custom skills for interacting with this repository should be placed in `.claude/skills/`.

## Setup

```bash
# 1. Install dependencies
composer install

# 2. Import database schema and seed data
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seeder.sql

# 3. Configure environment
cp .env.example .env
# Edit .env: DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL, TIMEZONE, SESSION_LIFETIME, REMEMBER_ME_LIFETIME

# 4. Create upload directory and set write permissions
mkdir -p public/uploads/users
cp public/img/user_default.jpg public/uploads/users/
chmod 777 public/uploads/users/
```

> **Note:** Apache runs as `daemon` on LAMPP. The uploads directory must be world-writable (`777`) or owned by the web server user, otherwise `move_uploaded_file()` silently fails.

> **Note:** `APP_URL` must include `/public` at the end (e.g. `http://localhost/ProyectoBase/public`). Without it the router cannot resolve routes and assets will not load. This is a common issue when running under XAMPP/WAMP on Windows, where no VirtualHost points directly to `public/`.

**Requirements:** PHP 8.2+ with PDO and GD extensions, Apache/Nginx, MariaDB/MySQL.

**Default credentials:** `admin@sistema.com` / `admin123`

**Seed dataset reference:** see `docs/SEEDING.md` for all seeded users, permission matrix, and rerun behavior.

**Local URL:** `http://localhost/php-mvc-admin-starter/`

**Current release tag:** `3.15.3`

## No Build Process

This is a pure PHP application. There are no npm or Makefile commands. Run `composer install` once after cloning to install dependencies into `vendor/`. Frontend assets (CSS/JS) are static files in `public/`.

## Testing

PHPUnit 11 is configured with two suites:

```bash
vendor/bin/phpunit                   # run all suites
vendor/bin/phpunit --testsuite=Unit  # unit tests only (no DB required)
vendor/bin/phpunit --testsuite=Integration  # integration tests (requires DB)
```

**Unit suite** (`tests/Unit/`) — covers `Auth`, `Router`, `helpers`, `ImageService`. No DB, no session start needed. Runs in < 2 s.

**Integration suite** (`tests/Integration/`) — covers `User` model, `Permission` model, `Role` model, and `Auth` cross-model flows (`refreshPermissionsIfStale`, `attemptRememberLogin`). Requires a separate test DB.

**Test DB setup (one time):**

```bash
# 1. Create the test database
mysql -u root -p -e "CREATE DATABASE php_mvc_admin_starter_test CHARACTER SET utf8;"

# 2. Copy and configure .env.testing
cp .env.testing.example .env.testing
# Edit .env.testing: set DB_HOST, DB_USER, DB_PASS to match your local MySQL
```

The `IntegrationTestCase` loads `database/schema.sql` + `tests/fixtures/sql/minimal_seed.sql` automatically on the first run, and wraps each test in a transaction that is rolled back on teardown — so the test DB stays clean between runs.

**Conventions:**

- Test classes live under `tests/Unit/` or `tests/Integration/`, mirroring the `app/` directory structure.
- Base classes: `tests/TestCase.php` (unit) and `tests/IntegrationTestCase.php` (integration).
- Image fixtures for `ImageService` tests are in `tests/fixtures/images/`.
- Auth unit tests manipulate `$_SESSION` directly — no `session_start()` call needed in unit tests.
- CI runs both suites automatically on every push/PR via `.github/workflows/tests.yml`.

## Architecture

### Request Flow

All HTTP requests are routed through `public/index.php` (Front Controller) via Apache rewriting. The `App\Core\Router` matches the URI and HTTP method against `routes/web.php`, runs registered middleware, then dispatches to the appropriate controller method.

Clean URL examples:

- `/` or `/dashboard` — dashboard
- `/login` — login page
- `/users/create` — create user form
- `/users` (POST) — store new user
- `/users/5/edit` — edit user

**Entry point:** `public/index.php` starts the session and loads `app/Config/config.php`. Config bootstraps `vendor/autoload.php` (Composer PSR-4 + `files` for helpers) and phpdotenv, then instantiates the router.

**Helpers:** `app/Core/helpers.php` defines only three global CSRF functions: `generateCSRFToken()`, `verifyCSRFToken()`, and `regenerateCSRFToken()`, plus the `env()` wrapper. Loaded automatically by Composer via `autoload.files`. All auth/session concerns live in `App\Core\Auth`.

### MVC Structure

```
app/
├── Config/       # Bootstrap: config.php, Connection.php (PDO singleton), phpdotenv init
├── Controllers/  # Feature controllers (flat — AuthController, UserController, PermissionController, RoleController, DashboardController, AuditLogController, ProfileController, PasswordResetController, InvitationController)
├── Core/         # Controller.php, Model.php, Router.php, Auth.php, AssetRegistry.php, ErrorHandler.php, helpers.php
├── Middleware/   # AuthMiddleware, GuestMiddleware, PermissionMiddleware
├── Models/       # App\Models; Traits/ holds UserAuthTrait, UserPasswordTrait, UserStatsTrait
└── Services/     # App\Services (ImageService, MailService, DashboardCache, LoginThrottleService, AuditLogger)
routes/           # web.php — all route definitions
database/         # schema.sql and seeder.sql
views/            # PHP templates; layouts/header.php pulls in all CSS/JS; sidebar.php handles navigation
public/           # Static assets (lib/, core/, plugins/, modules/) + index.php
vendor/           # Composer dependencies (not committed — run composer install)
```

### PSR-4 Autoloading via Composer

Composer resolves all `App\*` classes natively via the mapping `"App\\": "app/"` in `composer.json`. No custom autoloader exists. `app/Core/helpers.php` is loaded automatically via `autoload.files`. Run `composer dump-autoload -o` after adding new classes or changing the mapping.

### Database Connection

`app/Config/Connection.php` exposes a singleton `Connection::getInstance()` returning a configured PDO object. All queries use prepared statements.

### Auth Hub — `App\Core\Auth`

All authentication, session, remember-me, and permission cache concerns are centralised in the static class `App\Core\Auth`. No instantiation needed.

Key methods: `Auth::check()`, `Auth::id()`, `Auth::user()`, `Auth::isAdmin()`, `Auth::hasPermission(string $name)`, `Auth::login()`, `Auth::logout()`, `Auth::checkTimeout()`, `Auth::checkSecurity()`, `Auth::refreshPermissionsIfStale()`, `Auth::issueRememberCookie()`, `Auth::attemptRememberLogin()`.

See `docs/ACCESS_CONTROL.md` for the full method reference and flows.

### Permissions

`Auth::hasPermission(string $name)` is the standard check for menu/page/action gating. It reads from `$_SESSION['user_permissions']` — no DB query per call.

Administrators (users whose role has `is_system = 1`) always get `['*']` in their session cache. Non-admins get an array of permission name strings that is the **union** of their direct `user_permissions` assignments and the permissions inherited from their role via `role_permissions` — duplicates are removed with `array_unique`.

The permission cache is refreshed automatically by `Auth::refreshPermissionsIfStale()` — called by `AuthMiddleware` on every authenticated request — comparing `$_SESSION['permissions_ts']` against `users.permissions_updated_at`. Call `$userModel->updatePermissionsTimestamp($userId)` after any permission change so the affected user's cache is regenerated on their next page load. When role permissions change, call `updatePermissionsTimestamp()` for **every user of that role** so all their caches are invalidated.

**Middleware:** Route-level protection is declared in `routes/web.php` via the `middleware` key:

- `'auth'` — `AuthMiddleware`: attempts auto-login from remember-me cookie if no session, then validates timeout and anti-hijacking, then calls `Auth::refreshPermissionsIfStale()`.
- `'guest'` — `GuestMiddleware`: attempts auto-login from remember-me cookie before checking session; redirects authenticated users away from login/forgot-password pages.
- `'perm:NAME'` — `PermissionMiddleware`: calls `Auth::hasPermission($name)`; returns 403 view on failure.

### Error Handling

`App\Core\ErrorHandler` handles 403, 404, and 500 responses. It auto-detects AJAX requests (via `X-Requested-With` header) and returns JSON or the corresponding HTML view under `views/errors/`.

### AJAX Pattern

Controller methods that handle AJAX calls return JSON via `$this->jsonResponse($data)`. CSRF tokens are generated via `generateCSRFToken()` (global in `helpers.php`) and validated with `$this->csrfCheck()` (calls `verifyCSRFToken()` internally); call `regenerateCSRFToken()` after every successful POST. AJAX routes are declared in `routes/web.php` like any other route and protected by the same middleware.

When an AJAX action is followed by `location.reload()` in JS, set `$_SESSION['message']` and `$_SESSION['icon']` before calling `jsonResponse()` — `messages.php` will call `ToastUtils[icon](message)` on the reloaded page. Session values are interpolated into `<script>` blocks via `json_encode()` — never use `addslashes()` for JS context (does not escape `</script>`). For the welcome popup on first login, set `$_SESSION['welcome_user']` instead; `messages.php` calls `AlertUtils.welcome()` for it.

Additional reference: `docs/ACCESS_CONTROL.md` and `docs/AJAX_AND_MODULES.md`.

### Password Reset & Invitation — `App\Models\PasswordReset`

Dedicated model for token lifecycle. Tokens are stored **hashed** (SHA-256); the plain token only travels in the email link.

```php
const TTL_RESET      = '+1 hour';
const TTL_INVITATION = '+48 hours';

PasswordReset::create(int $userId, string $type): string      // invalidates previous live tokens of same type, returns plain token
PasswordReset::findValidByToken(string $token, string $type): array|false
PasswordReset::markUsed(int $id): bool
```

**Table:** `password_resets` — columns: `id`, `user_id`, `token_hash CHAR(64)`, `type ENUM('reset','invitation')`, `expires_at`, `used_at`, `created_at`.

**User invitation flow:**

1. Admin creates user with `invite=1` → `UserController::save()` sets `status = User::STATUS_PENDING` and a random unusable password hash.
2. `PasswordReset::create($userId, 'invitation')` issues a 48 h token; `MailService::sendInvitationEmail()` delivers the link.
3. User opens `/accept-invitation?token=...` → `InvitationController::showAcceptForm()` validates the token.
4. User submits password → `InvitationController::acceptInvitation()` calls `User::resetPassword()` + `User::activate()` + `PasswordReset::markUsed()`.
5. Admin can resend via `POST /users/{id}/resend-invitation` — `PasswordReset::create()` auto-invalidates the previous token.

**User status values (`users.status` tinyint):**

| Value | Constant                | Meaning                                                                                         |
| ----- | ----------------------- | ----------------------------------------------------------------------------------------------- |
| `0`   | `User::STATUS_INACTIVE` | Deactivated by admin — cannot log in                                                            |
| `1`   | `User::STATUS_ACTIVE`   | Normal active account                                                                           |
| `2`   | `User::STATUS_PENDING`  | Invitation sent, password not yet set — cannot log in, reset password, or auto-login via cookie |

### Dashboard Cache — `App\Services\DashboardCache`

Session-based cache for dashboard metrics with event-driven invalidation. Uses `$_SESSION['dashboard_cache']` as store; TTL is configured via `DASHBOARD_CACHE_TTL` in `.env` (default 300 s).

Key methods: `DashboardCache::get(string $key)`, `DashboardCache::put(string $key, array $data)`, `DashboardCache::remember(string $key, callable $loader)`, `DashboardCache::forget(string $key)`, `DashboardCache::flush()`.

**Invalidation contract:** any model method that mutates user, permission, or role data must call `DashboardCache::forget()` for the affected keys after a successful write. User mutations clear `user_stats`, `users_by_status`, `recent_users`, `users_by_month`. Permission mutations (including assign/revoke in `PermissionController`) clear `perm_stats`, `top_permissions`. Role mutations (`create`, `update`, `updateStatus`, `syncPermissions`) clear `role_stats`. `AuditLogger::log()` calls `DashboardCache::forget('audit_today')` after every successful insert so the dashboard "Events Today" counter stays current. Invitation mutations clear `pending_invitations`: call `DashboardCache::forget('pending_invitations')` in `UserController::save()` (invite branch), `UserController::resendInvitationAjax()`, and `InvitationController::acceptInvitation()`. Password-reset requests clear `resets_this_week`: call `DashboardCache::forget('resets_this_week')` in `PasswordResetController` after `$this->resets->create($userId, 'reset')`. Never read `$_SESSION['dashboard_cache']` directly — always go through `DashboardCache`.

**Passing data to JS:** views pass chart datasets as `data-*` attributes on canvas elements (e.g. `data-chart="<?= htmlspecialchars(json_encode(...)) ?>"`). Module JS reads `element.dataset.*` — no `<script>` blocks inside views.

### Audit Logger — `App\Services\AuditLogger`

Static service for appending entries to the `activity_logs` table. No instantiation needed.

```php
AuditLogger::log([
    'module'      => 'users',          // required — module slug
    'action'      => 'create',         // required — action verb
    'description' => 'User created: John Doe',  // optional
    'details'     => ['email' => 'john@...', 'role' => 'Editor'],  // optional array → JSON
]);
```

`AuditLogger::log()` automatically reads `actor_id` and `actor_label` from `Auth::id()` / `Auth::user()`, resolves the client IP from `$_SERVER`, and delegates to `ActivityLog::create()`. On success it calls `DashboardCache::forget('audit_today')`. Failures are silently swallowed — logging must never break the primary action.

**Call it after every state-changing action** in controllers:

- `AuthController` — after `login` and `logout`.
- `UserController` — after `create`, `update`, `delete`, `status change`, `unlock-login`, `invite`, `invite_resent`.
- `InvitationController` — after `invitation_accepted`.
- `PermissionController` — after `create`, `update`, `delete`, `assign`, `revoke`.
- `RoleController` — after `create`, `update`, `delete`, `sync_permissions`.

**Never log inside model methods** — logging belongs in the controller, after the model returns success, so failed writes never produce spurious log entries.

### Frontend Modules

Feature-specific JS lives in `public/js/modules/{feature}/`. Core utilities (DataTables setup, jQuery Validate config, SweetAlert2 wrappers) are in `public/js/core/`. All third-party libraries are already bundled in `public/js/lib/` and `public/css/lib/`.

**Loading order:** `footer.php` loads Bootstrap and AdminLTE, then conditional plugins (`$plugins`), then `public/js/core/ui-components.js` (auto-initializes Select2 and tooltips via `ComponentUtils.initAll()`), then `public/js/core/sweetalert-utils.js` (exposes `ToastUtils` and `AlertUtils` globally), then `$module_scripts`. Always register page-specific JS via `$module_scripts = ['feature/file']` at the top of the view — never use inline `<script>` blocks before `footer.php`, as Bootstrap plugins (e.g. `.tab()`) will not yet be available.

`public/css/core/ui-components.css` is loaded globally in `header.php` and requires no per-page declaration.

Similarly, register page-specific CSS via `$module_styles = ['feature/file']` at the top of the view.

**Conditional plugin loading:** Third-party plugins (DataTables, Select2, jQuery Validate) are loaded on demand via `$plugins` passed to `Controller::render()`. Asset maps are centralized in `App\Core\AssetRegistry` and consumed by layouts. Available plugins: `datatables`, `datatables-export`, `select2`, `validate`, `chart`. Example (inside a controller method):

```php
$this->render('users/index', $data, ['datatables', 'datatables-export'], ['users/index-users']);
```

**Form validation:** Pass `['select2', 'validate']` as the plugins argument. `public/js/core/common-validate.js` (loaded automatically with `validate`) configures jQuery Validate globally for Bootstrap 4 — `errorPlacement` inside `.form-group`, `highlight`/`unhighlight`, `onkeyup: false`. Each module calls `$('#form').validate({ rules, messages, submitHandler })` with its own rules. For uniqueness checks against the DB, use `remote` rules pointing to `/users/check-email` or `/users/check-document`.

**Auth standalone pages:** `views/auth/*.php` do not use `layouts/footer.php`, so they must include `sweetalert-utils.js` and the validation assets manually (`jquery.validate.min.js`, `additional-methods.min.js`, `common-validate.js`) before loading their module script. Keep auth input markup as `form-group > input-group` so `.invalid-feedback` placement from `common-validate.js` renders correctly. Each auth page also loads `dark-mode.css`, `login-dark.css`, and `theme-toggle.js` manually — add these to any new standalone auth page.

**Dark mode:** Theme preference is stored in `localStorage` (key `'theme'`, values `'light'` | `'dark'`); falls back to `prefers-color-scheme` on first visit. The anti-FOUC IIFE in `views/layouts/header.php` (and in each auth standalone page) applies `html.dark-mode` before any CSS loads. Toggle JS lives in `public/js/modules/profile/theme-toggle.js` (loaded globally from `footer.php`). CSS overrides are split into `public/css/core/dark-mode.css` (global panel) and `public/css/modules/login/login-dark.css` (auth pages). No DB column, no AJAX endpoint, no `AuditLogger` call — theme changes are not auditable business actions.

## Coding Conventions

- **Namespaces:** use `App\Controllers\*`, `App\Models\*`, `App\Services\*`, and `App\Core\*` consistently.
- **Auth checks:** use `Auth::check()`, `Auth::hasPermission()`, `Auth::id()`, `Auth::user()` — never read `$_SESSION` directly for auth state outside of `App\Core\Auth`.
- **CSRF:** Generate token with `generateCSRFToken()`, validate via `$this->csrfCheck()` (controller helper that calls `verifyCSRFToken()` and returns JSON 403 for AJAX or redirects for regular POSTs). Call `regenerateCSRFToken()` after every successful POST — including failure paths on sensitive endpoints (e.g. password change). All three functions live in `app/Core/helpers.php`. Destructive actions that are not AJAX (including logout) must be `POST` routes with CSRF validation, never `GET`.
- **Remember-me invalidation:** Call `$userModel->clearRememberToken($userId)` (via `Auth::clearRememberCookie()`) whenever a password is changed, regardless of who triggered the change (user self-service, admin edit, invitation acceptance, or forgot-password reset). `User::updatePassword()` and `UserPasswordTrait::resetPassword()` both call `clearRememberToken()` internally — never bypass either with a raw SQL UPDATE on the `password` column.
- **Input sanitization:** Use `trim()` at the model layer (`trimInput()`). Apply `htmlspecialchars()` exclusively at the view layer on all output — never in the model or before storing in the DB.
- **Passwords:** Always `password_hash($pass, PASSWORD_DEFAULT)` / `password_verify()`.
- **Images:** Route all upload/resize/delete through `ImageService`. MIME type is validated server-side via `(new \finfo(FILEINFO_MIME_TYPE))->file($tmp_name)` — never use `$_FILES['type']` (client-controlled). Extension whitelist: `jpg`, `jpeg`, `png`, `gif`, `webp`. `public/uploads/users/.htaccess` blocks PHP execution.
- **JS:** ES6+ with JSDoc comments. Use `ToastUtils` and `AlertUtils` from `public/js/core/sweetalert-utils.js` for all SweetAlert2 interactions — never call `Swal.fire()` directly in module scripts. Use `DataTables` for lists, `Select2` for dropdowns.
- **Select2 auto-init:** `ComponentUtils.initAll()` (called automatically on `DOMContentLoaded` by `ui-components.js`) initializes every `.select2` element on the page. Do not call `initializeSelect2()` manually in module scripts unless extra options are needed.
- **Select2 in modals:** When a Select2 element lives inside a Bootstrap modal, call `initializeSelect2('#selectId', { dropdownParent: $('#modalId') })` explicitly. `ComponentUtils.initAll()` cannot apply `dropdownParent` globally, so modal selects need targeted initialization. Without `dropdownParent`, Bootstrap's focus trap closes the dropdown immediately. Populate options from PHP `<option>` elements (server-side) rather than AJAX to avoid re-initialization conflicts.
- **AdminLTE cards:** `card-outline card-{color}` classes go on the outer `div.card`, **never** on `div.card-header`. Adding them to the header instead of the card silently breaks the colored left border style.
- **Models — fat model / thin controller:** Validation logic, data formatting, and cache invalidation belong in the model, not in the controller. Controllers read POST/GET, call one or a few model methods, set session flash messages, and redirect or return JSON — nothing more.
- **Model base class:** `App\Core\Model` provides generic CRUD (`find`, `all`, `insert`, `update`, `delete`, `query`, `getLastInsertId`, `trimInput`). Concrete models extend it, set `protected $table = 'table_name'`, and override only the methods that require JOINs or custom field handling. Never duplicate `getLastInsertId()` or `trimInput()` in a concrete model.
- **Model traits:** When a model grows beyond ~400 lines, split concerns into PHP traits under `app/Models/Traits/`. Traits share `$this` with the model, so they access `$this->connection` and `$this->table` directly — no extra parameters needed. Group by responsibility (auth queries, password/token lifecycle, statistics), not by arbitrary size.
- **Commits:** Follow Conventional Commits (`feat:`, `fix:`, `docs:`, etc.).
