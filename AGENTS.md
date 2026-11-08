# AGENTS.md

Operational reference for any AI agent (and humans) working on this repo. **Single source of truth**
for setup, architecture, and every coding convention. `CLAUDE.md` imports this file. Deep-dive
material lives in `docs/`.

## Project

PHP MVC admin starter — authentication, user / role / permission management, permission-based access
control. AdminLTE 4 / Bootstrap 5.3 without jQuery (released as `4.0.0`; the `3.x` tags are AdminLTE 3,
migration spec in `specs/001-adminlte-migration/`), PDO/MySQL, Composer native
PSR-4 autoloading. Pure PHP, **no build process** (no Makefile, no bundler — frontend assets are static files in
`public/`, copied from npm/Composer by `npm run assets`). Current release tag: `4.0.0`.

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
- Seed dataset (users, permission matrix, rerun behavior): `docs/guides/SEEDING.md`.
- `schema.sql` and `seeder.sql` hardcode `USE auth_base`: `mysql other_db < database/schema.sql` still writes to `auth_base`. For a throwaway or test DB, strip those lines first (`sed '/^\(CREATE DATABASE\|USE\) /d' database/schema.sql | mysql other_db`) or use the Integration suite, which already skips them.

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

## Dependencies

- **`tecnickcom/tcpdf` is pinned to `^6.11` on purpose — do not bump to 7.x.** The 7.x series is a
  deprecated compatibility shim (its own `composer.json` says so) that delegates to
  `tecnickcom/tc-lib-pdf ^8`, only really implements ~24 of 293 methods, and ships **no fonts** —
  they are built by a `post-install-cmd` needing `fontforge`, which would have to run on every
  `composer install` (dev, all 3 CI jobs, prod) since `vendor/` is not committed. A `new TCPDF()`
  throws in the constructor under 7.x. 6.11.4 bundles 165 font files (full `dejavusans`) and works.
  Close any Dependabot PR that raises this constraint.
- **`phpunit/phpunit` (`^11`) y `laravel/pint` (1.30.x) se quedan en su versión mientras el proyecto soporte PHP 8.2.** phpunit 13 exige PHP ≥ 8.4 y pint ≥ 1.31 exige PHP ≥ 8.3, así que `composer install` falla en el CI (PHP 8.2) y en local. Cierra sus PR de Dependabot con `@dependabot ignore` hasta subir el mínimo de PHP en `composer.json` y en `tests.yml`.

### Dependencias frontend

Composer (`almasaeed2010/adminlte`, require-dev) y npm (`package.json`, devDependencies fijas) son solo
**fuentes de descarga**: `npm run assets` (`scripts/sync-assets.mjs`) copia los archivos a `public/`
sin transformarlos y lo copiado se versiona. No hay bundler ni CDN en tiempo de ejecución, y el servidor
no necesita Node.

```bash
composer install && npm ci && npm run assets   # Node 22 (.nvmrc); idempotente
```

- Al cambiar `package-lock.json` o la versión de AdminLTE, ejecuta `npm run assets` y commitea `public/`.
- Las seis librerías de AdminLTE 4.10.1 (Bootstrap, Bootstrap Icons, OverlayScrollbars, Tabulator, Tom Select, Chart.js) se mantienen en sus versiones y suben juntas con AdminLTE; Dependabot las ignora salvo avisos de seguridad.
- **SheetJS 0.20.3 solo viene de `cdn.sheetjs.com`** (tarball con integridad en el lockfile): el paquete `xlsx` de npm (0.18.5) tiene CVE-2023-30533 y CVE-2024-22363, corregidos en 0.19.3 y 0.20.2. Dependabot no lo vigila: revísalo a mano. Escáneres como OSV lo marcan por el nombre del paquete aunque la versión ya esté corregida.
- **Despliegue (`rsync`/`scp`)**: excluir `.git/`, `.github/`, `node_modules/`, `vendor/` (se instala en destino), `package.json`, `package-lock.json`, `.nvmrc`, `scripts/`, `docs/`, `specs/`, `deliverables/` y `tests/`. `.gitattributes` aplica la misma lista a `git archive`.

