# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.15.3] - 2026-07-13

### Fixed

- **Remember-me cookie not revoked on password reset via token** — `UserPasswordTrait::resetPassword()` (used by `InvitationController::acceptInvitation()` and `PasswordResetController::resetPassword()`) updated the password hash but never called `clearRememberToken()`, unlike `User::updatePassword()`. An attacker's stolen remember-me cookie stayed valid after the account owner accepted an invitation or completed a forgot-password reset. `clearRememberToken($id)` is now called after a successful password update in `resetPassword()`, matching `updatePassword()`.

### Docs

- **README polished for open source** — added PRs-welcome/Conventional-Commits badges, a quick-nav link row, a full Table of Contents, and a Screenshots section (login, dashboard, users, roles).
- **`CONTRIBUTING.md` name fix** — replaced leftover "ProyectoBase" references (title, intro, PHPDoc `@package` examples, closing line) with "PHP MVC Admin Starter" / `PhpMvcAdminStarter`.

---

## [3.15.2] - 2026-06-25

### Security

- **RCE en `ImageService` por MIME type client-controlled** — `processImage()` usaba `$_FILES['type']` (header HTTP enviado por el cliente) para validar el MIME type, permitiendo a cualquier usuario autenticado subir un webshell PHP falsificando el `Content-Type`. Corregido reemplazando la validación con detección server-side via `(new \finfo(FILEINFO_MIME_TYPE))->file($tmp_name)`. Se agregó whitelist de extensiones (`jpg`, `jpeg`, `png`, `gif`, `webp`) para rechazar extensiones no-imagen independientemente del MIME. Se agregó `.htaccess` en `public/uploads/users/` bloqueando ejecución PHP como defensa en profundidad.
- **Stored XSS en mensajes de sesión** — `views/layouts/messages.php` usaba `addslashes()` para escapar `$_SESSION['welcome_user']`, `$_SESSION['message']` e `$_SESSION['icon']` antes de interpolarlos en bloques `<script>`. `addslashes()` no escapa `</script>`, permitiendo que un payload (e.g. nombre de usuario con `</script><script>alert(1)</script>`) rompa el bloque de script. Corregido reemplazando las tres interpolaciones con `json_encode()`, que escapa `</` como `<\/`.

---

## [3.15.1] - 2026-06-05

### Fixed

- **XSS in `$user['image']` src attribute** — `views/users/show.php` and `views/users/update.php` (two locations) echoed the image filename directly into `src="..."` without escaping. Wrapped with `htmlspecialchars()` to prevent attribute-injection attacks.
- **Undefined `$user['position']` in update view** — `views/users/update.php` profile-preview card referenced a `position` column that does not exist in the schema (removed in v3.10.0). Replaced with `$user['role_name'] ?? ''`.
- **Open redirect in CSRF failure handler** — `app/Core/Controller::csrfCheck()` redirected to `$_SERVER['HTTP_REFERER']` without validation, allowing an attacker-controlled `Referer` header to send users to external URLs on CSRF token failure. Redirect now only follows the referrer if it starts with the application's `URL` constant; falls back to `URL` otherwise.
- **Logout unprotected on GET route** — `GET /logout` triggered session destruction with no CSRF token, allowing any third-party page to log users out via `<img src="...">`. Route changed to `POST`; `views/layouts/header.php` logout link replaced with a `<form method="POST">` containing a CSRF token; `AuthController::logout()` now calls `$this->csrfCheck()`.
- **Remember-me cookie not revoked on password change** — `User::updatePassword()` updated the password hash but left `remember_token` intact, meaning an attacker's stolen cookie stayed valid after the account owner changed their password. `clearRememberToken($id)` is now called unconditionally after a successful password update.
- **CSRF token not rotated on password-change error path** — `UserController::updateProfilePasswordAjax()` only called `regenerateCSRFToken()` in the success branch. Token rotation moved before the `if/else` so it always runs, regardless of whether `updatePassword()` succeeds.

### Added

- **Dashboard CSS module** — new `public/css/modules/dashboard/dashboard.css` registered via `DashboardController` as a `module_style`. Contains all dashboard-specific animation and layout rules without touching AdminLTE or Bootstrap classes.
- **Slide+fade animation for access-metrics toggle** — the hidden metrics row now uses CSS `max-height` + `opacity` + `translateY` transitions (`600 ms / 480 ms`, `cubic-bezier(0.4, 0, 0.2, 1)`) instead of jQuery `animate()`. Initial restore from `localStorage` suppresses the transition via `requestAnimationFrame` to avoid animating on page load.
- **Chevron arrow on toggle button** — `fa-eye` replaced with `fa-sliders-h`; a `fa-chevron-down` icon appended after the label text rotates 180° (CSS `transform`) when the metrics row is visible.
- **Chart.js entrance animation** — all three dashboard charts (donut, bar, line) now specify `animation: { duration: 700, easing: 'easeInOutQuart' }` for a smooth render on initial load and on theme change.
- **Staggered entrance animations for stat cards** — the four top `small-box` widgets fade and slide up on page load with 70 ms stagger between each (`@keyframes db-fadein-up`).
- **Staggered entrance for chart cards** — the three chart cards (`dashboard-charts` row) animate in with a combined fade + `translateY` + `scale(0.98→1)` effect at 200 / 310 / 420 ms delays.
- **Hover lift on chart cards** — `translateY(-3px)` + box-shadow on hover with dark-mode–aware shadow intensity.
- **Recent Users card entrance** — `.dashboard-recent` row animates in at 520 ms delay; table rows show a left-border accent (`#007bff` / `#4dabf7` dark) on hover.

---

## [3.15.0] - 2026-06-04

### Added

- **Pending Invitations widget** — new `small-box bg-warning` showing the count of active (not used, not expired) invitation tokens. Sourced from `PasswordReset::getPendingInvitationsCount()`. Cache key `pending_invitations` invalidated in `UserController::save()` (invite branch), `UserController::resendInvitationAjax()`, and `InvitationController::acceptInvitation()`.
- **Password Resets This Week widget** — new `small-box bg-secondary` showing reset tokens created in the last 7 days. Sourced from `PasswordReset::getResetRequestsThisWeek()`. Cache key `resets_this_week` invalidated in `PasswordResetController` after `$this->resets->create($userId, 'reset')`.
- **Toggleable access-metrics row** — second widget row (Pending Invitations + Resets This Week) hidden by default; a link above it shows/hides it with a jQuery fade animation (300 ms). Preference persisted in `localStorage` under `dashboard_access_metrics_visible`. Visible only for users with the `users` permission.
- **4 new integration tests** in `PasswordResetTest`: `getPendingInvitationsCount` (active tokens counted, used tokens excluded) and `getResetRequestsThisWeek` (recent tokens counted, returns int).
- **2 new integration tests** in `UserStatsTest`: `getStatistics` and `getUsersByStatus` include `pending` key.

### Fixed

- **Donut chart ignored STATUS_PENDING** — `UserStatsTrait::getUsersByStatus()` and `getStatistics()` previously omitted `status = 2` users. Both methods now include a `pending` key with `SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END)`. The donut chart renders three segments: Active (green `#28a745`), Inactive (red `#dc3545`), Pending (yellow `#ffc107`).
- **Recent Users table missing Pending badge** — status badge in `views/dashboard/index.php` was a 2-case `if/else`; extended to `if/elseif/else` for status 0/1/2 with a `badge-warning` "Pending" label.

### Changed

- **`DashboardController`** — imports `PasswordReset`; loads `pending_invitations` and `resets_this_week` via `DashboardCache::remember()`; passes both to the view.
- **`DashboardCache` invalidation contract** updated in `CLAUDE.md` to document the two new cache keys and their invalidation controllers.
- **Test suite** — 281 tests, 498 assertions (up from 275 / 486 in v3.14.0).

---

## [3.14.0] - 2026-05-27

### Added

