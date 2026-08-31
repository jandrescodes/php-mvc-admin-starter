# AGENTS.md

Operational reference for any AI agent (and humans) working on this repo. **Single source of truth**
for setup, architecture, and every coding convention. `CLAUDE.md` imports this file. Deep-dive
material lives in `docs/`.

## Project

PHP MVC admin starter — authentication, user / role / permission management, permission-based access
control. AdminLTE 3, PDO/MySQL, Composer native PSR-4 autoloading. Pure PHP, **no build process**
(no npm, no Makefile — frontend assets are static files in `public/`). Current release tag: `3.17.0`.

## Setup

```bash
composer install
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seeder.sql
cp .env.example .env      # set DB_*, APP_URL, TIMEZONE, SESSION_LIFETIME, REMEMBER_ME_LIFETIME
mkdir -p public/uploads/users
cp public/img/user_default.jpg public/uploads/users/
chmod 777 public/uploads/users/
```

- **PHP 8.2+** with PDO + GD extensions. Apache/Nginx, MariaDB/MySQL.
- `APP_URL` **must end with `/public`** (e.g. `http://localhost/php-mvc-admin-starter/public`) — without it the router and assets break. Common issue on XAMPP/WAMP with no VirtualHost pointing at `public/`.
- Apache runs as `daemon` on LAMPP; `public/uploads/users/` must be `777` (or web-server-owned) or `move_uploaded_file()` silently fails.
- `public/uploads/users/.htaccess` blocks PHP execution — never remove it.
- Default login: `admin@sistema.com` / `admin123`. Local URL: `http://localhost/php-mvc-admin-starter/`.
- Seed dataset (users, permission matrix, rerun behavior): `docs/SEEDING.md`.

## Commands

```bash
composer dump-autoload -o      # after adding any class or changing the PSR-4 map
composer test                  # phpunit — all suites
composer test:unit             # --testsuite=Unit — no DB, ~2s
composer test:integration      # --testsuite=Integration — needs .env.testing + test DB
composer lint                  # pint --test  (PSR-12 check, no changes; excludes views/)
composer lint:fix              # pint         (apply style fixes)
composer stan                  # phpstan analyse  (level 5)
composer check                 # lint + stan + all suites — the CI gate; run before every commit
```

## Architecture

- **Entry point:** `public/index.php` starts the session and loads `app/Config/config.php`, which bootstraps `vendor/autoload.php` (PSR-4 + `files` for helpers) + phpdotenv, then instantiates `App\Core\Router`. Apache rewrites all requests to `index.php` via `public/.htaccess`.
- **Routes:** `routes/web.php` — array of `['method', 'path', 'controller' => 'Name@method', 'middleware' => []]`. Params use `(\d+)`, passed positionally to the controller method. Router auto-appends the `Controller` suffix. Clean URLs: `/`, `/login`, `/users`, `/users/create`, `/users/5/edit`, `/users` (POST).
- **MVC layout:**
    ```
    app/Config/       config.php, Connection.php (PDO singleton), phpdotenv init
    app/Controllers/  flat — Auth, User, Permission, Role, Dashboard, AuditLog, Profile, PasswordReset, Invitation
    app/Core/         Controller, Model, Router, Auth, AssetRegistry, ErrorHandler, helpers.php
    app/Middleware/   AuthMiddleware, GuestMiddleware, PermissionMiddleware
    app/Models/       App\Models; Traits/ = UserAuthTrait, UserPasswordTrait, UserStatsTrait
    app/Services/     ImageService, MailService, DashboardCache, LoginThrottleService, AuditLogger
    routes/web.php · database/ (schema.sql, seeder.sql) · views/ · public/ · vendor/ (not committed)
    ```
