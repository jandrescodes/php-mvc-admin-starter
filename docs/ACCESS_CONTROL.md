# Access Control and Session Flow

This document summarizes how authentication, session validation, remember-me, and permission checks work in the project.

## Request guard flow

All requests pass through `public/index.php`, which boots the session and CSRF helpers. The `App\Core\Router` runs middleware before dispatching to a controller method:

1. `AuthMiddleware::handle()` — if no active session, attempts auto-login via remember-me cookie; otherwise validates timeout and anti-hijacking, then calls `Auth::refreshPermissionsIfStale()`.
2. `GuestMiddleware::handle()` — attempts auto-login via remember-me cookie before checking session; redirects authenticated users away from guest-only pages (login, forgot-password).
3. `PermissionMiddleware::handle($name)` — calls `Auth::hasPermission($name)`; returns a 403 view on failure.

Middleware is declared per route in `routes/web.php`:

```php
['method' => 'GET', 'path' => '/users', 'controller' => 'User@index', 'middleware' => ['auth', 'perm:users']],
```

## Auth hub — `App\Core\Auth`

All authentication, session, and remember-me concerns are centralised in the static class `App\Core\Auth`. No instantiation needed.

| Method                                       | Description                                                                                                 |
| -------------------------------------------- | ----------------------------------------------------------------------------------------------------------- |
| `Auth::check()`                              | Returns `true` if a valid authenticated session exists                                                      |
| `Auth::id()`                                 | Returns the current user's ID or `null`                                                                     |
| `Auth::user()`                               | Returns current user array (`id`, `name`, `email`, `role`, `image`) or `null`                               |
| `Auth::isAdmin()`                            | Returns `true` if `$_SESSION['user_is_admin']` is `true` (set from `roles.is_system`)                       |
| `Auth::hasPermission(string $name)`          | Checks session cache; `'*'` grants all (admins)                                                             |
| `Auth::permissions()`                        | Returns the full permission name array from session                                                         |
| `Auth::login(array $user, array $permNames)` | Regenerates session ID, writes all session keys, caches permissions                                         |
| `Auth::logout()`                             | Reads user ID from session, clears remember-me cookie, destroys the session                                 |
| `Auth::checkTimeout()`                       | Destroys session and returns `false` if idle > `SESSION_LIFETIME`; fail-closed if `last_access` key missing |
| `Auth::checkSecurity()`                      | Destroys session and returns `false` if IP or User-Agent changed; fail-closed if keys missing               |
| `Auth::refreshPermissionsIfStale()`          | Reloads permission cache from DB if `permissions_updated_at` is newer than session timestamp                |
| `Auth::issueRememberCookie(int $userId)`     | Generates token, stores SHA-256 hash in DB, sets cookie                                                     |
| `Auth::attemptRememberLogin()`               | Validates cookie token, auto-logs in, rotates token                                                         |
| `Auth::clearRememberCookie(int $userId)`     | NULLs DB token, expires cookie                                                                              |

## Session keys written at login

| Key                | Value                                                               |
| ------------------ | ------------------------------------------------------------------- |
| `user_id`          | `int` — primary key                                                 |
| `user_name`        | `string` — first name                                               |
| `user_email`       | `string`                                                            |
| `user_role`        | `string` — role display name (from `roles.name`)                    |
| `user_is_admin`    | `bool` — derived from `roles.is_system`                             |
| `user_image`       | `string` — avatar path                                              |
| `user_permissions` | `string[]` — union of direct + role permissions; `['*']` for admins |
| `permissions_ts`   | `string` — `Y-m-d H:i:s` timestamp used for stale-check             |
| `last_access`      | `int` — Unix timestamp for inactivity timeout                       |
| `ip`               | `string` — client IP for anti-hijacking                             |
| `user_agent`       | `string` — User-Agent for anti-hijacking                            |

## Session security controls

Implemented in `App\Core\Auth` (called by `AuthMiddleware`):

- Session cookie hardening (`httponly`, `SameSite=Lax`, `use_strict_mode`) — set in `public/index.php` before `session_start()`
- Inactivity timeout via `Auth::checkTimeout()` — reads `SESSION_LIFETIME` from `.env` (default: 1800 s). Fail-closed: a session without `last_access` is destroyed immediately.
- Anti-hijacking validation via `Auth::checkSecurity()` (IP + User-Agent). Fail-closed: a session without `ip` or `user_agent` is destroyed immediately.