- **User invitation by email** — admin creates a user with status=pending; the system sends a 48 h invitation link; the user sets their own password via a standalone accept-invitation page; the account activates automatically on acceptance:
  - `users.status` now has three values: `0` inactive, `1` active, `2` pending (new). `User::STATUS_PENDING` constant added.
  - **`InvitationController`** — two public (no-auth) actions: `showAcceptForm()` validates the token and renders `views/auth/accept_invitation.php`; `acceptInvitation()` sets the password, activates the user, marks the token used, and logs `invitation_accepted`.
  - **`MailService::sendInvitationEmail(string $email, string $token, string $inviterName): bool`** — invitation email via PHPMailer with a 48 h expiry message and `/accept-invitation?token=` link.
  - **`UserController::save()`** — invite branch (POST `invite=1`) stores an unusable pre-hashed placeholder password, sets `STATUS_PENDING`, creates a `PasswordReset` token of type `invitation`, sends the email, and logs `invite`.
  - **`UserController::resendInvitationAjax(int $id)`** — AJAX endpoint; rejects non-pending users; invalidates the previous token, issues a new 48 h token, resends the email, logs `invite_resent`.
  - **`views/auth/accept_invitation.php`** — standalone auth page (anti-FOUC IIFE, dark-mode.css, login-dark.css, theme-toggle.js); password + confirm-password fields with eye-toggle; `card-outline card-success`.
  - **`public/js/modules/auth/accept-invitation.js`** — jQuery Validate rules for password (min 8) and confirm_password (`equalTo`); eye-toggle handlers; loading toast on submit.
  - **Invitation toggle on create-user form** — `custom-switch` hides password fields and sets `invite=1`; jQuery Validate password rule becomes conditional on `$('#invite').val() !== '1'`; status badge updates in real time.
  - **"Pending" badge** — 3-way status badge (Pending/warning, Active/success, Inactive/danger) in `views/users/index.php` and `views/users/show.php`.
  - **"Resend Invitation" button** — in `views/users/index.php` (DataTables row, pending only) and `views/users/show.php` (pending only); AJAX handlers in `index-users.js` and `show-user.js` with `AlertUtils.confirm` → `ToastUtils.loadingWithMinTime` → `location.reload()`.
  - **3 new routes** in `routes/web.php`: `GET /accept-invitation` (guest middleware), `POST /accept-invitation` (no middleware), `POST /users/{id}/resend-invitation` (`auth + perm:users`).
  - **20 integration tests** across 4 new test classes: `PendingUserBlockTest` (5), `InvitationCreateTest` (6), `AcceptInvitationTest` (6), `ResendInvitationTest` (3).

- **Password reset refactored to dedicated table** — tokens moved out of `users` into a dedicated `password_resets` table:
  - **`password_resets` table** — `id`, `user_id FK`, `token CHAR(64)`, `type ENUM('reset','invitation')`, `expires_at DATETIME`, `used_at DATETIME NULL`, `created_at`. Supports multiple concurrent tokens per user.
  - **`App\Models\PasswordReset`** — `create(int $userId, string $type, int $ttlSeconds): array` (generates raw token + SHA-256 hash, inserts row, returns `[token, record]`); `findValidByToken(string $rawToken, string $type): ?array` (SHA-256 hash lookup, expiry + used_at check); `markUsed(int $id): bool`; `invalidatePreviousByType(int $userId, string $type): void`; TTL constants `TTL_RESET = 3600`, `TTL_INVITATION = 172800`.
  - **`PasswordResetController`** updated to use `PasswordReset::create()` and `PasswordReset::findValidByToken()` instead of `users.reset_token` columns.
  - **Legacy `users` columns removed** — `reset_token`, `reset_token_expires`, `reset_token_used` dropped from `schema.sql` and from `User` model / `UserPasswordTrait`.
  - **`minimal_seed.sql`** updated with `TRUNCATE TABLE password_resets`.
  - **5 integration tests** in `PasswordResetTest` + updated `PasswordResetFlowTest` and `PendingUserBlockTest`.

### Changed

- **`AuthController::login()`** — blocks `STATUS_PENDING` users before the `STATUS_INACTIVE` check; both return the same generic warning message to avoid status enumeration.
- **`User::validateData()`** — password validation skipped when `status === STATUS_PENDING` (invitation path sets an unusable placeholder); length check also bypassed for pending creates.
- **`views/users/index.php`** — pending users show a "Resend" action button instead of the toggle-status button.
- **`docs/TESTING.md`**, **`docs/ACCESS_CONTROL.md`**, **`docs/SEEDING.md`**, **`CLAUDE.md`** — updated to reflect the new invitation flow, `password_resets` table, user status values, and extended test suite.

---

## [3.13.1] - 2026-05-26

### Fixed

- **Role required on user create/edit** — the role field now carries `required` validation both client-side (jQuery Validate) and server-side (`User::validateData()`); the placeholder changed from "No role assigned" to "Select a role".
- **jQuery Validate added to permissions modal** — `modal-permission.js` rewritten with jQuery Validate rules (`required`, `maxlength: 60`, `remote` uniqueness check against `/permissions/check-name`), `isSubmitting` guard, and proper reset on modal close. Consistent with the existing validation in the roles modal.
- **`/permissions/check-name` route** — new `POST` endpoint (`Permission@checkName`) added to `routes/web.php`; gated by `auth` middleware.
- **Thin controllers** — extracted validation logic that lived inline in controllers into model methods:
  - `User::validatePasswordChange()` and `User::validateNewPassword()` centralise password validation; called by `ProfileController`, `UserController`, and `PasswordResetController`.
  - `Permission::getUsersWithoutFormatted()` centralises Select2 formatting; `PermissionController::getUsersWithout()` delegates to it.
  - `Role::syncPermissions()` now internally invalidates permission timestamps for all role users after commit; `RoleController` no longer needs to do it manually.

### Refactored

- **`app/Core/Model.php`** — base class now provides fully usable generic CRUD (`find`, `all`, `insert`, `update`, `delete`, `query`, `getLastInsertId`, `trimInput`). All concrete models extend and inherit these; model-specific methods override only where JOINs or custom field handling are needed.
- **`$tabla` → `$table`** — renamed in `User`, `Role`, and `Permission` to match the protected `$table` property declared in the base `Model`. Visibility changed from `private` to `protected` so the base class can reference it.
- **`getLastInsertId()` deduplicated** — removed from `User`, `Role`, and `Permission`; the method now lives only in the base `Model`.
- **`User.php` split into traits** — the 1 270-line model reorganised into three focused traits under `app/Models/Traits/`:
  - `UserAuthTrait` — login queries (`loginByEmail`, `loginByDocumentNumber`), lookup methods (`findByEmail`, `findByDocumentNumber`), uniqueness checks (`emailExists`, `documentTypeExists`, …), and login throttle (`recordFailure`, `clearAttempts`, `unlock`, `getLockStatus`).
  - `UserPasswordTrait` — password reset token lifecycle (`createPasswordResetToken`, `setResetToken`, `getUserByResetToken`, `resetPassword`), remember-me token (`setRememberToken`, `findByRememberToken`, `clearRememberToken`), and `verifyCurrentPassword`.
  - `UserStatsTrait` — dashboard aggregation queries (`getStatistics`, `getRecent`, `getUsersByStatus`, `getUsersByMonth`).
- **`User.php` `forgetUserCaches()`** — dashboard cache invalidation extracted to a private helper called by `create`, `update`, and `updateStatus`, eliminating four repeated `DashboardCache::forget()` calls.

---

## [3.13.0] - 2026-05-26

### Added

- **Dark mode** — system-aware theme toggle with `localStorage` persistence and zero-DB-query design:
  - **Anti-FOUC inline script** in `views/layouts/header.php` (`<head>`, before all CSS) — synchronous IIFE reads `localStorage('theme')` and falls back to `prefers-color-scheme`; adds `dark-mode` to `<html>` before the browser paints any pixel.
  - **`public/css/core/dark-mode.css`** — global dark-mode overrides loaded on every page. Covers: CSS custom-property palette (`--dm-bg`, `--dm-bg-alt`, `--dm-border`, `--dm-text`, `--dm-text-muted`, `--dm-primary/success/info/warning/danger`), sidebar light→dark (CSS-only, no HTML class change), navbar, small-box dashboard cards, Bootstrap semantic colors (btn, badge, links, pagination), DataTables (header, rows, hover, pagination), Select2 (dropdown, options, multi-choice tags), SweetAlert2 popup, and webkit autofill fix (`-webkit-box-shadow` inset trick to suppress the yellow background).
  - **`public/js/modules/profile/theme-toggle.js`** — loaded globally from `footer.php`; handles click toggle (writes `localStorage`), syncs icon (moon ↔ sun), animates the icon with a 360° spin + bounce via `cubic-bezier(0.34, 1.56, 0.64, 1)`, and reacts to OS theme changes in real time (only when no manual preference is set). Loaded after `sweetalert-utils.js`, before `$module_scripts`.
  - **Toggle button** in `views/layouts/header.php` navbar (`#theme-toggle`, `navbar-nav ml-auto`, before fullscreen widget).
  - **Global CSS transition** on `*, *::before, *::after` for smooth `background-color`, `color`, `border-color`, and `box-shadow` interpolation when switching themes.
  - **Auth standalone pages** (`views/auth/login.php`, `forgot_password.php`, `reset_password.php`) — each gets the anti-FOUC IIFE, `dark-mode.css`, `login-dark.css`, a fixed `#theme-toggle` button (`.auth-theme-toggle`, bottom-right to avoid SweetAlert2 toast overlap), and `theme-toggle.js`.
  - **`public/css/modules/login/login-dark.css`** — auth-specific dark overrides: page background gradient, card + card-header, login-card-body, inputs + placeholder + focus ring, input-group icons, iCheck label, links, footer text, and the `.auth-theme-toggle` button (bottom-right, hover opacity).