- **Autoloading:** Composer PSR-4 `"App\\": "app/"`, no custom autoloader. `app/Core/helpers.php` loads via `autoload.files` — it defines only `generateCSRFToken()`, `verifyCSRFToken()`, `regenerateCSRFToken()`, and the `env()` wrapper.
- **DB:** `Connection::getInstance()` singleton → configured PDO. All queries use prepared statements.
- **Auth hub:** `App\Core\Auth` — static, never instantiate. `check()`, `id()`, `user()`, `isAdmin()`, `hasPermission($name)`, `login()`, `logout()`, `checkTimeout()`, `checkSecurity()`, `refreshPermissionsIfStale()`, `issueRememberCookie()`, `attemptRememberLogin()`. Full method reference + flows: `docs/ACCESS_CONTROL.md`.
- **Middleware** (declared per route in `routes/web.php`): `auth` — remember-me auto-login → timeout + anti-hijacking → `refreshPermissionsIfStale()`; `guest` — remember-me auto-login, then redirects authenticated users away from login/forgot pages; `perm:NAME` — `hasPermission()` → 403 view on failure.
- **Permissions:** `hasPermission()` reads `$_SESSION['user_permissions']` — zero DB per call. Admins (role `is_system = 1`) get `['*']`. Non-admins get an `array_unique` **union** of their direct `user_permissions` and the permissions inherited from their role via `role_permissions`. The cache is refreshed by `refreshPermissionsIfStale()` (called by `AuthMiddleware` on every authed request) comparing `$_SESSION['permissions_ts']` against `users.permissions_updated_at`.
- **Error handling:** `App\Core\ErrorHandler` handles 403/404/500, auto-detects AJAX (`X-Requested-With`) → JSON or `views/errors/*.php`. Each error view sets only `$code`, `$tone` (`'danger'`|`'warning'`), `$heading`, `$message`, then `require`s `views/layouts/error.php` (owns the full standalone document). Styling: `public/css/modules/errors/errors.css` + `errors-dark.css` (reuses `--dm-*`). `[data-tone]` on `<body>` drives the `.error-code` color — never hardcode it per view.
- **Static analysis:** PHPStan `phpstan.neon`, **level 5** — analyses `app/` + `routes/`, excludes `app/Config/config.php`. Bootstrap `define()` constants (`URL`, `APP_VERSION`, `APP_BASE_PATH`) are declared in `phpstan-constants.php` (`scanFiles`) and listed under `dynamicConstantNames` so PHPStan does not narrow them to literal values — add any new bootstrap constant there too.
- **CI:** `.github/workflows/tests.yml` — jobs `quality` (pint `--test` + phpstan) / `unit` / `integration` (MySQL 8.0 service container, `.env.testing` generated at runtime, no secrets committed). Runs on `pull_request` (any branch) and `push` to `main` or tags only — feature branches are covered by the PR event. `paths-ignore` skips docs/static-asset-only changes; a `concurrency` group cancels superseded runs on the same ref. `actions/checkout@v5`. Dependabot (`.github/dependabot.yml`) opens weekly update PRs for Composer and GitHub Actions.

## Critical conventions (deviation breaks things)

### Backend

