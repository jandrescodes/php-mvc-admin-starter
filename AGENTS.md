# AGENTS.md

Compact reference for AI agents working on this repo. Only high-signal, non-obvious facts.

## Setup gotchas

- `APP_URL` must end with `/public` (e.g. `http://localhost/ProyectoBase/public`) — without it router and assets break.
- Apache runs as `daemon` on LAMPP; `public/uploads/users/` must be `777` or `move_uploaded_file()` silently fails.
- `public/uploads/users/.htaccess` blocks PHP execution — never remove it. MIME type in `ImageService` is validated via `finfo` on `tmp_name`, not `$_FILES['type']`.
- Test DB must exist before running integration suite: `CREATE DATABASE php_mvc_admin_starter_test CHARACTER SET utf8;`

## Commands

```bash
composer dump-autoload -o            # after adding any class
vendor/bin/phpunit --testsuite=Unit  # no DB needed, ~2s
vendor/bin/phpunit --testsuite=Integration  # requires .env.testing + test DB
```

## Architecture

- **Entry point:** `public/index.php` → `app/Config/config.php` → `App\Core\Router`. Apache rewrites all requests to `index.php` via `public/.htaccess`.
- **Routes:** `routes/web.php` returns array of `['method', 'path', 'controller' => 'ControllerName@method', 'middleware' => []]`. Route params use `(\d+)` — captured as positional args to controller method. Router auto-appends `Controller` suffix if missing.
- **Auth:** `App\Core\Auth` is a static class — never instantiate. `Auth::check()`, `Auth::id()`, `Auth::user()`, `Auth::hasPermission()`, `Auth::login()`, `Auth::logout()` — never read `$_SESSION` directly for auth state.
- **CSRF helpers** (`generateCSRFToken()`, `verifyCSRFToken()`, `regenerateCSRFToken()`) load via Composer `autoload.files` from `app/Core/helpers.php` — always available globally.
- **Views:** Register JS via `$module_scripts = ['feature/file']` at view top. Register plugins via `$plugins` array to `Controller::render()`. Never use inline `<script>` before `footer.php`.
- **No build process** — pure PHP, no npm, no Makefile.

## Critical conventions (deviation breaks things)

- **CSRF:** Call `regenerateCSRFToken()` after **every** successful POST, including failure paths on sensitive endpoints (e.g. password change). Destructive actions (including logout) must be POST with CSRF, never GET.
- **Permission cache invalidation:** After any permission/role change, call `$userModel->updatePermissionsTimestamp($userId)` so affected users' session caches refresh. When role permissions change, do this for **every user in that role**.
- **Dashboard cache invalidation:** After user/perm/role mutations, call `DashboardCache::forget('key')` with the appropriate key (`user_stats`, `perm_stats`, `role_stats`, `pending_invitations`, `resets_this_week`, `audit_today`).
- **AuditLogger:** Call in controllers after model returns success — never inside model methods. Failed writes must not produce spurious log entries.
- **AJAX + location.reload():** Set `$_SESSION['message']` and `$_SESSION['icon']` before calling `$this->jsonResponse()` — `messages.php` renders toast on reload.
- **Select2 in modals:** Call `initializeSelect2('#id', { dropdownParent: $('#modalId') })` explicitly — without it, Bootstrap focus trap closes dropdown immediately.
- **AdminLTE cards:** `card-outline card-{color}` goes on outer `div.card`, **never** on `div.card-header` (silently breaks border style).
- **Remember-me:** `clearRememberToken()` must be called whenever a password changes (user or admin) — revokes all active sessions.
- **Standalone pages** (`views/auth/*.php`, `views/errors/*.php`) don't use `footer.php`. Views hold only page-specific markup; controllers call `$this->renderStandalone($view, $data, $title, $module_scripts)` (auth) or `require layouts/error.php` after setting `$code`/`$tone`/`$heading`/`$message` (errors) — the shared layout owns SweetAlert2, validation, and dark-mode assets. Never inline a full `<html>` document in a view.
- **Password toggle:** use `<button data-password-toggle="#fieldId" aria-pressed="false" aria-label="Show password">` — one delegated handler in `public/js/core/common-utils.js` handles all of them. Don't re-implement per page.
- **No inline event-handler attributes:** no `onclick=`/`onchange=` in view markup, same rule as inline `<script>`. Use a `data-*` attribute + a delegated `$(document).on('click', '.your-class', ...)` handler in the module's JS file.
- **`.bg-light`/`.thead-light` need the shared dark-mode override:** these Bootstrap classes don't invert under `html.dark-mode` on their own — already patched once in `public/css/core/dark-mode.css`. Extend that shared block for any other Bootstrap "light" utility (`.table-light`, etc.); don't patch per-module CSS.

## Testing quirks

- Unit tests manipulate `$_SESSION` directly — no `session_start()` needed.
- Integration tests auto-load `database/schema.sql` + `tests/fixtures/sql/minimal_seed.sql` once, then wrap each test in a transaction that rolls back on `tearDown()`. Opt-out by setting `protected bool $useTransactions = false`.
- Auth integration tests checking `Auth::refreshPermissionsIfStale()` and `Auth::attemptRememberLogin()` opt out of transaction wrapping and call `self::reloadSeed()` manually.

## doc references (read for deeper context)

- `docs/ACCESS_CONTROL.md` — permission cache flow, session guards
- `docs/AJAX_AND_MODULES.md` — AJAX endpoint conventions
- `docs/TESTING.md` — test suites, fixtures, conventions