### Changed

- **`views/layouts/header.php`** — added anti-FOUC script (first element in `<head>`), `dark-mode.css` link (after `ui-components.css`), and `#theme-toggle` nav item.
- **`views/layouts/footer.php`** — added global load of `theme-toggle.js` after `sweetalert-utils.js`.

---

## [3.12.0] - 2026-05-25

### Added

- **Audit Log module** — read-only activity log for all admin actions:
  - `activity_logs` table in `schema.sql`: `id`, `actor_id` (nullable FK → `users`), `actor_label` (snapshot of user display name at event time), `module`, `action`, `description`, `details` (JSON), `ip_address`, `user_agent`, `created_at`.
  - **`App\Services\AuditLogger`** — static service with a single `log(array $data)` method. Reads `actor_id` and `actor_label` from session, resolves client IP from `$_SERVER`, and delegates to `ActivityLog::create()`. Silently ignores write failures. Also calls `DashboardCache::forget('audit_today')` after every successful insert to keep the dashboard count current.
  - **`ActivityLog` model** — append-only: `create()`, `getAll(array $filters)`, `getDistinctModules()`, `getDistinctActions()`, `getActorsWithLogs()`, `countToday()`, `purgeOlderThan(int $days)`.
  - **`AuditLogController`** — read-only index with server-side filter support (module, action, actor, date range).
  - **View `views/audit-log/index.php`** — filter card (collapsed by default) + DataTables table with export buttons; date inputs with calendar icon trigger native picker on mobile.
  - **Partial `views/audit-log/_modal-detail.php`** — event detail modal with dynamic header subtitle (module badge + action code), meta grid, description callout, and human-readable key/value details table (no raw JSON).
  - **`public/js/modules/audit-log/index-audit.js`** — DataTables init, `humanizeKey()` / `renderDetailsTable()` helpers, responsive fix for DataTables child rows (mobile `TypeError` guard).
  - **Route** `GET /audit-log` (middleware: `auth + perm:audit_log`).
  - **Permission `audit_log`** — seeded in `database/seeder.sql`; granted to Administrator via `is_system = 1` (`*`).
  - **Sidebar link** gated by `audit_log` under the Administration section.
  - **Dashboard "Events Today" card** — `DashboardCache::remember('audit_today', ...)` in `DashboardController`; `small-box bg-secondary` visible only when `canViewAuditLog`.
  - **Seeder audit entries** — 17 `activity_logs` rows in `database/seeder.sql` reflecting the full setup timeline (roles, permissions, users, sync, first login).
  - **Instrumentation** — `AuditLogger::log()` called in `AuthController` (login/logout), `UserController` (create/update/delete/status/unlock), `PermissionController` (create/update/delete/assign/revoke), `RoleController` (create/update/delete/sync).

### Changed

- **`DashboardController`** — imports `ActivityLog`, loads `audit_today` via `DashboardCache`, passes `auditToday` and `canViewAuditLog` to the view.
- **`views/dashboard/index.php`** — new `small-box bg-secondary` card (Events Today) rendered after the Roles card, visible only when the user has `audit_log`.

---

## [3.11.0] - 2026-05-24

### Added

- **Brute-force login throttling** — lockout after N consecutive failed login attempts with automatic lazy unlock by time and manual admin unlock:
  - 3 new columns on `users` table: `login_attempts INT NOT NULL DEFAULT 0`, `locked_until DATETIME NULL`, `last_attempt_at DATETIME NULL`.
  - 2 new `.env` variables: `LOGIN_MAX_ATTEMPTS` (default 5) and `LOGIN_LOCKOUT_MINUTES` (default 15).
  - **`User` model** — 4 new throttle methods: `recordFailure(int $userId)` (single UPDATE that increments counter and sets `locked_until` on threshold), `clearAttempts(int $userId)` (reset after successful login), `unlock(int $userId)` (admin manual unlock, returns bool), `getLockStatus(int $userId)` (lazy evaluation of `locked_until` vs `NOW()`, no write).
  - **`User` model** — 2 new lookup methods: `findByEmail(string $email)` and `findByDocumentNumber(string $documentNumber)` — resolve the user row without verifying the password, enabling the throttle check before `password_verify`.
  - **`App\Services\LoginThrottleService`** — orchestration layer: `isLocked(array $user)` (returns `locked`, `remaining_seconds`, `message`), `registerFailure`, `clearOnSuccess`, `unlock`, `formatRemaining(int $seconds)` (human-readable string, singular/plural).
  - **`AuthController::login()`** refactored into 5 explicit steps: resolve user → throttle check → `password_verify` → status check → session init + clear attempts. Locked requests never reach `password_verify` (no timing leak). Email/document lookup failures are silent (do not reveal user existence).
  - **Admin manual unlock** — `POST /users/{id}/unlock-login` route (middleware `auth + perm:users`), `UserController::unlockLoginAjax()` endpoint (CSRF + `jsonResponse`), locked badge in `views/users/show.php` (conditionally shown when `locked_until > NOW()`), and AJAX handler in `show-user.js` (`AlertUtils.confirm` → `ToastUtils.loadingWithMinTime` → `location.reload()`).
- **19 integration tests** in `tests/Integration/Auth/LoginThrottleTest.php` — covers `recordFailure` (counter, `last_attempt_at`, `locked_until` threshold), `getLockStatus` (active, expired, fresh), `clearAttempts`, `unlock`, `LoginThrottleService::isLocked` (locked/unlocked/expired), and `formatRemaining` (minutes+seconds, singular, zero).

### Changed

- **`AuthController`** — uses `LoginThrottleService` injected in constructor; login flow split into `findByEmail`/`findByDocumentNumber` (resolve) + `password_verify` (verify) instead of the previous single `loginByEmail`/`loginByDocumentNumber` call.
- **`UserController`** — injects `LoginThrottleService` for the unlock endpoint.

---

## [3.10.0] - 2026-05-21

### Added

- **`role_permissions` pivot table** — `(id, role_id FK, permission_id FK, UNIQUE(role_id, permission_id))` in `schema.sql`; `ON DELETE CASCADE` on both FK. Enables role-level permission inheritance without replacing per-user assignments.
- **`roles.is_system` column** — `tinyint(1) NOT NULL DEFAULT 0`. Roles with `is_system = 1` (Administrator) cannot be deactivated or deleted via the UI; `Auth::isAdmin()` now derives from this flag instead of the role name.
- **Role↔permission assignment UI** — `GET /roles/{id}` detail page (`views/roles/detail.php`) with a checkbox list of all active permissions; `POST /roles/sync-permissions` AJAX endpoint (`RoleController::syncPermissions`) with CSRF, server-side validation, and cache invalidation for all users of that role.
- **`Role` model methods** — `getAssignedPermissionIds(int $roleId)`, `getPermissionNames(int $roleId)` (active only), `syncPermissions(int $roleId, array $permissionIds)` (DELETE+INSERT transaction), `getUserIdsByRole(int $roleId)`.
- **Permission UNION in `Auth`** — `Auth::resolvePermNames()` private helper merges direct `user_permissions` + role `role_permissions` via `array_unique(array_merge(...))`. Used in `Auth::login()`, `Auth::attemptRememberLogin()`, and `Auth::refreshPermissionsIfStale()`. Returns `[]` (no DB query) when `role_id` is `null`.
- **`role_id` in user forms** — `UserController::create()` and `edit()` inject `$activeRoles`; `prepareUserData()` maps `role_id` with `PDO::PARAM_NULL` when empty. `views/users/create.php` and `views/users/update.php` include a Select2 role selector.
- **Role column in user list** — `User::getAll()` includes `LEFT JOIN roles r ON u.role_id = r.id` and exposes `r.name AS role_name`; `views/users/index.php` shows the Role column (N/A for users without a role).
- **Dashboard "Total Roles" card** — `DashboardController` loads `role_stats` via `DashboardCache::remember('role_stats', ...)` and passes `canManageRoles`; the fourth stat card (bg-info) shows total roles with a "Manage" link for users with `roles`.
- **`public/js/modules/roles/detail-role.js`** — checkbox sync handler with `ToastUtils.loadingWithMinTime` and CSRF rotation.
- **Integration tests** — `RolePermissionTest` (11 cases: sync, idempotency, inactive-permission filtering, getUserIdsByRole); `AuthIntegrationTest` extended with 3 UNION cases (merge, dedup, empty); `UserTest` extended with 4 cases (create/update with role_id, NULL role_id, getAll JOIN).

### Changed