- **Namespaces:** `App\Controllers\*`, `App\Models\*`, `App\Services\*`, `App\Core\*` consistently.
- **Auth state:** only through `Auth::check()` / `hasPermission()` / `id()` / `user()` — never read `$_SESSION` directly for auth state outside `App\Core\Auth`.
- **CSRF:** generate with `generateCSRFToken()`, validate with `$this->csrfCheck()` (returns JSON 403 for AJAX, redirects otherwise). Call `regenerateCSRFToken()` after **every** successful POST **and** on the failure paths of sensitive endpoints (e.g. password change). Destructive non-AJAX actions, logout included, must be POST routes with CSRF — never GET.
- **Remember-me invalidation:** call `$userModel->clearRememberToken($userId)` (via `Auth::clearRememberCookie()`) on every password change, no matter who triggered it (self-service, admin edit, invitation acceptance, forgot-password). `User::updatePassword()` and `UserPasswordTrait::resetPassword()` do this internally — never bypass either with a raw SQL `UPDATE` on the `password` column.
- **Input sanitization:** `trim()` at the model layer (`trimInput()`). `htmlspecialchars()` **only** at the view layer on output — never in the model or before storing in the DB.
- **Passwords:** always `password_hash($pass, PASSWORD_DEFAULT)` / `password_verify()`. Minimum 8 characters.
- **Images:** route every upload / resize / delete through `ImageService`. MIME type is validated server-side via `(new \finfo(FILEINFO_MIME_TYPE))->file($tmp_name)` — never `$_FILES['type']` (client-controlled). Extension whitelist: `jpg`, `jpeg`, `png`, `gif`, `webp`.
- **Fat model / thin controller:** validation, data formatting, and cache invalidation live in the model. Controllers read POST/GET, call one or a few model methods, set session flash messages, and redirect or return JSON — nothing more.
- **Model base class:** extend `App\Core\Model`, set `protected $table = '…'`. It provides `find`, `all`, `insert`, `update`, `delete`, `query`, `getLastInsertId`, `trimInput` — never redeclare those in a concrete model. Override only methods needing JOINs or custom field handling.
- **Model traits:** past ~400 lines, split concerns into `app/Models/Traits/` by responsibility (auth queries, password/token lifecycle, statistics). Traits share `$this` — they use `$this->connection` / `$this->table` directly.
- **Commits:** Conventional Commits (`feat:`, `fix:`, `docs:`, `style:`, `chore:`, …). Version tags carry **no `v` prefix** (`3.17.0`, not `v3.17.0`).
- **Code style:** must pass `composer lint` (Pint, `psr12` preset — `pint.json`, excludes `views/`) and `composer stan` (PHPStan level 5). Run `composer lint:fix` to auto-format. Never suppress a PHPStan error with `@phpstan-ignore` or a baseline — fix the underlying cause.

### Cache invalidation contracts

- **Permission cache:** after any permission or role change call `$userModel->updatePermissionsTimestamp($userId)` so the affected user's session cache regenerates on their next page load. When **role** permissions change, do this for **every user of that role**.
- **Dashboard cache** — `App\Services\DashboardCache`, backed by `$_SESSION['dashboard_cache']`, TTL from `DASHBOARD_CACHE_TTL` (default 300 s). Never read the session key directly; go through `DashboardCache::get/put/remember/forget/flush`. After a successful write, `DashboardCache::forget()` the affected keys:
    - user mutations → `user_stats`, `users_by_status`, `recent_users`, `users_by_month`
    - permission mutations (incl. assign/revoke in `PermissionController`) → `perm_stats`, `top_permissions`
    - role mutations (`create`, `update`, `updateStatus`, `syncPermissions`) → `role_stats`
    - invitation mutations → `pending_invitations` (in `UserController::save()` invite branch, `UserController::resendInvitationAjax()`, `InvitationController::acceptInvitation()`)
    - password-reset request → `resets_this_week` (in `PasswordResetController` after `$this->resets->create($userId, 'reset')`)
    - `AuditLogger::log()` already forgets `audit_today` on every successful insert.

### Audit logging — `App\Services\AuditLogger` (static)

- `AuditLogger::log(['module' => …, 'action' => …, 'description' => …?, 'details' => [...]?])` — auto-fills `actor_id` / `actor_label` from `Auth`, resolves client IP, delegates to `ActivityLog::create()`, then forgets `audit_today`. Failures are silently swallowed — logging must never break the primary action.
- Call it **in the controller, after the model returns success** — never inside a model method (a failed write must not leave a spurious log entry). Sites: `AuthController` (login, logout); `UserController` (create, update, delete, status change, unlock-login, invite, invite_resent); `InvitationController` (invitation_accepted); `PermissionController` (create, update, delete, assign, revoke); `RoleController` (create, update, delete, sync_permissions).

### Password reset & invitation — `App\Models\PasswordReset`

