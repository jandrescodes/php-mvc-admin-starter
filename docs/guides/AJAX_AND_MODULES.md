# AJAX Endpoints and Frontend Module Conventions

This guide documents the project conventions for AJAX controller methods and module scripts.

## Endpoint pattern (PHP)

AJAX actions are regular controller methods registered in `routes/web.php` with the appropriate HTTP method and middleware. A typical AJAX method follows this sequence:

1. Call `$this->csrfCheck()` — validates the CSRF token; automatically returns JSON 403 for AJAX requests or redirects for regular POSTs.
2. Call `regenerateCSRFToken()` **unconditionally** (before the `if/else`) on sensitive endpoints so the token rotates on both success and failure paths.
3. Perform the action (model call, service call, etc.).
4. Return JSON via `$this->jsonResponse($data)`.

Example:

```php
public function toggleStatusAjax(): void
{
    $this->csrfCheck();
    regenerateCSRFToken(); // always rotate — before success/failure branch
    // ... business logic ...
    $this->jsonResponse(['success' => true]);
}
```

**Non-AJAX destructive actions** (logout, hard-delete, bulk operations) must also use `POST` routes with CSRF — never `GET`. A `GET /logout` can be triggered by any third-party `<img>` tag. The pattern is a minimal `<form method="POST">` with a hidden `csrf_token` field and a submit button styled as a link or button:

```php
// routes/web.php
['method' => 'POST', 'path' => '/logout', 'controller' => 'Auth@logout', 'middleware' => []],
```

```php
// Controller method
public function logout(): void
{
    $this->csrfCheck();
    // ... session teardown ...
}
```

```php
<!-- view -->
<form method="POST" action="<?= URL ?>logout" class="d-inline">
    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken(); ?>">
    <button type="submit" class="btn btn-outline-secondary">Log Out</button>
</form>
```

Route declaration in `routes/web.php`:

```php
['method' => 'POST', 'path' => '/users/toggle-status', 'controller' => 'User@toggleStatusAjax', 'middleware' => ['auth', 'perm:users']],
```

When the JS success flow ends with `location.reload()`, set the flash message before calling `jsonResponse()`:

```php
$_SESSION['message'] = 'User status updated.';
$_SESSION['icon']    = 'success';   // success | error | warning | info
$this->jsonResponse(['success' => true]);
```

`views/layouts/messages.php` picks these up and calls `ToastUtils[icon](message)` on the reloaded page.

For the welcome popup shown after login, use `$_SESSION['welcome_user']` instead — `messages.php` calls `AlertUtils.welcome(name)` for it:

```php
$_SESSION['welcome_user'] = $_SESSION['user_name'];
$this->redirect('dashboard');
```

## Frontend module structure

Feature scripts live in:

- `public/js/modules/{feature}/`

Pass them to `Controller::render()` as the fourth argument:

```php
$this->render('users/index', $data, ['tabulator', 'export', 'tom-select'], ['users/index-users']);
```

## Plugin loading

Pass only required plugins as the third argument to `render()`:

```php
$this->render('permissions/index', $data, ['tabulator', 'export', 'tom-select'], ['permissions/index-permissions']);
```

Asset resolution is handled by `App\Core\AssetRegistry` and rendered in `layouts/header.php` / `layouts/footer.php`.

## Load order (important)

`footer.php` loads:

1. Bootstrap + AdminLTE + OverlayScrollbars
2. SweetAlert2
3. `public/js/core/http.js` (`Http`), `common-utils.js`, `form-validator.js` (`FormValidator`)
4. Conditional plugin JS (`tabulator`, `tom-select`, `export`, `chart`)
5. `public/js/core/ui-components.js` (`ComponentUtils`), `sweetalert-utils.js` — `ToastUtils`, `AlertUtils` and `SwalTheme` available globally from here — and `table-utils.js` (`TableUtils`)
6. `public/js/modules/profile/theme-toggle.js` — theme toggle (writes `data-bs-theme` and `localStorage['theme']`)
7. Module scripts (`$module_scripts`)

Do not place page inline scripts that depend on Bootstrap plugins before `footer.php`.

Auth views (`views/auth/*.php`) do not use `footer.php`. They are pure card markup rendered via `Controller::renderStandalone($view, $data, $title, $module_scripts)`, which requires the shared `views/layouts/auth.php` — that layout owns `sweetalert-utils.js`, `http.js`, `form-validator.js`, `common-utils.js` (password toggle), `theme-toggle.js`, `dark-mode.css` and `login-dark.css`. Register a new auth page's module script via the `$module_scripts` argument, not by editing the layout.

## AJAX URL targets

JS modules call the clean routes registered in `routes/web.php` (all `POST` unless noted; every one is behind `auth` and its module permission):

| Action                              | Route                                                       |
| ----------------------------------- | ----------------------------------------------------------- |
| Check email uniqueness              | `POST /users/check-email`                                   |
| Check document uniqueness           | `POST /users/check-document`                                |
| Toggle user status                  | `POST /users/toggle-status`                                 |
| Change password (AJAX, own account) | `POST /users/change-password`                               |
| Unlock a login                      | `POST /users/{id}/unlock-login`                             |
| Resend an invitation                | `POST /users/{id}/resend-invitation`                        |
| Create / update permission          | `POST /permissions/create`, `/permissions/update`           |
| Toggle permission status            | `POST /permissions/toggle-status`                           |
| Assign / revoke a user permission   | `POST /permissions/assign-user`, `/permissions/revoke-user` |
| Users without a permission          | `GET /permissions/get-users-without`                        |
| Check permission name uniqueness    | `POST /permissions/check-name`                              |
| Create / update role                | `POST /roles/create`, `/roles/update`                       |
| Toggle role status                  | `POST /roles/toggle-status`                                 |
| Sync a role's permissions           | `POST /roles/sync-permissions`                              |
| Check role name uniqueness          | `POST /roles/check-name`                                    |