- **`Auth::isAdmin()`** — now reads `$_SESSION['user_is_admin']` (bool, set from `roles.is_system` at login) instead of comparing `$_SESSION['user_position']` to `'administrator'`. The role name can be changed freely without breaking admin detection.
- **`Auth::login()`** — sets `$_SESSION['user_is_admin']` and `$_SESSION['user_role']` (role display name) from the JOIN result; removes `$_SESSION['user_position']`.
- **`Auth::logout()`** — removed the `int $userId` parameter; reads `$_SESSION['user_id']` internally to avoid a zero-ID query when the session is empty.
- **`Auth::checkTimeout()`** — fail-closed: a session without `last_access` is destroyed and returns `false` immediately.
- **`Auth::checkSecurity()`** — fail-closed: a session without `ip` or `user_agent` is destroyed and returns `false` immediately.
- **`AuthMiddleware`** — calls `Auth::refreshPermissionsIfStale()` after a successful `attemptRememberLogin()` before returning.
- **`AuthController`** login query includes `LEFT JOIN roles r ON u.role_id = r.id` to supply `role_name` and `role_is_system`.
- **`Role` mutations** (`create`, `update`, `updateStatus`, `syncPermissions`) all call `DashboardCache::forget('role_stats')`.
- **`tests/fixtures/sql/minimal_seed.sql`** — roles inserted before users to satisfy the FK constraint (required for MySQL 8.0 in CI); added `role_permissions` seed row (Editor → `users`).
- **`docs/ACCESS_CONTROL.md`**, **`docs/SEEDING.md`**, **`docs/TESTING.md`**, **`CLAUDE.md`**, **`README.md`**, **`PROMPTS.md`**, **`CONTRIBUTING.md`** — updated to reflect the two-level permission model, new session keys, system roles, and the extended test suite.

### Removed

- **`users.position` column** — the "position" field mixed display cargo with the admin flag. Both concerns are now covered by `roles.name` (display) and `roles.is_system` (admin detection). `$_SESSION['user_position']` removed from all session writes and reads.

---

## [3.9.0] - 2026-05-20

### Added

- **`roles` table** — `id`, `name` (varchar 60, unique), `description` (varchar 255 nullable), `status` (tinyint default 1), timestamps. Created before `users` in `schema.sql` to satisfy the FK constraint.
- **`role_id` FK on `users`** — `INT DEFAULT NULL` column + `CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)` in `schema.sql`.
- **`roles.manage` permission** — inserted in `database/seeder.sql` and granted to admin in the permission matrix.
- **`App\Models\Role`** — `getAllWithUserCount()`, `getById()`, `getAllActive()`, `getStatistics()`, `nameExists()`, `create()`, `update()`, `updateStatus()`, `countUsers()`, `getLastInsertId()`. All mutation methods call `DashboardCache::forget('user_stats')` and `DashboardCache::forget('users_by_status')`.
- **`App\Controllers\Roles\RoleController`** — `pageIndex()`, `create()`, `update()`, `toggleStatus()`, `checkName()`. CRUD via AJAX + shared modal, logical delete only (no physical DELETE).
- **5 routes** in `routes/web.php`: `GET /roles`, `POST /roles/create`, `POST /roles/update`, `POST /roles/toggle-status`, `POST /roles/check-name`. All gated by `auth + perm:roles.manage` (check-name uses `auth` only, matching the `check-email` pattern).
- **`views/roles/index.php`** — info-boxes (Total/Active/Inactive), DataTables list with ID, Name, Description, Users, Status, Actions columns; Edit and Toggle-status buttons per row.
- **`views/roles/_modal_role.php`** — shared create/edit modal. Status field intentionally omitted — status is managed exclusively via the toggle button in the table.
- **`public/js/modules/roles/index-roles.js`** — DataTables init, open-modal-in-create-mode handler, toggle-status handler with client-side guard (blocks deactivation when users are assigned).
- **`public/js/modules/roles/modal-role.js`** — jQuery Validate (name required + maxlength 60 + remote uniqueness check, description maxlength 255), `isSubmitting` guard, open-in-edit-mode handler, AJAX submit, reset on modal close.
- **Sidebar entry** for Roles in `views/layouts/header.php`, gated by `Auth::hasPermission('roles.manage')`. Administration block guard updated to show when `roles.manage` is present.
- **`tests/Integration/Models/RoleTest.php`** — 19 integration tests covering all `Role` model methods. Status assertions use `(int)` cast to handle native PHP types from PDO with `ATTR_EMULATE_PREPARES => false`.
- **`tests/fixtures/sql/minimal_seed.sql`** — added `TRUNCATE TABLE roles`, `ALTER TABLE users ADD COLUMN IF NOT EXISTS role_id INT DEFAULT NULL`, and two seed roles (Editor active, Auditor inactive).

### Changed

- **`docs/SEEDING.md`** — added `roles.manage` column to the permission matrix (admin ✅, others ❌).
- **`CLAUDE.md`**, **`README.md`** — version bumped to 3.9.0; Controllers/ architecture updated to include `Roles/`; Features list and integration suite description updated.

### Fixed

- **`tests/fixtures/sql/minimal_seed.sql`** — removed `ALTER TABLE users ADD COLUMN IF NOT EXISTS role_id` (MariaDB-only syntax, incompatible with MySQL 8.0 used in CI). The column is already present in `schema.sql`, making the `ALTER` redundant.

---

## [3.8.0] - 2026-05-19

### Added

- **Dashboard metrics** — three Chart.js visualizations on the dashboard:
  - Donut chart: active vs inactive users
  - Bar chart: top 5 permissions by assigned users
  - Line chart: user registrations over the last 6 months
- **`App\Services\DashboardCache`** — session-based cache for dashboard metrics with configurable TTL (`DASHBOARD_CACHE_TTL` in `.env`, default 300 s) and event-driven invalidation. API: `get`, `put`, `remember`, `forget`, `flush`.
- **`User::getUsersByStatus()`** — returns active/inactive counts for the donut chart.
- **`User::getUsersByMonth(int $months)`** — returns registration counts per month, zero-filled for empty months.
- **`Permission::getTopAssigned(int $limit)`** — returns top N active permissions by assigned user count.
- **`DASHBOARD_CACHE_TTL`** environment variable in `.env.example`.
- **11 unit tests** for `DashboardCache` (`tests/Unit/Services/DashboardCacheTest.php`).
- **13 integration tests** for the new model methods (`UserDashboardTest`, `PermissionDashboardTest`).
- **8 integration tests** for cache invalidation (`DashboardCacheInvalidationTest`) — verifies that model mutations clear the correct cache keys.

### Changed

- **`DashboardController::index()`** — all data fetched via `DashboardCache::remember()`; passes `usersByStatus`, `topPerms`, and `usersByMonth` to the view; loads `chart` plugin and `dashboard/index-dashboard` module script.
- **`User::create()`, `update()`, `updateStatus()`** — call `DashboardCache::forget()` on the user-related keys after a successful write.
- **`Permission::create()`, `update()`, `updateStatus()`** — call `DashboardCache::forget()` on the permission-related keys after a successful write.
- **`PermissionController::assignUser()`, `revokeUser()`** — call `DashboardCache::forget()` on `perm_stats` and `top_permissions` after a successful operation.
- **`views/dashboard/index.php`** — added three chart cards; data passed to JS via `data-*` attributes on canvas elements (no `<script>` blocks in views).
- **`docs/TESTING.md`** — updated unit and integration suite tables with the 3 new test classes.
- **`docs/AJAX_AND_MODULES.md`** — added "Passing PHP data to JS (non-AJAX)" section documenting the `data-*` attribute pattern.
- **`CLAUDE.md`**, **`PROMPTS.md`** — added `DashboardCache` section, updated module list and version references.
- **`README.md`**, **`CONTRIBUTING.md`** — updated features, config example, and architecture references to include `DashboardCache`.

---

## [3.7.0] - 2026-05-16

### Added

- **`public/js/core/sweetalert-utils.js`** — centralised SweetAlert2 utility layer:
  - `ToastUtils`: `success`, `error`, `warning`, `info`, `loading`, `loadingWithMinTime(message, action, minMs)` — guarantees a minimum display time before running the async action, eliminating the flash-of-loading pattern
  - `AlertUtils`: `confirm(title, text, onConfirm, options)` (supports `options.html` for rich body text), `confirmDelete`, `welcome(name)` (animated login popup), `image(src, alt, confirmButtonText)` (lightbox viewer)

### Changed

