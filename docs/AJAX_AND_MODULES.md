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
    <button type="submit" class="btn btn-default btn-flat">Log Out</button>
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
$this->render('users/index', $data, ['datatables', 'datatables-export'], ['users/index-users']);
```

## Plugin loading

Pass only required plugins as the third argument to `render()`:

```php
$this->render('permissions/index', $data, ['datatables', 'select2'], ['permissions/index-permissions']);
```

Asset resolution is handled by `App\Core\AssetRegistry` and rendered in `layouts/header.php` / `layouts/footer.php`.

## Load order (important)

`footer.php` loads:

1. Bootstrap + AdminLTE
2. Conditional plugin JS
3. `public/js/core/ui-components.js`
4. `public/js/core/sweetalert-utils.js` — `ToastUtils` and `AlertUtils` available globally from here
5. `public/js/modules/profile/theme-toggle.js` — dark mode toggle, available globally from here
6. Module scripts (`$module_scripts`)

Do not place page inline scripts that depend on Bootstrap plugins before `footer.php`.

Auth views (`views/auth/*.php`) do not use `footer.php`. They are pure card markup rendered via `Controller::renderStandalone($view, $data, $title, $module_scripts)`, which requires the shared `views/layouts/auth.php` — that layout owns `sweetalert-utils.js`, `dark-mode.css`, `login-dark.css`, `common-utils.js` (password toggle), and `theme-toggle.js`. Register a new auth page's module script via the `$module_scripts` argument, not by editing the layout.

## AJAX URL targets

JS modules should call the clean routes registered in `routes/web.php`:

| Action                    | Old path (removed)                                                  | New route                            |
| ------------------------- | ------------------------------------------------------------------- | ------------------------------------ |
| Check email uniqueness    | `app/controllers/users/check_email.php`                             | `POST /users/check-email`            |
| Check document uniqueness | `app/controllers/users/check_document.php`                          | `POST /users/check-document`         |
| Toggle user status        | `app/controllers/users/toggle_user_status.php`                      | `POST /users/toggle-status`          |
| Change password (AJAX)    | `app/controllers/users/ajax_change_password.php`                    | `POST /users/change-password`        |
| Create permission         | `app/controllers/permissions/create_permission_ajax.php`            | `POST /permissions/create`           |
| Update permission         | `app/controllers/permissions/update_permission_ajax.php`            | `POST /permissions/update`           |
| Toggle permission status  | `app/controllers/permissions/toggle_permission_status_ajax.php`     | `POST /permissions/toggle-status`    |
| Assign user to permission | `app/controllers/permissions/assign_user_permission_ajax.php`       | `POST /permissions/assign-user`      |
| Revoke user permission    | `app/controllers/permissions/revoke_user_permission_ajax.php`       | `POST /permissions/revoke-user`      |
| Get users without perm    | `app/controllers/permissions/get_users_without_permission_ajax.php` | `GET /permissions/get-users-without` |

## SweetAlert2 utilities

All SweetAlert2 usage must go through `public/js/core/sweetalert-utils.js`. Never call `Swal.fire()` directly in module scripts.

### ToastUtils

| Method                                                  | Purpose                                                           |
| ------------------------------------------------------- | ----------------------------------------------------------------- |
| `ToastUtils.success(title, text?)`                      | Green toast                                                       |
| `ToastUtils.error(title, text?)`                        | Red toast                                                         |
| `ToastUtils.warning(title, text?, duration?)`           | Orange toast                                                      |
| `ToastUtils.info(title, text?)`                         | Blue toast                                                        |
| `ToastUtils.loading(message)`                           | Blocking loading dialog (no timer)                                |
| `ToastUtils.loadingWithMinTime(message, action, minMs)` | Show loading, guarantee minimum display time, then run `action()` |

### AlertUtils

| Method                                                 | Purpose                                                |
| ------------------------------------------------------ | ------------------------------------------------------ |
| `AlertUtils.confirm(title, text, onConfirm, options?)` | Confirmation dialog; `options.html` for rich body text |
| `AlertUtils.confirmDelete(title, text, onConfirm)`     | Red confirm pre-configured for destructive actions     |
| `AlertUtils.welcome(name)`                             | Animated welcome popup (used at login)                 |
| `AlertUtils.image(src, alt?, confirmButtonText?)`      | Lightbox image viewer                                  |

### loadingWithMinTime pattern

Use this for every CRUD action (create, update, delete, toggle-status, assign, revoke):

```js
ToastUtils.loadingWithMinTime('Creating user...', () => {
    $.ajax({
        ...
        success: function (response) {
            if (response.success) {
                location.reload();          // Swal closes naturally with the page reload
            } else {
                Swal.close();
                ToastUtils.error('Error', response.message);
            }
        },
        error: function () {
            Swal.close();
            ToastUtils.error('Error', 'A communication error occurred with the server.');
        }
    });
}, 800);
```

### Modal-first pattern

When an AJAX action is triggered from inside a Bootstrap modal, close the modal **before** showing the loading toast to avoid z-index/backdrop conflicts:

```js
$("#myModal").modal("hide");
ToastUtils.loadingWithMinTime(
  "Saving...",
  () => {
    /* ajax */
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

This pattern is used in `public/js/modules/dashboard/index-dashboard.js`.

## Select2 and validation conventions

- `ComponentUtils.initAll()` auto-initializes `.select2` and tooltips.
- For Select2 inside Bootstrap modals, initialize explicitly with `dropdownParent`.
- Forms that use jQuery Validate should load plugin `validate`.
- Global validate config lives in `public/js/core/common-validate.js`.
- For remote uniqueness validation, point `remote` rules at the clean routes above (e.g., `/users/check-email`).