If validation fails and no valid remember-me cookie exists, the user is redirected to login and receives a session message.

## Remember Me

Implemented in `App\Core\Auth`. Controlled by three `.env` variables:

| Variable                  | Default       | Description                                   |
| ------------------------- | ------------- | --------------------------------------------- |
| `SESSION_LIFETIME`        | `1800`        | Seconds of inactivity before session expires  |
| `REMEMBER_ME_LIFETIME`    | `2592000`     | Seconds the persistent cookie lives (30 days) |
| `REMEMBER_ME_COOKIE_NAME` | `remember_me` | Cookie name                                   |

**How it works:**

1. User checks "Remember me" on login → `AuthController` calls `Auth::issueRememberCookie($userId)`.
2. A 64-char hex token is generated with `random_bytes(32)`; its SHA-256 hash is stored in `users.remember_token` with an expiry in `users.remember_token_expires`. The plain token goes into the cookie.
3. On every request without an active session, `AuthMiddleware` calls `Auth::attemptRememberLogin()`:
   - Reads the cookie, hashes it, looks up `users` where `remember_token = hash AND expires > NOW() AND status = 1`.
   - On match: calls `Auth::login()` to rebuild the session (including the permission UNION), then **rotates** the token to mitigate cookie theft. Also calls `Auth::refreshPermissionsIfStale()` before returning.
   - On no match: clears the stale cookie and returns `false`.
4. On logout: `Auth::logout()` reads the user ID from session internally, NULLs the DB columns, expires the cookie, then destroys the session.

**Security properties:**

- Token never stored in plain text in DB — only SHA-256 hash.
- Token rotated on every successful auto-login.
- Cookie: `HttpOnly` (no XSS), `SameSite=Lax` (mitigates CSRF), `Secure` flag set when HTTPS is detected.
- Deactivated users (`status = 0`) and pending users (`status = 2`) cannot auto-login — the query filters `status = 1`.
- Expired tokens ignored via `NOW()` comparison in the query.
- **Token revoked on password change** — `User::updatePassword()` calls `clearRememberToken($id)` after every successful password hash update, regardless of whether the change was initiated by the user or an admin. This prevents a stolen cookie from remaining valid after a password reset.

**DB columns in `users`:**

```sql
remember_token         CHAR(64)  NULL DEFAULT NULL
remember_token_expires DATETIME  NULL DEFAULT NULL
```

## Login throttling (brute-force protection)

Implemented in `App\Services\LoginThrottleService` + `App\Models\User`. Controlled by two `.env` variables:

| Variable                | Default | Description                         |
| ----------------------- | ------- | ----------------------------------- |
| `LOGIN_MAX_ATTEMPTS`    | `5`     | Consecutive failures before lockout |
| `LOGIN_LOCKOUT_MINUTES` | `15`    | Minutes the account stays locked    |

**How it works:**

1. `AuthController::login()` resolves the user row via `User::findByEmail()` or `User::findByDocumentNumber()` **before** calling `password_verify`.
2. If the user exists, `LoginThrottleService::isLocked()` evaluates `locked_until` vs `NOW()` (lazy — no write). If locked, the request is rejected with a human-readable message and `password_verify` is never called.
3. On a credential failure (wrong password), `LoginThrottleService::registerFailure()` calls `User::recordFailure()` — a single UPDATE that increments `login_attempts` and sets `locked_until` when the threshold is reached. Non-existent emails do not generate any DB write.
4. On a successful login, `LoginThrottleService::clearOnSuccess()` resets all three throttle columns to their defaults.
5. **Lazy unlock** — `locked_until` is evaluated on the next attempt. No cron job required. Once `locked_until ≤ NOW()`, `getLockStatus()` returns `locked=false`.

**Admin manual unlock:**

- `POST /users/{id}/unlock-login` (middleware: `auth + perm:users`) → `UserController::unlockLoginAjax()`.
- `views/users/show.php` conditionally shows a "Locked until HH:MM" badge and an "Unlock Login" button when `locked_until > NOW()`.
- JS handler in `show-user.js` uses `AlertUtils.confirm` → `ToastUtils.loadingWithMinTime` → `location.reload()`.

**DB columns in `users`:**

```sql
login_attempts  INT      NOT NULL DEFAULT 0
locked_until    DATETIME NULL     DEFAULT NULL
last_attempt_at DATETIME NULL     DEFAULT NULL
```

**Security properties:**