- **`views/layouts/footer.php`** — loads `sweetalert-utils.js` globally after `ui-components.js`; all module scripts now have `ToastUtils` and `AlertUtils` available without extra imports
- **`views/layouts/messages.php`** — rewritten to use `ToastUtils[icon](message)` for flash messages and `AlertUtils.welcome(name)` for `$_SESSION['welcome_user']`; removed nested `if` structure
- **`app/Controllers/Auth/AuthController.php`** — login success now sets `$_SESSION['welcome_user']` instead of `$_SESSION['message']` + `$_SESSION['icon']`, triggering the dedicated welcome popup
- **All JS modules** — removed every inline `Swal.fire()` call; `ToastUtils.loadingWithMinTime()` replaces bare `Swal.showLoading()` + `setTimeout` patterns; `AlertUtils.confirm()` replaces inline confirm dialogs:
  - `public/js/modules/auth/login.js`, `forgot_password.js`, `reset_password.js` — submit handlers use `loadingWithMinTime` with descriptive messages
  - `public/js/modules/users/create-user.js`, `update-user.js` — form submit uses `loadingWithMinTime`
  - `public/js/modules/users/index-users.js` — toggle status uses `AlertUtils.confirm` + `loadingWithMinTime`
  - `public/js/modules/users/profile-user.js` — password change and profile update use `loadingWithMinTime`; password change success shows `ToastUtils.success` before redirecting to logout
  - `public/js/modules/users/show-user.js` — profile image lightbox uses `AlertUtils.image`
  - `public/js/modules/permissions/index-permissions.js` — toggle status uses `AlertUtils.confirm` + `loadingWithMinTime`
  - `public/js/modules/permissions/modal-permission.js` — modal closed before `loadingWithMinTime` (modal-first pattern to avoid z-index conflicts)
  - `public/js/modules/permissions/detail-permission.js` — assign and revoke use modal-first pattern + `loadingWithMinTime`; revoke confirm uses `options.html` for rich body text
- **`views/auth/login.php`**, **`forgot_password.php`**, **`reset_password.php`** — include `sweetalert-utils.js` manually (auth views do not use `footer.php`)
- **`views/users/index.php`** — corrected Spanish tooltips (`"Ver usuario"` → `"View user"`, `"Editar usuario"` → `"Edit user"`)
- **`views/permissions/detail.php`** — reordered action buttons (Edit before Back)
- **`docs/AJAX_AND_MODULES.md`** — added SweetAlert2 utilities section documenting `ToastUtils`, `AlertUtils`, `loadingWithMinTime` pattern, and modal-first pattern; updated flash message section with `$_SESSION['welcome_user']`; updated load order to include `sweetalert-utils.js`
- **`CLAUDE.md`**, **`PROMPTS.md`** — updated conventions and prompt templates to reflect `ToastUtils`/`AlertUtils` as the required SweetAlert2 interface

### Removed

- All direct `Swal.fire()` calls from module scripts — `sweetalert-utils.js` is the single declaration point for all SweetAlert2 behavior
- `console.error` and `console.log` calls from all JS modules
- `docs/SWEETALERT_MIGRATION.md` — temporary migration plan, no longer needed

---

## [3.6.0] - 2026-05-13

### Added

- **PHPUnit 11 test suite** — two independent suites with 115 tests and 158 assertions:
  - `Unit` suite (`tests/Unit/`) — covers `App\Core\Auth`, `App\Core\Router`, `app/Core/helpers.php`, and `App\Services\ImageService`. No DB required; runs in < 2 s.
  - `Integration` suite (`tests/Integration/`) — covers `App\Models\User`, `App\Models\Permission`, and cross-model `Auth` flows (`refreshPermissionsIfStale`, `attemptRememberLogin`). Uses a dedicated test DB with per-test transaction rollback for isolation.
- `tests/bootstrap.php` — loads Composer autoloader, defines `BASE_PATH`/`APP_PATH` constants, and buffers output to prevent "headers already sent" noise in CLI.
- `tests/TestCase.php` — base class for all suites: resets `$_SESSION`, `$_POST`, `$_GET`, `$_COOKIE`, and snapshots/restores `$_SERVER` around each test.
- `tests/IntegrationTestCase.php` — loads `.env.testing` via phpdotenv, resets the `Connection` singleton via reflection, runs schema + seed once per suite, and wraps each test in a `beginTransaction` / `rollBack` cycle.
- `tests/fixtures/images/` — 50×50 JPEG, 80×40 PNG (with alpha), and a corrupt `.txt` for `ImageService` tests.
- `tests/fixtures/sql/minimal_seed.sql` — minimal dataset (1 admin, 1 editor, 2 permissions, 1 assignment) for fast integration test runs.
- `.env.testing.example` — template for local test DB configuration.
- **GitHub Actions workflow** (`.github/workflows/tests.yml`) — two jobs on every push/PR:
  - `unit` — installs PHP 8.2 + extensions, runs `phpunit --testsuite=Unit` (no DB).
  - `integration` — spins up a MySQL 8.0 service container, loads the schema, generates `.env.testing` at runtime, and runs `phpunit --testsuite=Integration`.

### Changed

- `composer.json` — added `phpunit/phpunit ^11.0` to `require-dev`, `Tests\\` PSR-4 mapping to `autoload-dev`, and `test`, `test:unit`, `test:integration` scripts.
- `.gitignore` — added `/.phpunit.cache/`, `/.phpunit.result.cache`, `/tests/coverage/`, `/.env.testing`; added `!tests/fixtures/sql/minimal_seed.sql` exception to the `*.sql` exclusion rule.

---

## [3.5.0] - 2026-05-12

### Changed

- All `app/` subdirectories renamed to PascalCase (`config/` → `Config/`, `controllers/` → `Controllers/`, `core/` → `Core/`, `middleware/` → `Middleware/`, `models/` → `Models/`, `services/` → `Services/`) to align with PSR-4 standard.
- `composer.json` — `autoload.psr-4` mapping `"App\\": "app/"` now resolves all `App\*` classes natively; `autoload.files` path updated to `app/Core/helpers.php`.
- `app/Config/config.php` — removed `require_once autoload.php`; Composer handles all autoloading.
- `public/index.php` — removed hardcoded `require_once app/Core/Router.php`; resolved via Composer PSR-4.
- `README.md`, `CLAUDE.md`, `CONTRIBUTING.md`, `PROMPTS.md` — directory references updated to PascalCase.

### Removed

- `app/Config/autoload.php` — custom `spl_autoload_register` implementation fully replaced by Composer native PSR-4.

---

## [3.4.0] - 2026-05-12

### Added

- **Composer** introduced as dependency manager. `vendor/autoload.php` coexists with the custom PSR-4 autoloader — Composer handles third-party packages, the custom autoloader handles `App\*` classes.
- **`phpmailer/phpmailer` v6.12** — replaces the manually vendored `libs/PHPMailer-master/`.
- **`vlucas/phpdotenv` v5.6** — replaces the hand-rolled `app/config/env.php` parser. `env()` helper moved to `app/core/helpers.php` with identical type-casting semantics.
- **`tecnickcom/tcpdf` v6.11** — replaces the manually vendored `libs/TCPDF-main/`.

### Changed

- `app/config/config.php` — loads `vendor/autoload.php` (conditional) before the custom autoloader; bootstraps phpdotenv via `Dotenv::createImmutable()->safeLoad()`.
- `app/core/helpers.php` — `env()` global helper added; CSRF functions unchanged.
- `app/services/MailService.php` — removed three `require_once` lines pointing to `libs/PHPMailer-master/src/`; resolved via Composer autoloader.
- `public/index.php` — removed redundant `require_once helpers.php` (now loaded inside `config.php`).
- `README.md` — `composer install` added as step 2 of the installation guide.

### Removed

- `libs/PHPMailer-master/` — replaced by `phpmailer/phpmailer` Composer package.
- `libs/TCPDF-main/` — replaced by `tecnickcom/tcpdf` Composer package.
- `libs/` directory — fully removed; no vendored libraries remain.
- `app/config/env.php` — replaced by `vlucas/phpdotenv` + `env()` wrapper in `helpers.php`.

---

## [3.3.0] - 2026-05-11

### Added

- **`App\Core\Auth`** — static hub that centralises all authentication, session, remember-me, and permission-cache concerns. Replaces `AuthorizationService` and `RememberMeService` as the single source of truth for auth state.
  - Session state: `check()`, `id()`, `user()`, `isAdmin()`, `hasPermission()`, `permissions()`
  - Login / logout lifecycle: `login()`, `logout()`
  - Session validation: `checkTimeout()`, `checkSecurity()`
  - Permission cache: `refreshPermissionsIfStale()`
  - Remember-me: `issueRememberCookie()`, `attemptRememberLogin()`, `clearRememberCookie()`

- **`App\Core\ErrorHandler`** — handles 403, 404, and 500 responses; auto-detects AJAX via `X-Requested-With` and returns JSON or the HTML error view.

- **`Permission::assign(int $userId, int $permissionId)`** and **`Permission::revoke()`** — idempotent single-row operations on `user_permissions`; replace the equivalent methods that were in `AuthorizationService`.