- Tokens stored **SHA-256 hashed**; the plain token only travels in the email link. `TTL_RESET = '+1 hour'`, `TTL_INVITATION = '+48 hours'`.
- `create(int $userId, string $type): string` (invalidates prior live tokens of the same type) · `findValidByToken(string $token, string $type): array|false` · `markUsed(int $id): bool`.
- Table `password_resets`: `id`, `user_id`, `token_hash CHAR(64)`, `type ENUM('reset','invitation')`, `expires_at`, `used_at`, `created_at`.
- Invitation flow: admin creates user with `invite=1` → `UserController::save()` sets `status = User::STATUS_PENDING` + a random unusable password hash → `PasswordReset::create($id, 'invitation')` + `MailService::sendInvitationEmail()` → user opens `/accept-invitation?token=…` → `InvitationController::acceptInvitation()` calls `User::resetPassword()` + `User::activate()` + `PasswordReset::markUsed()`. Resend: `POST /users/{id}/resend-invitation` (auto-invalidates the previous token).
- **User status** (`users.status` tinyint): `0` `STATUS_INACTIVE` (deactivated by admin — cannot log in) · `1` `STATUS_ACTIVE` · `2` `STATUS_PENDING` (invited, password not set — cannot log in, reset password, or auto-login via cookie).

### Frontend / views

- **No inline `<script>` before `footer.php`** (Bootstrap plugins like `.tab()` are not loaded yet) and **no inline event-handler attributes** (`onclick=`, `onchange=`, …). Wire a delegated `$(document).on('click', '.your-class', …)` handler in the module's JS file (see `.btn-detail` / `.btn-date-picker` in `audit-log/index-audit.js`).
- Register page assets at the top of the view: `$module_scripts = ['feature/file']`, `$module_styles = ['feature/file']`. `public/css/core/ui-components.css` is global (in `header.php`) — no per-page declaration.
- **Conditional plugins** via the `$plugins` arg to `Controller::render($view, $data, $plugins, $module_scripts, $module_styles)` — asset maps in `App\Core\AssetRegistry`. Available: `datatables`, `datatables-export`, `select2`, `validate`, `chart`. Full load order: `docs/AJAX_AND_MODULES.md`.
- **Pass PHP data to JS** via `data-*` attributes (`htmlspecialchars(json_encode(...))`), read with `element.dataset.*` — never a `<script>` block inside a view. Flash/session values interpolated into an inline `<script>` use `json_encode()`, never `addslashes()` (does not escape `</script>`).
- **AJAX:** controller returns `$this->jsonResponse($data)`. When the JS then calls `location.reload()`, set `$_SESSION['message']` + `$_SESSION['icon']` first (`messages.php` fires `ToastUtils[icon](message)` on reload). First-login welcome popup: set `$_SESSION['welcome_user']` → `AlertUtils.welcome()`.
- **SweetAlert2:** only via `ToastUtils` / `AlertUtils` (`public/js/core/sweetalert-utils.js`) — never call `Swal.fire()` directly in a module script.
- **Select2:** `.select2` elements auto-init via `ComponentUtils.initAll()` on `DOMContentLoaded` — don't call `initializeSelect2()` manually unless you need extra options. **Inside a modal:** call `initializeSelect2('#id', { dropdownParent: $('#modalId') })` explicitly and populate `<option>`s server-side, not via AJAX (Bootstrap's focus trap closes an unparented dropdown immediately).
- **Form validation:** pass `['select2', 'validate']`; `common-validate.js` configures jQuery Validate for Bootstrap 4 globally; each module calls `$('#form').validate({ rules, messages, submitHandler })`. DB uniqueness → `remote` rules to `/users/check-email` or `/users/check-document`.
- **AdminLTE cards:** `card-outline card-{color}` go on the outer `div.card`, **never** on `div.card-header` (silently breaks the colored left border).
- **Password show/hide toggle:** `<button type="button" data-password-toggle="#fieldId" aria-pressed="false" aria-label="Show password">` + `<i class="fas fa-eye-slash">`. One delegated handler in `public/js/core/common-utils.js` covers admin and auth pages — never re-implement per page.
- **Standalone pages** (`views/auth/*`, `views/errors/*`) do not use `header.php`/`footer.php`. Auth: `$this->renderStandalone($view, $data, $title, $module_scripts)` → `views/layouts/auth.php`. Errors: set `$code`/`$tone`/`$heading`/`$message` → `require views/layouts/error.php`. View files hold **only** the page-specific card markup — never a full `<html>` document. Every input needs a paired `<label class="sr-only">` (placeholder is not an accessible label); keep `form-group > input-group` structure so `.invalid-feedback` placement works.
- **Dark mode:** `localStorage` key `'theme'` (`'light'`|`'dark'`), falls back to `prefers-color-scheme`. Anti-FOUC IIFE lives once per layout (`header.php`, `auth.php`, `error.php`). Toggle JS: `public/js/modules/profile/theme-toggle.js` (respects `prefers-reduced-motion`). CSS: `public/css/core/dark-mode.css` (admin + the shared `--dm-*` semantic tokens), `login-dark.css` (auth), `errors-dark.css` (errors); module-specific light tokens (`--login-*`, `--error-*`) live in each module's own light CSS and are reused by the `-dark.css` file. Not an auditable business action — no DB column, no endpoint, no `AuditLogger` call.
- **`.bg-light` / `.thead-light`** don't invert under `html.dark-mode` on their own — already patched once in `dark-mode.css` (→ `--dm-bg-alt` / `--dm-text` / `--dm-border`). Extend that shared block for any other Bootstrap "light" utility (`.table-light`, `.list-group-item-light`, …); don't patch per-module CSS.
- **Flash messages** (`views/layouts/messages.php`) degrade without JS — a `<noscript>` plain Bootstrap alert plus a JS fallback if `ToastUtils` is undefined. Don't remove either when touching the file.