- Locked accounts never reach `password_verify` — no timing information leaks.
- Non-existent emails are silently ignored — user existence is not revealed.
- IP-based blocking intentionally omitted: internal admin system where false positives (shared NAT) outweigh the benefit.
- Lockout by account is a known DoS vector; mitigated by admin manual unlock and configurable lockout duration.

---

## Permission model

Permissions are cached in session at login and checked via `Auth::hasPermission(string $name)`.

### Two levels of assignment

```
roles ──┐
        ├── role_permissions ──► permissions   ← "this ROLE can do X"
        │
users ──┼── user_permissions ──► permissions   ← "this USER specifically can do X"
        │
        └── role_id ──► roles                  ← "this user belongs to role Y"
```

- `user_permissions` — direct per-user permission overrides.
- `role_permissions` — permissions inherited by all users of a role.
- At login and on cache refresh, `Auth` computes `array_unique(merge(direct, from_role))` and stores the result in `$_SESSION['user_permissions']`.
- Admins (role with `is_system = 1`) always get `['*']` — `hasPermission()` returns `true` for any name without iterating the array.

### System roles (`is_system = 1`)

- `Auth::isAdmin()` reads `$_SESSION['user_is_admin']`, which is set from `roles.is_system` at login — not from the role name. The role can be freely renamed.
- Roles with `is_system = 1` cannot be deactivated or deleted via the UI (`RoleController` enforces this server-side).

## Permission cache refresh contract

Permission changes are cached in session and refreshed when stale.

- Session value: `$_SESSION['permissions_ts']`
- DB value: `users.permissions_updated_at`
- Refresh trigger: `Auth::refreshPermissionsIfStale()` — called by `AuthMiddleware` on every authenticated request — compares both values and reloads the UNION from DB if the DB timestamp is newer.

**After changing direct user permissions:** call `$userModel->updatePermissionsTimestamp($userId)`.

**After changing role permissions:** call `$userModel->updatePermissionsTimestamp($uid)` for **every user** of that role — `Role::getUserIdsByRole($roleId)` returns the list. `RoleController::syncPermissions()` already does this automatically.

## User status values

`users.status` is a `tinyint` with three values:

| Value | Constant                | Login | Reset password   | Remember-me cookie |
| ----- | ----------------------- | ----- | ---------------- | ------------------ |
| `0`   | `User::STATUS_INACTIVE` | ❌    | ❌ (generic msg) | ❌                 |
| `1`   | `User::STATUS_ACTIVE`   | ✅    | ✅               | ✅                 |
| `2`   | `User::STATUS_PENDING`  | ❌    | ❌ (generic msg) | ❌                 |

Pending users are created via the invitation flow. `AuthController::login()` blocks status 2 before `Auth::login()` is called.

## Application permissions reference

| Permission name | Module      | Description                                                     |
| --------------- | ----------- | --------------------------------------------------------------- |
| `profile`       | Profile     | Access to own profile and password changes                      |
| `admin`         | Global      | General administration — granted to all system-role users (`*`) |
| `users`         | Users       | Full user management (CRUD, activation, unlock)                 |
| `permissions`   | Permissions | Create, edit, and assign/revoke permissions                     |
| `roles`         | Roles       | Create, edit, and manage role↔permission assignments            |
| `audit_log`     | Audit Log   | Read-only access to the activity/audit log                      |
| `audit_log_export` | Audit Log | Download the audit log as a server-generated PDF (`GET /audit-log/export`) |

> Administrators (role with `is_system = 1`) receive `['*']` in session — `hasPermission()` returns `true` for **any** permission name without requiring explicit assignment.

## Practical pattern

For any new protected route, declare middleware in `routes/web.php`:

```php
// Requires authentication only
['method' => 'GET', 'path' => '/profile', 'controller' => 'User@profile', 'middleware' => ['auth']],

// Requires authentication + named permission
['method' => 'GET', 'path' => '/products', 'controller' => 'Product@index', 'middleware' => ['auth', 'perm:products']],
```

Within the controller method, additional inline checks are available via the base `Controller` helpers:

```php
$this->csrfCheck();               // validates CSRF; returns JSON 403 for AJAX or redirects
$this->requirePermission('products'); // re-checks permission and renders 403 view on failure
```

Views and layouts can gate nav items and action buttons directly:

```php
<?php if (\App\Core\Auth::hasPermission('users')): ?>
    <a href="<?= URL ?>users">Users</a>
<?php endif; ?>
```