- **`User::createPasswordResetToken(string $email)`** — generates token + expiry and delegates to `setResetToken()`; returns the raw token or `null`.

### Changed

- `AuthMiddleware`, `GuestMiddleware`, `PermissionMiddleware` — all calls to old helper functions and `AuthorizationService` replaced with `Auth::` static methods.
- `AuthController` — `initSession()` private method removed; replaced with `Auth::login()`. `logout()` now delegates entirely to `Auth::logout()`. Remember-me cookie issued via `Auth::issueRememberCookie()`.
- `PasswordResetController` — uses `User::createPasswordResetToken()` instead of inline token generation.
- `ProfileController` — now extends `App\Core\Controller`; removed `global $URL` and bare `$_SESSION` auth checks; uses `Auth::check()` / `Auth::id()`.
- `DashboardController` — permission checks use `Auth::hasPermission()` instead of `AuthorizationService`.
- `PermissionController` — `assignUser()` and `revokeUser()` use `Permission::assign()` / `Permission::revoke()` instead of `AuthorizationService`.
- `UserController` — permission sync uses `Permission::syncForUser()`.
- `Controller::render()` — `$currentUser` populated via `Auth::user()`; `$authService` variable removed.
- `Controller::requireLogin()` / `requirePermission()` — use `Auth::check()` and `Auth::hasPermission()`.
- `views/layouts/header.php` — navigation permission gates use `\App\Core\Auth::hasPermission()`.
- `app/core/helpers.php` — reduced to three CSRF functions only (`generateCSRFToken`, `verifyCSRFToken`, `regenerateCSRFToken`). All session/auth helpers removed.
- `app/core/Model.php` — `sanitizeData()` renamed to `trimInput()` for clarity.

### Removed

- `app/services/AuthorizationService.php` — fully absorbed into `App\Core\Auth` (session/permission checks) and `App\Models\Permission` (persistence).
- `app/services/RememberMeService.php` — fully absorbed into `App\Core\Auth`.
- `helpers.php` global functions: `isAuthenticated()`, `checkSessionTimeout()`, `checkSessionSecurity()`, `getCurrentUser()`, `refreshPermissionsIfStale()`, `tryAutoLoginFromRememberCookie()`.

## [3.2.0] - 2026-05-01

### Added

- **Remember Me** — persistent login via secure cookie (`RememberMeService`)
  - Token: 64-char hex (`random_bytes(32)`), stored as SHA-256 hash in `users.remember_token`; plain token goes only in the cookie — never in the DB
  - Cookie: `HttpOnly`, `SameSite=Lax`, `Secure` when HTTPS; 30-day lifetime by default
  - Token rotated on every successful auto-login to mitigate cookie theft
  - Deactivated users (`status = 0`) and expired tokens blocked at query level
  - `app/services/RememberMeService.php` — `issue()`, `attemptLogin()`, `clear()`
  - `views/auth/login.php` — "Remember me" checkbox using `icheck-primary`

- **Configurable session lifetime** — `SESSION_LIFETIME` in `.env` (default `1800` s); `checkSessionTimeout()` reads it per-request instead of a hardcoded value

- **New `.env` variables**

  | Variable                  | Default       | Description                                   |
  | ------------------------- | ------------- | --------------------------------------------- |
  | `SESSION_LIFETIME`        | `1800`        | Seconds of inactivity before session expires  |
  | `REMEMBER_ME_LIFETIME`    | `2592000`     | Seconds the persistent cookie lasts (30 days) |
  | `REMEMBER_ME_COOKIE_NAME` | `remember_me` | Cookie name                                   |

- **New DB columns** on `users`:
  - `remember_token CHAR(64) NULL DEFAULT NULL`
  - `remember_token_expires DATETIME NULL DEFAULT NULL`

- **`tryAutoLoginFromRememberCookie()`** global helper in `app/core/helpers.php`

### Changed

- `AuthMiddleware` — attempts auto-login from remember-me cookie before redirecting to login; active sessions also fall back to cookie when timeout/security check fails
- `GuestMiddleware` — attempts auto-login from cookie before showing login; redirects to `/` if cookie is valid
- `AuthController::logout()` — clears remember token in DB and expires cookie before destroying the session
- `User::checkSessionTimeout()` — signature changed from `int $timeout = 86400` to `?int $timeout = null`; reads `SESSION_LIFETIME` from `.env` when no argument is passed (backwards-compatible)
- `User` model — added `setRememberToken()`, `findByRememberToken()`, `clearRememberToken()`
- `database/schema.sql` — `remember_token` and `remember_token_expires` columns added to `CREATE TABLE users`
- `docs/ACCESS_CONTROL.md` — updated to document remember-me flow and configurable session lifetime

## [3.1.0] - 2026-04-27

⚠️ **BREAKING CHANGES - Migration Required**

This release introduces a major architectural refactoring from scattered endpoint files to a centralized Front Controller pattern with proper MVC structure.

### Migration Guide

If upgrading from v3.0.x, follow these steps:

1. **Update Apache Configuration**
   - Set DocumentRoot to `public/` directory
   - OR add `.htaccess` rewriting rules (included)
   - Restart Apache

2. **URL Structure Changes**
   - Before: `/app/controllers/users/create_user.php`
   - After: `/users/create` (routed through `public/index.php`)
   - Update all frontend navigation and API calls

3. **Remove Direct File Access**
   - No longer access `/app/controllers/*` directly
   - All requests must go through the router

### Added

- **Front Controller Pattern** (`public/index.php`)
  - Single entry point for all requests
  - Centralized request dispatching via `App\Core\Router`

- **Centralized Routing** (`routes/web.php`)
  - All routes defined in single configuration file
  - Clean URL syntax: `/users/edit/5`
  - Supports HTTP method matching

- **Core Classes**
  - `App\Core\Controller` - Base controller with `render()`, `redirect()`, `jsonResponse()`
  - `App\Core\Model` - Base model with PDO database methods
  - `App\Core\Router` - Dynamic route dispatcher

- **Apache Configuration** (`.htaccess` in public/)
  - URL rewriting for clean routes
  - Automatic routing to `public/index.php`

- New developer docs:
  - `docs/SEEDING.md` (seeded users, permission matrix, rerun behavior)
  - `docs/ACCESS_CONTROL.md` (session guard + permission cache flow)
  - `docs/AJAX_AND_MODULES.md` (AJAX endpoint and frontend module conventions)

### Changed

- Consolidated 18+ legacy endpoint files into controller methods
- Renamed `BaseModel.php` → `Model.php` in `app/core/`
- Updated all controllers to extend `App\Core\Controller`
- Updated all models to extend `App\Core\Model`
- Reorganized views with layout wrappers
- Updated all JavaScript AJAX calls to use new routing
- `database/seeder.sql` expanded with a realistic multi-user dataset for manual QA (active/inactive users and role-oriented permission assignments)
- `database/seeder.sql` is now idempotent for local re-imports by truncating `user_permissions`, `users`, and `permissions` before insert
- `README.md` now links to seeding and developer docs for faster onboarding

### Removed

- Legacy endpoint files:
  - `app/controllers/auth/{login.php, logout.php, forgot_password_process.php, reset_password_process.php}`
  - `app/controllers/users/{create_user.php, update_user.php, ajax_change_password.php, check_email.php, check_document.php, toggle_user_status.php, process_update_profile.php}`
  - `app/controllers/users/{UserPageController.php, ProfileController.php}`
  - `app/controllers/permissions/{*_ajax.php, PermissionPageController.php}` (7 files)
  - `app/controllers/dashboard/DashboardPageController.php`

- Legacy core classes:
  - `app/core/BaseController.php`
  - `app/core/BaseModel.php`
  - `app/core/ViewRenderer.php`

### Fixed

- Direct file access security concerns
- Inconsistent routing patterns
- Missing request method validation

## [3.0.0] - 2026-04-14

### Added

- New app-layer base components: `app/core/BaseController.php`, `app/core/ViewRenderer.php`, and `app/core/AssetRegistry.php` to support incremental MVC migration without breaking legacy routes
- New page controllers for view-model preparation: `app/controllers/users/UserPageController.php` and `app/controllers/permissions/PermissionPageController.php`

### Changed

- `app/config/autoload.php` now resolves `App\...` classes explicitly as the primary bootstrap autoloader
- Layout plugin assets were centralized through `AssetRegistry` (`views/layouts/header.php`, `views/layouts/footer.php`) to remove duplicated inline plugin maps
- Users and Permissions views (`index`, `show`/`update`, `detail`) now consume page-controller view models instead of embedding request validation and flow control logic directly in views
- Action endpoints moved from root `controllers/*` to `app/controllers/*`, and frontend form/AJAX routes were updated to the new paths
- Documentation updated to describe the app-only structure (`app/controllers`, `app/models`, `app/services`, `app/config`)