- Hashes sha384, versions and load order of the copied assets, plus the SweetAlert2 supply-chain note: `docs/guides/FRONTEND_ASSETS.md`.

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
    app/Services/     ImageService, MailService, DashboardCache, LoginThrottleService, AuditLogger, AuditLogPdfExporter; Pdf/ = ReportDocument (\TCPDF)
    routes/web.php · database/ (schema.sql, seeder.sql) · views/ · public/ · vendor/ (not committed)
    ```
- **Autoloading:** Composer PSR-4 `"App\\": "app/"`, no custom autoloader. `app/Core/helpers.php` loads via `autoload.files` — it defines only `generateCSRFToken()`, `verifyCSRFToken()`, `regenerateCSRFToken()`, and the `env()` wrapper.
- **DB:** `Connection::getInstance()` singleton → configured PDO. All queries use prepared statements.
- **Auth hub:** `App\Core\Auth` — static, never instantiate. `check()`, `id()`, `user()`, `isAdmin()`, `hasPermission($name)`, `login()`, `logout()`, `checkTimeout()`, `checkSecurity()`, `checkAccountState()`, `syncPasswordFingerprint()`, `canGrantPermissionIds()`, `canManageUser()`, `reloadPermissions()`, `refreshPermissionsIfStale()`, `issueRememberCookie()`, `attemptRememberLogin()`. Full method reference + flows: `docs/guides/ACCESS_CONTROL.md`.
- **Middleware** (declared per route in `routes/web.php`): `auth` — remember-me auto-login → timeout + anti-hijacking → `checkAccountState()` (status + password fingerprint) → `refreshPermissionsIfStale()`; `guest` — remember-me auto-login, then redirects authenticated users away from login/forgot pages; `perm:NAME` — `hasPermission()` → 403 view on failure.
- **Permissions:** `hasPermission()` reads `$_SESSION['user_permissions']` — zero DB per call. Admins (role `is_system = 1`) get `['*']`. Non-admins get an `array_unique` **union** of their direct `user_permissions` and the permissions inherited from their role via `role_permissions`. The cache is refreshed by `refreshPermissionsIfStale()` (called by `AuthMiddleware` on every authed request) comparing `$_SESSION['permissions_ts']` against `users.permissions_updated_at`.
- **Error handling:** `App\Core\ErrorHandler` handles 403/404/500, auto-detects AJAX (`X-Requested-With`) → JSON or `views/errors/*.php`. Each error view sets only `$code`, `$tone` (`'danger'`|`'warning'`), `$heading`, `$message`, then `require`s `views/layouts/error.php` (owns the full standalone document). Styling: `public/css/modules/errors/errors.css` (both themes, reuses `--dm-*`). `[data-tone]` on `<body>` drives the `.error-code` color — never hardcode it per view.
- **Static analysis:** PHPStan `phpstan.neon`, **level 5** — analyses `app/` + `routes/`, excludes `app/Config/config.php`. Bootstrap `define()` constants (`URL`, `APP_VERSION`, `APP_BASE_PATH`) are declared in `phpstan-constants.php` (`scanFiles`) and listed under `dynamicConstantNames` so PHPStan does not narrow them to literal values — add any new bootstrap constant there too.
- **CI:** `.github/workflows/tests.yml` — jobs `quality` (pint `--test` + phpstan + `npm ci && npm run assets` con `git diff --exit-code -- public/` + `scripts/check-asset-paths.php`) / `unit` / `integration` (MySQL 8.0 service container, `.env.testing` generated at runtime, no secrets committed). Runs on `pull_request` (any branch) and `push` to `main` or tags only — feature branches are covered by the PR event. `paths-ignore` skips docs/image-only changes (css/js changes run, because `quality` verifies `public/` against `npm run assets`); a `concurrency` group cancels superseded runs on the same ref. `actions/checkout@v7`, `actions/setup-node@v7` (Node de `.nvmrc`). Dependabot (`.github/dependabot.yml`) opens weekly update PRs for Composer, npm (grouped) and GitHub Actions.

## Critical conventions (deviation breaks things)

### Backend

- **Namespaces:** `App\Controllers\*`, `App\Models\*`, `App\Services\*`, `App\Core\*` consistently.
- **Auth state:** only through `Auth::check()` / `hasPermission()` / `id()` / `user()` — never read `$_SESSION` directly for auth state outside `App\Core\Auth`.
- **CSRF:** generate with `generateCSRFToken()`, validate with `$this->csrfCheck()` (returns JSON 403 for AJAX, redirects otherwise). Call `regenerateCSRFToken()` after **every** successful POST **and** on the failure paths of sensitive endpoints (e.g. password change). Destructive non-AJAX actions, logout included, must be POST routes with CSRF — never GET.
- **Remember-me invalidation:** call `$userModel->clearRememberToken($userId)` (via `Auth::clearRememberCookie()`) on every password change, no matter who triggered it (self-service, admin edit, invitation acceptance, forgot-password). `User::updatePassword()` and `UserPasswordTrait::resetPassword()` do this internally — never bypass either with a raw SQL `UPDATE` on the `password` column.
- **Privilege boundaries — nobody grants what they do not hold.** A non-admin may only manage accounts whose effective permissions (role + direct) they already have (`Auth::canManageUser()`: edit, status toggle, unlock, resend invitation). Assigning a role or permissions requires holding all of them (`Auth::canGrantPermissionIds()`; for a role, pass `Role::getAssignedPermissionIds()`). Lo mismo al **quitar**: `RoleController::syncPermissions` exige tener los permisos activos que se retiran, y `Role::syncPermissions` solo reemplaza los activos (los inactivos asignados se conservan, porque el formulario no los muestra). Only an admin touches `is_system` roles/accounts, or renames a permission. Validate everything **before** writing to the DB or disk (images included) and read the current state (e.g. user status) from the DB, never from POST.
- **Session invalidation:** `AuthMiddleware` calls `Auth::checkAccountState()` on every request — the session dies if the account is no longer active or its password hash changed (`pw_fp` fingerprint). After a self-service password change call `Auth::syncPasswordFingerprint()` so the current session survives. Passwords are never `trim()`med (spaces are part of the secret). `AuthMiddleware` opens a fresh session before storing the redirect flash, otherwise the message is lost.
- **Client-side output:** any user-controlled value that reaches SweetAlert (`title`, `options.html`), `.html()` or the audit-log detail modal goes through `escapeHtml()` (global, `sweetalert-utils.js`) or `.text()`. Audit-log values (e.g. the identifier of a failed login) come from unauthenticated visitors.
- **Edit forms with checkbox lists** (`users/update.php` permissions) include a hidden `<name>_submitted=1` marker: an unchecked list posts nothing, so the controller syncs only when the marker or the list is present — otherwise a client omitting the field would wipe the stored values.
- **AJAX business rejections** (permission/rule denied) answer HTTP 200 with `success:false` — a 403 is treated as a transport failure and hides the reason. Use 403 only for CSRF/middleware.
- **Login hardening:** one generic failure message for unknown user / wrong password / locked account (`AuthController::GENERIC_FAILURE`), a dummy `password_verify` for unknown users, and a per-IP failure cap (`IP_MAX_FAILURES` in `IP_WINDOW_MINUTES`, counted from `activity_logs`). The IP comes from `REMOTE_ADDR`: behind a reverse proxy it is the proxy's, so configure the web server to restore the client IP (e.g. `mod_remoteip`) before relying on it.
- **Input sanitization:** `trim()` at the model layer (`trimInput()`). `htmlspecialchars()` **only** at the view layer on output — never in the model or before storing in the DB.
- **Passwords:** always `password_hash($pass, PASSWORD_DEFAULT)` / `password_verify()`. Minimum 8 characters.
- **Images:** route every upload / resize / delete through `ImageService`. MIME type is validated server-side via `(new \finfo(FILEINFO_MIME_TYPE))->file($tmp_name)` — never `$_FILES['type']` (client-controlled). Extension whitelist: `jpg`, `jpeg`, `png`, `gif`, `webp`.
- **PDF exports:** generate server-side through `AuditLogPdfExporter` (which owns the `\TCPDF` subclass `ReportDocument`) — **never instantiate TCPDF in a controller**. The exporter takes rows + metadata as plain parameters (zero DB/Auth coupling) and returns the PDF as a string; the controller sets the headers and echoes. UTF-8 requires the `dejavusans` font (bundled in TCPDF `^6`).
- **Fat model / thin controller:** validation, data formatting, and cache invalidation live in the model. Controllers read POST/GET, call one or a few model methods, set session flash messages, and redirect or return JSON — nothing more.
- **Model base class:** extend `App\Core\Model`, set `protected $table = '…'`. It provides `find`, `all`, `insert`, `update`, `delete`, `query`, `getLastInsertId`, `trimInput` — never redeclare those in a concrete model. Override only methods needing JOINs or custom field handling.
- **Model traits:** past ~400 lines, split concerns into `app/Models/Traits/` by responsibility (auth queries, password/token lifecycle, statistics). Traits share `$this` — they use `$this->connection` / `$this->table` directly.
- **Commits:** Conventional Commits (`feat:`, `fix:`, `docs:`, `style:`, `chore:`, …). Version tags carry **no `v` prefix** (`4.0.0`, not `v4.0.0`).
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

- `AuditLogger::log(string $module, string $action, string $description = '', array $details = [], ?int $actorId = null, ?string $actorLabel = null)` — **posicional** (never pass an array). Auto-fills `actor_id` / `actor_label` from `Auth`, resolves client IP, delegates to `ActivityLog::create()`, then forgets `audit_today`. Failures are silently swallowed — logging must never break the primary action.
- Call it **in the controller, after the model returns success** — never inside a model method (a failed write must not leave a spurious log entry). Sites: `AuthController` (login, logout); `UserController` (create, update, delete, status change, unlock-login, invite, invite_resent); `InvitationController` (invitation_accepted); `PermissionController` (create, update, delete, assign, revoke); `RoleController` (create, update, delete, sync_permissions); `AuditLogController` (export).

### Password reset & invitation — `App\Models\PasswordReset`

- Tokens stored **SHA-256 hashed**; the plain token only travels in the email link. `TTL_RESET = '+1 hour'`, `TTL_INVITATION = '+48 hours'`.
- `create(int $userId, string $type): string` (invalidates prior live tokens of the same type) · `findValidByToken(string $token, string $type): array|false` · `markUsed(int $id): bool`.
- Table `password_resets`: `id`, `user_id`, `token_hash CHAR(64)`, `type ENUM('reset','invitation')`, `expires_at`, `used_at`, `created_at`.
- Invitation flow: admin creates user with `invite=1` → `UserController::save()` sets `status = User::STATUS_PENDING` + a random unusable password hash → `PasswordReset::create($id, 'invitation')` + `MailService::sendInvitationEmail()` → user opens `/accept-invitation?token=…` → `InvitationController::acceptInvitation()` calls `User::resetPassword()` + `User::activate()` + `PasswordReset::markUsed()`. Resend: `POST /users/{id}/resend-invitation` (auto-invalidates the previous token).
- **User status** (`users.status` tinyint): `0` `STATUS_INACTIVE` (deactivated by admin — cannot log in) · `1` `STATUS_ACTIVE` · `2` `STATUS_PENDING` (invited, password not set — cannot log in, reset password, or auto-login via cookie).

### Frontend / views

- **No inline `<script>` before `footer.php`** (Bootstrap plugins like `.tab()` are not loaded yet) and **no inline event-handler attributes** (`onclick=`, `onchange=`, …). Wire a delegated `document.addEventListener('click', e => e.target.closest('.your-class') && …)` handler in the module's JS file (see `.btn-detail` / `.btn-date-picker` in `audit-log/index-audit.js`).
- Register page assets at the top of the view: `$module_scripts = ['feature/file']`, `$module_styles = ['feature/file']`. `public/css/core/ui-components.css` is global (in `header.php`) — no per-page declaration.
- **Conditional plugins** via the `$plugins` arg to `Controller::render($view, $data, $plugins, $module_scripts, $module_styles)` — asset maps in `App\Core\AssetRegistry`. Available: `tabulator`, `tom-select`, `export` (Excel/PDF), `chart`. Full load order: `docs/guides/AJAX_AND_MODULES.md`.
- **Pass PHP data to JS** via `data-*` attributes (`htmlspecialchars(json_encode(...))`), read with `element.dataset.*` — never a `<script>` block inside a view. Flash/session values interpolated into an inline `<script>` use `json_encode()`, never `addslashes()` (does not escape `</script>`).
- **AJAX:** controller returns `$this->jsonResponse($data)`. When the JS then calls `location.reload()`, set `$_SESSION['message']` + `$_SESSION['icon']` first (`messages.php` leaves them in a hidden `#flash-data` element as `data-message`/`data-icon`; `sweetalert-utils.js` fires `ToastUtils[icon](message)` on load). First-login welcome popup: set `$_SESSION['welcome_user']` → `data-welcome` on that element → `AlertUtils.welcome()`.
- **JS helpers (sin jQuery):** detalle completo (opciones de `TableUtils`, controles, tamaños, contador, exportación, responsivo, axe, Tom Select en modales, `SwalTheme`) en `docs/guides/FRONTEND_HELPERS.md`. Reglas que rompen cosas si se ignoran:
    - Toda petición AJAX usa `Http.post/get` (`core/http.js`): lleva `X-Requested-With` y el token CSRF vigente; sin la cabecera `csrfCheck` y `ErrorHandler` responden HTML. Un POST que no recarga la página debe devolver `csrf_token`.
    - `FormValidator` valida con `validity`, nunca con `checkValidity()`/`reportValidity()`; `minlength`/`maxlength` se comprueban a mano.
    - `TableUtils`: los datos viajan en `data-rows` (JSON armado en el controlador, nunca en la vista); toda celda con datos de usuario pasa por `escapeHtml`; las columnas usan `sorter: 'alphanum'`; Copiar y CSV exportan el dato crudo, así que una columna con marcado declara `exportAs`.
    - Acciones por fila según permiso: el servidor manda `manage` por fila y el formatter solo dibuja los botones permitidos; el rechazo del servidor sigue siendo la barrera real.
    - `ComponentUtils`: todo `<select>` de formulario o filtro usa Tom Select (`<select class="tom-select">`); con `multiple`, copia `getValue()` (`[...ts.getValue()]`) antes de cerrar el modal.
    - `SwalTheme`: el tema de SweetAlert2 sale de `data-bs-theme`, nunca `auto`; botones con contraste ≥ 4.5:1 sobre texto blanco.
    - Registro de auditoría: `AuditLogController::index` envía todas las filas en `data-rows` (riesgo aceptado; mediciones y umbral en `docs/guides/FRONTEND_HELPERS.md`).
- **SweetAlert2:** only via `ToastUtils` / `AlertUtils` (`public/js/core/sweetalert-utils.js`) — never call `Swal.fire()` directly in a module script.
- **Select2, DataTables y jQuery Validate ya no existen en las pantallas:** ningún controlador pide los plugins `select2`/`datatables`/`validate` y ningún JS de `public/js/modules/` usa jQuery. Selects → Tom Select (`ComponentUtils`), tablas → Tabulator (`TableUtils`), validación → `FormValidator`. Los archivos de esas librerías y de jQuery ya no están en `public/`.
- **Form validation:** `FormValidator.init(form, { … })` (`core/form-validator.js`, cargado por `footer.php` y `auth.php`): atributos nativos (`required`, `minlength`, `pattern`) más `equalTo` y `remote` para unicidad (`/users/check-email`, `/users/check-document`, `/roles/check-name`, `/permissions/check-name`); mensajes en `.invalid-feedback` con `.is-invalid`. Detalle del uso de `validity` y `minlength` en el bloque «JS helpers» de arriba.
- **Encabezados:** un `h1` por pantalla; el título de cada card es `h2.card-title` (la clase fija tamaño y peso, no el nivel), lo que cuelga de un card (acordeón, vista previa) es `h3`, y el nombre de una ficha es `h2` con `.h4`/`.fs-4` para el tamaño. No uses encabezados para cifras ni avisos: las cifras del dashboard son `<p class="h3">` (con ese `.h3` AdminLTE aplica su tamaño responsivo por columna) y los avisos `<p class="h5">`. Axe `heading-order` lo comprueba.
- **AdminLTE cards:** `card-outline card-{color}` go on the outer `div.card`, **never** on `div.card-header` (silently breaks the colored left border).
- **Separación entre cards:** AdminLTE 4 / Bootstrap 5 ya no da `margin-bottom` a `.card` (AdminLTE 3 sí): las cards apiladas en la misma columna o formulario llevan `mb-4`; una sola card por columna no lo necesita.
- **Cards con pestañas:** `<div class="card card-{color} card-outline card-outline-tabs">` con `<button class="nav-link" data-bs-toggle="tab">`. La card no lleva borde superior: el acento es el borde superior de la pestaña activa. `ui-components.css` lo pinta, junto con el texto de las pestañas, desde `--lte-card-variant-bg`, así que cualquier `card-{color}` funciona sin CSS nuevo y con contraste en claro y oscuro; no añadas bloques por color con hex fijos. AdminLTE 4 solo implementa a medias `card-outline-tabs` (quita el borde de la card y estiliza `.card-header a`, pero no colorea la pestaña activa ni los `<button>`).
- **Password show/hide toggle:** `<button type="button" data-password-toggle="#fieldId" aria-pressed="false" aria-label="Show password">` + `<i class="bi bi-eye-slash">`. One delegated handler in `public/js/core/common-utils.js` covers admin and auth pages — never re-implement per page.
- **Standalone pages** (`views/auth/*`, `views/errors/*`) do not use `header.php`/`footer.php`. Auth: `$this->renderStandalone($view, $data, $title, $module_scripts)` → `views/layouts/auth.php`. Errors: set `$code`/`$tone`/`$heading`/`$message` → `require views/layouts/error.php`. View files hold **only** the page-specific card markup — never a full `<html>` document. Every input needs a paired `<label class="visually-hidden">` (placeholder is not an accessible label); keep the `div.mb-3 > label + div.input-group` structure so `.invalid-feedback` placement works.
- **Dark mode:** `localStorage` key `'theme'` (`'light'`|`'dark'`), falls back to `prefers-color-scheme`. The anti-FOUC IIFE (the only inline `<script>` allowed in a layout) lives once per layout (`header.php`, `auth.php`, `error.php`) and writes `data-bs-theme` on `<html>` (`<html data-lte-color-mode="off">` turns AdminLTE's own color mode off); the toggle icon is shown by CSS from `data-bs-theme` (`.theme-icon-to-dark` / `.theme-icon-to-light`), never swapped by JS, so there is no flicker on load. `dark-mode.css` keys on `[data-bs-theme="dark"]`; AdminLTE 4 / Bootstrap 5.3 colour navbar, sidebar, cards, forms, pagination, tooltips and tabs from `--bs-*`, so only tuned accent colours, small-box/modal headers, autofill and `.bg-light` are patched there. Toggle JS: `public/js/modules/profile/theme-toggle.js` (writes `data-bs-theme` + `localStorage['theme']`) (respects `prefers-reduced-motion`). CSS: `public/css/core/dark-mode.css` (admin + the shared `--dm-*` semantic tokens), `login-dark.css` (auth); module-specific tokens (`--login-*`) live in the module's own CSS. Not an auditable business action — no DB column, no endpoint, no `AuditLogger` call.
- **`.bg-light`** doesn't invert under `[data-bs-theme="dark"]` on its own — already patched once in `dark-mode.css` (→ `--dm-bg-alt` / `--dm-text` / `--dm-border`). Extend that shared block for any other Bootstrap "light" utility (`.table-light`, `.list-group-item-light`, …); don't patch per-module CSS.
- **Tooltips y Tom Select en oscuro** (`ui-components.css`): el tooltip se colorea con `--bs-tooltip-bg` (fondo y flecha a la vez; en oscuro `--dm-bg-hover`, porque `#212529` se confunde con la página); Tom Select trae colores fijos (`#343a40` en texto, `#efefef` en etiquetas, `#d0d0d0` en el borde del desplegable) y la hoja del plugin carga después, así que las reglas del tema llevan más especificidad (`.ts-wrapper .ts-control`, `.ts-wrapper.multi .ts-control > div.item`). Los acentos de `--dm-*` se usan como fondo con texto blanco: `--dm-success`/`--dm-danger` están oscurecidos para llegar a 4.5:1; no los aclares.
- **Flash messages** (`views/layouts/messages.php`) hold no `<script>`: they print `#flash-data` (data attributes) and a `<noscript>` Bootstrap alert; the JS handler and its plain-alert fallback (if SweetAlert2 fails to load) live at the end of `sweetalert-utils.js`. Don't remove either when touching them.
- **Layout globals:** `baseUrl` and `csrfToken` come from `<meta name="base-url">` / `<meta name="csrf-token">`, read in `common-utils.js` (`http.js` los usa para el token vigente); layouts never emit them from an inline script. The sidebar scrollbar (OverlayScrollbars) is initialised in `common-utils.js` for the same reason.

## Testing

- **Unit** (`tests/Unit/`) — no DB, no `session_start()`; manipulates `$_SESSION` as a plain array. Mirrors the `app/` directory structure. Base class `tests/TestCase.php` (resets superglobals, `invokePrivate()` helper).
- **Integration** (`tests/Integration/`) — needs a test DB. Base `tests/IntegrationTestCase.php` loads `.env.testing`, resets the `Connection` singleton, loads `database/schema.sql` + `tests/fixtures/sql/minimal_seed.sql` once (`setUpBeforeClass`), and wraps each test in a transaction rolled back on teardown. Opt out with `protected bool $useTransactions = false` + `self::reloadSeed()` when the SUT manages its own transaction (`Role::syncPermissions`, `Permission::syncForUser`) or for the `Auth::refreshPermissionsIfStale` / `attemptRememberLogin` tests.
- **Test DB (one time):** `mysql -u root -p -e "CREATE DATABASE php_mvc_admin_starter_test CHARACTER SET utf8;"` then `cp .env.testing.example .env.testing` and set `DB_*`.
- Image fixtures in `tests/fixtures/images/`. Method names: `test_[method]_[condition]_[expected result]`. No PDO / `move_uploaded_file()` mocking — real DB, real `/tmp` files. `set_error_handler()` must always be paired with `restore_error_handler()`. **Don't test controllers** — they're covered by manual browser testing.
- Full reference: `docs/guides/TESTING.md`.

## Deep-dive docs

- `docs/guides/ACCESS_CONTROL.md` — request guard flow, session keys, security controls, remember-me, login throttling, permission model, cache refresh contract, status values, application permissions reference
- `docs/guides/AJAX_AND_MODULES.md` — AJAX endpoint pattern, frontend module structure, plugin load order, `ToastUtils`/`AlertUtils`, `loadingWithMinTime`, modal-first pattern
- `docs/guides/TESTING.md` — suites, DB setup, base classes, fixtures, conventions, CI, static analysis & code style
- `docs/guides/FRONTEND_HELPERS.md` — `Http`, `FormValidator`, `TableUtils`, `ComponentUtils`, `SwalTheme`: opciones, tamaños, responsivo, accesibilidad
- `docs/guides/FRONTEND_ASSETS.md` — versiones, sha384 y orden de carga de los assets copiados a `public/`
- `docs/guides/SDD.md` — árbol de artefactos y fases del flujo SDD
- `docs/guides/UPGRADE_4.0.md` — porting code written against 3.x (markup, plugins, JS helpers) to the AdminLTE 4 stack
- `docs/guides/SEEDING.md` — seeded roles/users, permission matrix, import order, rerun behavior
- `docs/guides/AI_SETUP.md` — `.claude/skills/`, MCP servers (`.mcp.json`), Bash permission allowlist

### Frontend lessons (Bootstrap / Tabulator)

- **Área táctil de 44px (≤576px) es global** en `public/css/core/ui-components.css` (`.btn`, `.btn-tool`, `.page-link`, `.nav-link` del header/sidebar, `.form-control`, `.form-select`, `.ts-control`): no la repitas por módulo. `.small-box-footer` no es `.btn` y conserva el estilo de AdminLTE. Casillas y switches (16px) no entran en la regla: envuélvelos en una fila de 44px como `#formSyncPermissions .form-check`.
- A `<button>` inside an `.input-group` (flex `align-items:stretch`) never shrinks like a sibling `<div>`. Wrap it in a `.input-group-text`-style `<div>` and reset the button (`all:unset; display:flex; align-items:center; justify-content:center; width:100%; height:100%`). For the 44px touch target, do not put `min-height`/`min-width` on the visible box: use an absolute `::before` with `width/height: max(100%, 44px)`.
- Do not patch a shared base selector (`.form-control`, `.btn`) for a local symptom. Compare `getComputedStyle` against a sibling that looks right and scope the fix to the broken element's selector.
- Bootstrap tooltips en filas que la tabla repinta (paginar, ordenar, buscar): no se inicializan por fila; `ComponentUtils` delega un único tooltip en `<body>` (`data-bs-toggle="tooltip"`), así que sobreviven al repintado.
- Tras guardar un cambio que recarga la tabla (`location.reload()` o redibujar), el filtro de búsqueda se pierde: es esperado, no un fallo. Al probar con Playwright, el clic «estable» puede colgarse y las transiciones CSS no avanzan: usa `.click()` por JS y desactiva las transiciones antes de medir colores.
- A hidden HTML template that shares a class with the cloned items breaks any global selector on that class (counts, guards): scope selectors to the real container (`'#container .item'`).
- Los `<link>`/`<script>` propios no llevan `?v=` (ni en los layouts ni en `AssetRegistry`): tras cambiar CSS/JS, el navegador puede servir la versión anterior. Antes de concluir que un cambio no surtió efecto, recarga sin caché (`fetch(url, {cache:'reload'})` o DevTools «Disable cache»); si se añade versionado, cubre también las etiquetas escritas a mano fuera del bucle.
- A contrast-tuned color override on a shared selector (especially with `!important`) must ship with its dark-mode counterpart in the same commit, using a _lightened_ value ≥4.5:1 on the real dark backgrounds. Verify both themes with axe-core.
- After renaming or regrouping classes in the markup (`bg-*` → `text-bg-*`, `badge-*` → `text-bg-*`, `.btn-sm` per button → `.btn-group-sm` on the group), re-grep every CSS/JS selector that targets them: a selector for a class that no longer exists fails silently (the `.modal-header.bg-*` dark rules did nothing once the views moved to `text-bg-*`). Sweep the renamed class list against selectors **and** `class=` tokens in `views/` and `public/js`. Tie size/variant rules to the stable structure (`#tableX .btn-group .btn`), not to the modifier.
- En Playwright las transiciones CSS no avanzan, y `*{transition:none}` no alcanza a `::before`/`::after` (la flecha del tooltip se mide transparente): `document.getAnimations().forEach(a => a.finish())` antes de medir. El perfil del navegador cachea las hojas sin `?v=`: fuerza `fetch(url, {cache:'reload'})` y reemplaza el `<link>` con un `?v=` nuevo antes de concluir que un cambio no surtió efecto.
- Para ver una pantalla sin sesión ni BD: renderiza la vista con los layouts reales y datos de muestra desde un script PHP de CLI (`Controller::render` con `$_SESSION` simulado) y sírvela como HTML estático desde el docroot de Apache, borrándola al terminar. Con sesión real, el seeder deja `admin123` para todos sus usuarios (`docs/guides/SEEDING.md`).
- An a11y audit of the initial state is not enough: open every modal, activate every tab/accordion and audit conditional branches (empty list vs. with data).

### Test lessons

- A fixture/trait that does `DROP`/`CREATE` on a fixed-name resource (DB, file) must suffix the name with a per-process id (`getmypid()`) when tests run in parallel (paratest).
- A test that resets a shared singleton/global (DB connection, config) must restore the test double in `tearDown`, not leave it `null`: with random order, later tests would try to reach the real resource. "Passes locally" proves nothing when local has the real resource; to reproduce CI, run without `.env`/`.env.testing` and with `--order-by=default`.
- Never compute a test's cutoff date with `date()`/`strtotime()` to compare against the DB's `NOW()`/`CURDATE()`: the process clock/timezone and the DB server's can differ. Use an SQL expression (`NOW() - INTERVAL 1 HOUR`).

## Spec-Driven Development (SDD)

This project runs on SDD: spec first, then plan, then tasks, and only then
code. **The spec is the contract: if it isn't there, it isn't implemented.**

See `docs/guides/SDD.md` for the artifact tree and the eight phases (commands for the plugin and the scripts).

### Rules

- **`specs/NNN` numbering:** `specs/`'s own counter, three digits, monotonic,
  never reusing deleted numbers. Starts at `001`. It is not the roadmap's
  ordinal nor a historical FR.
- **Verification as a gate:** no task is marked `[x]` without
  `composer check` green or a documented manual checklist with evidence.
- **Precedence:** `docs/constitution.md` rules over specs and plans. On
  conflict between `docs/`/`specs/` and this file, this file is the
  operational source for **code** and `docs/`/`specs/` is the **planning**
  layer.
- **Docs profile:** lite
- **`docs/`, `specs/`, and `deliverables/` are versioned but not deployed:**
  exclude them from the deploy artifact and the docroot.
- **Don't modify `specs/` outside its phase** without an explicit request.