## Testing

- **Unit** (`tests/Unit/`) — no DB, no `session_start()`; manipulates `$_SESSION` as a plain array. Mirrors the `app/` directory structure. Base class `tests/TestCase.php` (resets superglobals, `invokePrivate()` helper).
- **Integration** (`tests/Integration/`) — needs a test DB. Base `tests/IntegrationTestCase.php` loads `.env.testing`, resets the `Connection` singleton, loads `database/schema.sql` + `tests/fixtures/sql/minimal_seed.sql` once (`setUpBeforeClass`), and wraps each test in a transaction rolled back on teardown. Opt out with `protected bool $useTransactions = false` + `self::reloadSeed()` when the SUT manages its own transaction (`Role::syncPermissions`, `Permission::syncForUser`) or for the `Auth::refreshPermissionsIfStale` / `attemptRememberLogin` tests.
- **Test DB (one time):** `mysql -u root -p -e "CREATE DATABASE php_mvc_admin_starter_test CHARACTER SET utf8;"` then `cp .env.testing.example .env.testing` and set `DB_*`.
- Image fixtures in `tests/fixtures/images/`. Method names: `test_[method]_[condition]_[expected result]`. No PDO / `move_uploaded_file()` mocking — real DB, real `/tmp` files. `set_error_handler()` must always be paired with `restore_error_handler()`. **Don't test controllers** — they're covered by manual browser testing.
- Full reference: `docs/TESTING.md`.

## Deep-dive docs

- `docs/ACCESS_CONTROL.md` — request guard flow, session keys, security controls, remember-me, login throttling, permission model, cache refresh contract, status values, application permissions reference
- `docs/AJAX_AND_MODULES.md` — AJAX endpoint pattern, frontend module structure, plugin load order, `ToastUtils`/`AlertUtils`, `loadingWithMinTime`, modal-first pattern
- `docs/TESTING.md` — suites, DB setup, base classes, fixtures, conventions, CI, static analysis & code style
- `docs/SEEDING.md` — seeded roles/users, permission matrix, import order, rerun behavior
- `docs/AI_SETUP.md` — `.claude/skills/`, MCP servers (`.mcp.json`), Bash permission allowlist