## [2.3.1] - 2026-04-13

### Added

- jQuery Validate support in auth views (`views/auth/login.php`, `views/auth/forgot_password.php`, `views/auth/reset_password.php`) by loading `jquery.validate`, `additional-methods`, and `public/js/core/common-validate.js` for consistent client-side validation

### Changed

- Auth form scripts (`public/js/modules/auth/login.js`, `forgot_password.js`, `reset_password.js`) now use `$('#form').validate({ ... submitHandler })` instead of manual validation checks, while preserving loading toasts/spinners on submit
- Auth form markup now wraps each input as `form-group > input-group`, aligning Bootstrap validation feedback with the same pattern used in the rest of the app
- `public/css/modules/login/login.css` now relies on global validation styles (removed local `.is-valid`/`.is-invalid` overrides) and adjusts validation spacing for cleaner rendering

### Fixed

- Removed redundant Select2 initialization from `public/js/modules/permissions/detail-permission.js`; modal Select2 now depends on centralized initialization to avoid duplicate setup

## [2.3.0] - 2026-04-12

### Added

- **Password Reset System**: New workflow to recover accounts via email (`forgot_password.php`, `reset_password.php`)
- `Services\MailService` — handles SMTP email delivery using PHPMailer
- `libs/PHPMailer-master` — integrated PHPMailer as the primary mailing engine
- SMTP configuration support in `.env` (`MAIL_HOST`, `MAIL_PORT`, `MAIL_USER`, `MAIL_PASS`, etc.)

### Changed

- **Directory Restructuring**: Renamed `views/login/` to `views/auth/` for better semantic organization
- **Modular JavaScript**: Extracted inline scripts from auth views to `public/js/modules/auth/` (`login.js`, `forgot_password.js`, `reset_password.js`)
- **UX Improvements**: Login and password reset buttons now disable and show a spinner icon (`fa-spinner fa-spin`) during form submission to prevent duplicate requests
- **Enhanced Debugging**: `PasswordResetController` now provides a specific error message if an email is not found when `DEBUG=true` is set in `.env`

## [2.2.0] - 2026-04-11

### Added

- `public/js/core/ui-components.js` — `ComponentUtils` module: centralizes Select2 and tooltip initialization with a single `ComponentUtils.initAll()` call on `DOMContentLoaded`; `initSelect2(options, selector)` and `initTooltips()` are also exposed individually for modules that need targeted initialization
- `public/css/core/ui-components.css` — companion stylesheet loaded globally via `header.php`
- **Permissions index**: new "Description" column in the permissions table showing the permission description or "N/A"
- `data-toggle="tooltip"` added to action buttons in `users/index.php`, `permissions/index.php`, and `permissions/detail.php` — tooltips are now initialized automatically by `ComponentUtils`

### Changed

- `views/permissions/_modal_permission.php` now includes the "Assign User" modal (previously inlined in `detail.php`), keeping `detail.php` free of duplicate markup
- `public/js/core/common-utils.js` — `initializeSelect2()` now delegates to `ComponentUtils.initSelect2()` for backwards compatibility; `refreshSelect2()` and `initializeTooltips()` removed (functionality covered by `ComponentUtils`)
- `create-user.js` / `update-user.js` — removed manual `initializeSelect2()` and `$('[data-toggle="tooltip"]').tooltip()` calls; auto-initialization handled by `ComponentUtils.initAll()`

### Fixed

- **Users — `second_surname` stored as empty string**: `UserController::prepareUserData()` now returns `null` for an empty `second_surname`; `User::create()` and `User::update()` bind it with `PDO::PARAM_NULL` when empty, consistent with the existing pattern for `address`, `phone`, and other nullable fields
- **Permissions — `description` stored as empty string**: `Permission::create()` and `Permission::update()` now use `!empty()` (instead of `?? null`, which does not catch `''`) and bind `null` with `PDO::PARAM_NULL` when the description is absent or blank

## [2.1.0] - 2026-04-01

### Added

- `docs/AI_SETUP.md` documentation for configuring AI assistants like Claude Code.
- `.claude/settings.example.json` to share recommended bash permissions for Claude Code.
- `.mcp.example.json` to share recommended Model Context Protocol (MCP) servers (GitHub, MySQL, ClickUp).
- Added `.claude/*` to `.gitignore` while explicitly allowing `!.claude/skills/` and `!.claude/settings.example.json`.

## [2.0.1] - 2026-03-29

### Fixed

- **Permissions — duplicate name**: Added `UNIQUE KEY (name)` to the `permissions` table in `schema.sql`; `Permission::create()` and `Permission::update()` now catch SQLSTATE `23000` and return a user-friendly message instead of exposing the raw PDO exception
- **Permissions — inactive guard**: The "Assign User" button is now hidden and a contextual warning banner is displayed when viewing the detail of an inactive permission, preventing assignments that would silently fail
- **Permissions — revoke double-submit**: The revoke button in `detail-permission.js` is now disabled immediately after the SweetAlert2 confirmation, preventing concurrent AJAX calls on rapid clicks; re-enabled on server error
- **Permissions — session self-assign**: `assign_user_permission_ajax.php` no longer re-fetches all user permissions on self-assign; it appends the single new permission via `Permission::getById()`, saving a full JOIN query
- **JS error messages**: Normalized both network-error strings in `detail-permission.js` to match the rest of the modules: `'A communication error occurred with the server.'`

### Security

- **XSS in `get_users_without_permission_ajax.php`**: Both `htmlspecialchars()` calls now use `ENT_QUOTES, 'UTF-8'` flags, preventing attribute-context injection for user names and positions

## [2.0.0] - 2026-03-19

### Changed

- Full codebase translation from Spanish to English: DB schema/columns, model/service/controller class and method names, session keys, file and directory names (`usuarios/` → `users/`, `permisos/` → `permissions/`), all UI strings, JS modules, and documentation
- `config/conexion.php` renamed to `config/connection.php`; class `Conexion` renamed to `Connection`; property `$conexion` renamed to `$connection` in all models and services
- Dashboard (`index.php`): replaced placeholder cards with 4 AdminLTE stat widgets (total/active users and permissions pulled from the DB) and a recent users table; table hides Email on xs and Registered on xs/sm for better mobile responsiveness

### Added

- `User::getStatistics()` — returns total, active and inactive user counts in a single query
- `User::getRecent($limit)` — returns the most recently registered users

## [1.5.0] - 2026-03-19

### Added

- `description` field on the `permissions` table (TEXT NULL) — editable via the create/edit modal in the permissions index and detail views
- User assignment and revocation directly from the permission detail view: new AJAX endpoints `assign_user_permission_ajax.php` and `revoke_user_permission_ajax.php` with CSRF validation and permission guard
- `refreshPermissionsIfStale()` in `session.php` — called automatically from `requireLogin()` on every page load; compares `$_SESSION['permissions_ts']` against the `permissions_updated_at` column in `users` and regenerates the session permission cache only when stale (1 extra DB query per load vs. always querying)
- `permissions_updated_at DATETIME NULL` column on `users` table — updated by `updatePermissionsTimestamp()` whenever a user's permissions change (assign, revoke, or bulk update)
- `updatePermissionsTimestamp()` and `getPermissionsTimestamp()` on `User` model — used by the cache invalidation mechanism

### Changed

- Permission detail view (`views/permissions/detail.php`): redesigned with an "Assign User" modal (Select2 populated from PHP server-side) and per-row revoke buttons with SweetAlert2 confirmation
- Users without a permission now loaded from PHP `<option>` elements instead of AJAX, avoiding the Bootstrap modal + Select2 focus-trap conflict that caused the dropdown to close immediately
- `AuthorizationService::isAdmin()` now memoizes its result per-request via `private static array $adminCache` — eliminates repeated DB queries when the method is called multiple times for the same user in a single request
- `views/layouts/header.php`: `require_once config.php` moved before `requireLogin()` so the PSR-4 autoloader is registered before `refreshPermissionsIfStale()` instantiates `\Models\User`

### Fixed

- Fatal 500 on dashboard (`index.php`): `requireLogin()` → `refreshPermissionsIfStale()` tried to instantiate `\Models\User` before `config.php` registered the autoloader
- `Uncaught SyntaxError: redeclaration of const csrfToken` in `views/permissions/detail.php`: removed redundant `const csrfToken` declaration (already defined globally in `header.php`)

## [1.4.0] - 2026-03-16

### Added