Create and update role return `csrf_token` in the JSON: the role modal chains a second POST (permission sync) without reloading the page.

## SweetAlert2 utilities

All SweetAlert2 usage must go through `public/js/core/sweetalert-utils.js`. Never call `Swal.fire()` directly in module scripts.

> **Escaping:** SweetAlert2 renders `title` and `options.html` as HTML. `ToastUtils` / `AlertUtils` already pass every `title` through the global `escapeHtml()` (defined in `sweetalert-utils.js`); anything user-controlled that you put into `options.html` or into `.html()` must be wrapped in `escapeHtml(value)` yourself (or use `.text()`). Same for the audit-log detail modal, whose values can come from unauthenticated requests.

> **Business rejections:** answer HTTP 200 with `{success:false, message}`; reserve 403 for CSRF/middleware. `Http` returns `{ok, status, data}` and a 403 arrives with `ok: false`, so the reason in `data.message` would be lost.

### ToastUtils

| Method                                                  | Purpose                                                           |
| ------------------------------------------------------- | ----------------------------------------------------------------- |
| `ToastUtils.success(title, text?, timer?)`              | Green toast                                                       |
| `ToastUtils.error(title, text?)`                        | Red toast                                                         |
| `ToastUtils.warning(title, text?, duration?)`           | Orange toast                                                      |
| `ToastUtils.info(title, text?)`                         | Blue toast                                                        |
| `ToastUtils.loading(message)`                           | Blocking loading dialog (no timer)                                |
| `ToastUtils.loadingWithMinTime(message, action, minMs)` | Show loading, guarantee minimum display time, then run `action()` |

### AlertUtils

| Method                                                 | Purpose                                                                                                                                                                                      |
| ------------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `AlertUtils.confirm(title, text, onConfirm, options?)` | Confirmation dialog; `options.html` for rich body text                                                                                                                                       |
| `AlertUtils.confirmDelete(url, title?, text?)`         | Red confirm that navigates to `url` (a GET) once accepted. No screen uses it: destructive actions are `POST` routes with CSRF, confirmed with `AlertUtils.confirm` and sent with `Http.post` |
| `AlertUtils.welcome(name)`                             | Animated welcome popup (used at login)                                                                                                                                                       |
| `AlertUtils.image(src, alt?, confirmButtonText?)`      | Lightbox image viewer                                                                                                                                                                        |

### loadingWithMinTime pattern

Use this for every CRUD action (create, update, delete, toggle-status, assign, revoke):

```js
ToastUtils.loadingWithMinTime(
    "Creating user...",
    async () => {
        const { ok, data } = await Http.post(`${baseUrl}users`, formData);
        if (ok && data.success) {
            location.reload(); // Swal closes naturally with the page reload
            return;
        }
        Swal.close();
        ToastUtils.error(
            "Error",
            data?.message ?? "A communication error occurred with the server.",
        );
    },
    800,
);
```

`Http.post/get` (`core/http.js`) sends `X-Requested-With` and the current CSRF token. A POST that does not reload the page must return `csrf_token` so the next POST can use it.

### Modal-first pattern

When an AJAX action is triggered from inside a Bootstrap modal, close the modal **before** showing the loading toast to avoid z-index/backdrop conflicts:

```js
bootstrap.Modal.getInstance(document.getElementById("myModal")).hide();
ToastUtils.loadingWithMinTime(
    "Saving...",
    async () => {
        /* Http.post */
    },
    800,
);
```

## Passing PHP data to JS (non-AJAX)

When a page needs PHP data in JS without a fetch/AJAX call, embed it as `data-*` attributes on the relevant HTML element. **Never use inline `<script>` blocks inside views.**

```php
<!-- view: pass a JSON array via data attribute -->
<canvas id="myChart"
    data-chart="<?= htmlspecialchars(json_encode($dataset, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>">
</canvas>

<!-- For scalar values, a plain data attribute is enough -->
<div id="myWidget" data-user-id="<?= (int) $userId ?>"></div>
```

```js
// module JS reads it — no HTML generated here
const dataset = JSON.parse(
    document.getElementById("myChart").dataset.chart || "[]",
);
const userId = parseInt(document.getElementById("myWidget").dataset.userId, 10);
```

This pattern is used in `public/js/modules/dashboard/index-dashboard.js`. Table data travels the same way: the controller builds the JSON (`$rolesJson`, `UserController`, `AuditLogController`) and the view prints it in `data-rows` on the table's `<div>`; `TableUtils.create` reads it, so no endpoint feeds the tables.

## Tom Select and validation conventions

- `ComponentUtils.initAll()` initializes every `.tom-select` and the delegated tooltips; it does not use `dropdownParent`, so a select inside a Bootstrap modal needs no extra setup.
- `FormValidator.init(form, options)` validates with native attributes (`required`, `minlength`, `pattern`) plus `equalTo` and `remote`; it is loaded by `footer.php` and `auth.php`, no plugin to request.
- For remote uniqueness validation, point `remote` at the clean routes above (e.g., `/users/check-email`, `/roles/check-name`).
- Tables: `TableUtils.create(selector, options)` (plugin `tabulator`); conventions in `AGENTS.md` ("JS helpers sin jQuery").
