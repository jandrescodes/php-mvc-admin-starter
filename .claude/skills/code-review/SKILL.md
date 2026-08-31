---
name: code-review
description: 'Review PHP MVC code before merging a branch in php-mvc-admin-starter. Use when the user asks to review code, check a PR, validate a feature before merge, or mentions "code review", "revisar código", "antes del merge", or "PR". Triggers automatically when a feature branch is ready to merge to main.'
license: MIT
allowed-tools: Bash, Read
---

# Code Review — php-mvc-admin-starter

## Overview

Structured code review process for this PHP MVC admin starter before any branch merge to `main`. Covers security,
architecture conventions, and the project's specific patterns documented in `AGENTS.md` — read that file first if
it isn't already in context; this checklist assumes it.

## How to Run the Review

```bash
# See all changed files in the branch
git diff main --name-only

# Full diff against main
git diff main

# Check for any staged but uncommitted changes
git status

# Run the test suites
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Integration
```

Review each changed file against the checklist below. Group findings by severity before reporting.

---

## Review Checklist

### 1. Security — block merge if any fail

- [ ] No SQL concatenation — all queries use PDO prepared statements (`app/Core/Model.php` helpers or manual
      `prepare()`/`bindParam()`)
- [ ] Passwords always `password_hash()` on save, `password_verify()` on check — never plain text
- [ ] Output escaped — `htmlspecialchars()` on all user-generated output at the **view** layer only (never in
      models, never before storing in DB — `trimInput()` at model layer instead)
- [ ] No secrets committed — `.env`, `.env.testing`, tokens, credentials not in the diff
- [ ] Middleware declared — every route in `routes/web.php` has the correct `middleware` key: `'auth'`
      (`AuthMiddleware`), `'guest'` (`GuestMiddleware`), or `'perm:NAME'` (`PermissionMiddleware`) as appropriate
- [ ] CSRF present — `generateCSRFToken()` on forms, `$this->csrfCheck()` on every state-changing POST/AJAX
      controller method, `regenerateCSRFToken()` called after every successful POST (including failure paths on
      sensitive endpoints like password change)
- [ ] Destructive/non-AJAX actions are `POST` routes with CSRF, never `GET` (including logout)
- [ ] Image uploads route through `ImageService` — MIME validated via `finfo`, never `$_FILES['type']`; extension
      whitelist enforced (`jpg`, `jpeg`, `png`, `gif`, `webp`)
- [ ] Remember-me token cleared — `$userModel->clearRememberToken($userId)` called whenever a password changes,
      regardless of who triggered it (self-service or admin edit, forgot-password or invitation-accept)
- [ ] Password reset / invitation tokens stored hashed (SHA-256), never plain text in `password_resets`

---

### 2. Architecture Conventions — block merge if any fail

- [ ] Fat model / thin controller — validation, formatting, and cache invalidation live in the Model; controllers
      only read POST/GET, call model methods, set flash messages, and redirect/return JSON
- [ ] Auth checks use `Auth::check()`, `Auth::hasPermission()`, `Auth::id()`, `Auth::user()` — never read
      `$_SESSION` directly for auth state outside `App\Core\Auth`
- [ ] Namespaces consistent: `App\Controllers\*`, `App\Models\*`, `App\Services\*`, `App\Core\*`
- [ ] `DashboardCache::forget()` called for affected keys after any mutation to user/permission/role/invitation
      data, per the invalidation contract in `AGENTS.md` (e.g. user mutations clear `user_stats`,
      `users_by_status`, `recent_users`, `users_by_month`; permission mutations clear `perm_stats`,
      `top_permissions`; role mutations clear `role_stats`)
- [ ] `AuditLogger::log()` called in the **controller** (never inside model methods) after every state-changing
      action succeeds — create/update/delete/status-change/invite/etc.
- [ ] `updatePermissionsTimestamp()` called after any permission change — for the affected user, or for **every
      user of a role** when role permissions change
- [ ] No inline `<script>` blocks in views before `footer.php` loads — dynamic data passed via `data-*` attributes,
      JS modules registered through `$module_scripts` / `$module_styles`
- [ ] AdminLTE card color classes (`card-outline card-{color}`) on `div.card`, never on `div.card-header`
- [ ] New/changed classes use PSR-4 autoloading correctly (`App\` → `app/`); run `composer dump-autoload -o` if the
      mapping changed

---

### 3. Logic & Quality — flag as warning, discuss before merge

- [ ] Dual validation — jQuery Validate on the frontend AND matching PHP validation on the backend; frontend alone
      is never sufficient
- [ ] SweetAlert2 (`ToastUtils`/`AlertUtils`) used — no native `alert()`/`confirm()` in JS; destructive actions show
      a confirmation dialog first
- [ ] Edge cases covered: record doesn't exist, user lacks permission, empty/malformed input, race conditions on
      unique fields (email/document) — checked via `remote` validation rules AND server-side on submit
- [ ] Model traits used when a model grows beyond ~400 lines, split by responsibility (`app/Models/Traits/`), not
      by arbitrary size
- [ ] No duplicate logic — if the same query/check appears in two places, it belongs in a shared Model method
- [ ] Tests added/updated: Unit tests for pure logic (`Auth`, `Router`, `helpers`, `ImageService`), Integration
      tests for anything touching the DB (`tests/Integration/`, mirroring `app/` structure)

---

### 4. Git & Branch — block merge if any fail

- [ ] Commits follow Conventional Commits (`feat:`, `fix:`, `docs:`, etc.) with an accurate type
- [ ] No commits directly to `main` for non-trivial work — via PR unless the user explicitly directs otherwise
- [ ] No `.env`, `.env.testing`, or credentials in the diff
- [ ] `AGENTS.md` updated if the change introduces a new convention, cache key, or invalidation rule that isn't
      already documented

---

## Output Format

Always structure the review response in this exact format:

```
## Code Review — [branch name]

### ✅ What's good
- [At least 2 specific things done well]

### ⚠️ Observations (non-blocking)
- [Improvements that would be nice but don't block merge]

### 🚨 Must fix before merge
- [File:line] — [Problem description]
  [Code showing the issue]
  Fix: [Code showing the correct approach]
```

If there are no blocking issues, end with:

```
### Verdict: ✅ Approved — ready to merge to main
```

If there are blocking issues, end with:

```
### Verdict: 🚨 Changes required — do not merge until fixed
```

---

## Quick Reference — Most Common Issues

| #   | File type  | Issue                                                                      | Severity   |
| --- | ---------- | --------------------------------------------------------------------------- | ---------- |
| 1   | Controller | Missing `middleware` key or wrong middleware in `routes/web.php`            | 🚨 Block   |
| 2   | Controller | Missing `$this->csrfCheck()` on a state-changing POST/AJAX method            | 🚨 Block   |
| 3   | Model      | SQL built with string concatenation instead of prepared statements          | 🚨 Block   |
| 4   | View       | User output not escaped with `htmlspecialchars()`                           | 🚨 Block   |
| 5   | Model      | Password changed without calling `clearRememberToken()`                     | 🚨 Block   |
| 6   | Model      | Mutation missing matching `DashboardCache::forget()` call                   | ⚠️ Warning |
| 7   | Controller | `AuditLogger::log()` missing after a state-changing action                  | ⚠️ Warning |
| 8   | JS         | Native `alert()`/`confirm()` instead of `ToastUtils`/`AlertUtils`           | ⚠️ Warning |
| 9   | Controller | Business logic that belongs in the Model                                    | ⚠️ Warning |
| 10  | Any        | Frontend validation without matching backend validation                     | ⚠️ Warning |