- Conditional plugin loading via `$plugins` array in views — DataTables, Select2, jQuery Validate and export libraries are only loaded on pages that declare them, eliminating ~20 unnecessary files from every page load
- `public/js/core/common-validate.js` — global jQuery Validate configuration for Bootstrap 4: Bootstrap-compatible `errorPlacement`, `highlight`/`unhighlight`, `success` callback and `onkeyup: false`; reusable by any future module without extra config
- Remote validation endpoints `controllers/users/check_email.php` and `controllers/users/check_document.php` — lightweight AJAX uniqueness checks against the DB for email and document number on create/update forms; exclude the current user on edit via `$id_excluir`

### Changed

- `header.php` and `footer.php` now conditionally load plugin CSS/JS based on the `$plugins` array declared at the top of each view; `common-datatable.js` is now part of the `datatables` plugin group
- User create/update forms migrated to jQuery Validate with inline Bootstrap `is-invalid`/`is-valid` feedback, replacing manual `Swal.fire()` validation popups
- Remote validation fires only on field blur and form submit (`onkeyup: false`) — no AJAX calls while the user is still typing

### Fixed

- Password minimum length mismatch between frontend (was 6) and backend `validarDatos()` (requires 8) — both now enforce 8 characters
- Browser autofill in `update.php`: password fields changed to `autocomplete="new-password"` (Chrome ignores `autocomplete="off"` on `type="password"`), correo field set to `autocomplete="off"`

## [1.3.0] - 2026-03-15

### Added

- Permission cache in `$_SESSION['user_permissions']` populated at login — navigation checks now require zero DB queries per page load; cache is refreshed automatically when a user edits their own permissions
- Self-deactivation protection: users cannot deactivate their own account from the index, nor change their own status from the edit form
- Status field removed from the user edit form — account activation/deactivation is managed exclusively from the user list

### Changed

- `sanitizeData()` in `User.php` and `Permission.php` now only applies `trim()`; `htmlspecialchars()` moved exclusively to the view layer, eliminating double-encoding of stored data
- Login no longer reveals whether an email or document number exists — all credential failures return a generic "Incorrect credentials" message
- `loginByEmail()` and `loginByDocumentNumber()` no longer filter by `status = 1`; account status is checked after credential verification so disabled-account users get an explicit message
- `logout.php` now includes `session.php` (instead of raw `session_start()`) so `$URL` is always defined and the redirect lands on the correct login page

### Security

- CSRF token regenerated after every successful POST validation across all 6 endpoints (create/update/toggle-status permissions, create/update user, toggle user status)
- Session cookie hardened: `httponly`, `SameSite=Lax`, `use_strict_mode` flags set in `session.php`
- AJAX endpoints for permissions and users now require `requirePermission()` inline check returning JSON 403 instead of an HTML error page
- `AuthController::verificarSesion()` removed — was dead code with an inconsistent 3600 s timeout vs the 86400 s used by `checkSessionTimeout()` in `session.php`
- `toggle_user_status.php` rewritten as POST + CSRF + JSON AJAX endpoint (was an unprotected GET redirect)
- `AuthController` CSRF helpers now delegate to the global `generateCSRFToken()` / `verifyCSRFToken()` functions, eliminating duplicate logic

### Fixed

- N+1 queries in `PermissionController::index()` — replaced per-row `countUsers()` loop with a single `getAllWithUserCount()` LEFT JOIN query
- N+1 queries in `update.php` permission checkboxes — replaced per-permission `hasPermissionAssigned()` calls with a single `getAssignedPermissions()` + `in_array()` check
- Headers-after-output warning in `detail.php` — controller logic moved before `include header.php`
- `AdminLTE` card classes: `card-outline card-primary` moved from `card-header` to `div.card` in `users/index.php`

## [1.2.0] - 2026-03-14

### Added

- `show-user.js` module: Bootstrap tab persistence via `localStorage` and profile image lightbox with SweetAlert2
- `show-user.css` module: active tab color styling for the user detail page
- CSRF token on `create.php` and `update.php` user forms

### Changed

- `create.php` and `update.php`: input fields now use `input-group` with FontAwesome icons; sidebar cards use `card-outline sticky-top`; submit/cancel buttons moved to `card-footer`
- `show.php`: moved inline `<style>` and `<script>` blocks to `$module_styles` / `$module_scripts` modules; fixed `card-outline` placement on System card
- `profile.php`: simplified to only editable fields — profile photo, phone, and address; password change moved to a separate AJAX tab
- `profile-user.js`: rewrote with client-side image preview (size and format validation) and AJAX password change that redirects to logout on success
- `ProfileController::updateProfile`: non-editable fields (name, surnames, email) now sourced from DB instead of POST to prevent premature validation failure
- `process_update_profile.php`: switched from `UserController` to `ProfileController`
- `common-datatable.js`: added `initComplete` callback to reveal the table only after DataTables finishes rendering

### Fixed

- `.tab is not a function` error in `show.php` caused by inline script running before Bootstrap loaded
- Image upload silently failing due to Apache (`daemon`) lacking write permission on `public/uploads/users/`
- Profile update rejecting valid image uploads when name/surnames were absent from POST

## [1.1.2] - 2026-03-13

### Added

- Custom error pages (403, 404, 500) served via Apache `ErrorDocument` directives in `.htaccess`
- `APP_VERSION` environment variable exposed as `$APP_VERSION` global in `session.php`, displayed in the footer
- `requirePermission()` helper in `session.php` to gate access by named permission, rendering the 403 view directly instead of redirecting

### Fixed

- `$URL` undefined variable in `404.php` and `403.php` when served by Apache; replaced with local `$app_url`
- Font Awesome webfonts not loading due to mismatched relative path; added `public/css/lib/webfonts` symlink pointing to `fontawesome/webfonts`
- Removed external Google Fonts request in `header.php`; font is now loaded from local `public/css/core/webfonts.css`

## [1.1.1] - 2026-02-20

### Fixed

- Autoloader case-sensitive path resolution on Linux: `strtolower()` was applied to the full class path including the class name, causing `Class not found` errors on case-sensitive filesystems (Linux/ext4). Now only the namespace portion (directories) is lowercased; the class name preserves its original casing.

## [1.1.0] - 2025-08-24

### Added

- Comprehensive PHPDoc documentation across all PHP files
- JSDoc documentation for all custom JavaScript modules
- Standardized documentation format with @package ProyectoBase and @author jandrescodes
- Version 1.0 consistency across all documented files
- Professional documentation structure following industry standards

### Changed

- Updated project identity from legacy references (Sistema de Ventas, Calzados y Carteras, Alojamiento Flores) to ProyectoBase
- Repository references updated to php-mvc-admin-starter in README and documentation
- Standardized @author tags to jandrescodes across all files
- Enhanced code organization with proper @subpackage annotations

### Fixed

- Inconsistent project naming across different files
- Missing documentation headers in controller and service files
- Outdated repository URLs in installation instructions

### Documentation

- All PHP classes and files now include proper PHPDoc headers
- JavaScript modules documented with JSDoc standards
- Consistent package and authorship information throughout codebase

## [1.0.0] - 2025-08-23

### Added

- Initial project setup with PHP MVC authentication system
- Complete user management with CRUD operations
- Permission-based access control system
- Responsive admin interface using AdminLTE 3
- DataTables integration for data management
- Image upload and management functionality
- PDF generation capabilities with TCPDF
- Multi-language JavaScript components
- Secure session management
- Database configuration with environment variables

### Security

- Password hashing with PHP's password_hash()
- CSRF protection on all forms
- Session hijacking prevention
- SQL injection protection with prepared statements
- XSS prevention with input sanitization

[3.15.3]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.15.2...3.15.3
[3.15.2]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.15.1...3.15.2
[3.15.1]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.15.0...3.15.1
[3.15.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.14.0...3.15.0
[3.14.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.13.1...3.14.0
[3.13.1]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.13.0...3.13.1
[3.13.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.12.0...3.13.0
[3.12.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.11.0...3.12.0
[3.11.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.10.0...3.11.0
[3.10.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.9.0...3.10.0
[3.9.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.8.0...3.9.0
[3.8.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.7.0...3.8.0
[3.7.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.6.0...3.7.0
[3.6.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.5.0...3.6.0
[3.5.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.4.0...3.5.0
[3.4.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.3.0...3.4.0
[3.3.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.2.0...3.3.0
[3.2.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.1.0...3.2.0
[3.1.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/3.0.1...3.1.0
[3.0.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/2.3.1...3.0.0
[2.3.1]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/2.3.0...2.3.1
[2.3.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/2.2.0...2.3.0
[2.2.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/2.1.0...2.2.0
[2.1.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/2.0.1...2.1.0
[2.0.1]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/v1.5.0...2.0.0
[1.5.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/v1.4.0...v1.5.0
[1.4.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/v1.1.2...v1.2.0
[1.1.2]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/jandrescodes/php-mvc-admin-starter/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/jandrescodes/php-mvc-admin-starter/releases/tag/v1.0.0
