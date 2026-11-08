# Upgrading from 3.x to 4.0.0

4.0.0 replaces the presentation layer (AdminLTE 3 / Bootstrap 4 / jQuery) with AdminLTE 4 / Bootstrap 5.3, no jQuery. The PHP side keeps its routes, controllers' contracts and database schema; **views, JS modules and CSS written against 3.x need porting**. This guide lists what to change in code you added on top of the starter.

## Before you start

```bash
composer install && npm ci && npm run assets   # Node 22 (.nvmrc); only needed in development
```

`npm run assets` copies the vendored libraries into `public/`; commit the result. Production needs neither Node nor `vendor/` dev packages.

## Removed

jQuery, jQuery Validate, DataTables (+ Buttons, JSZip, pdfmake), Select2, iCheck, Moment, Bootstrap 4 and Font Awesome. `common-datatable.js`, `common-validate.js` and `webfonts.css` are gone, and no controller may request the `datatables`, `datatables-export`, `select2` or `validate` plugins.

## Plugins and assets

| 3.x                                               | 4.0.0                                                      |
| ------------------------------------------------- | ---------------------------------------------------------- |
| `['datatables', 'datatables-export', 'validate']` | `['tabulator', 'export']` (`export` = Excel/PDF libraries) |
| `select2`                                         | `tom-select`                                               |
| Chart.js 2 (`chart`)                              | `chart` (Chart.js 4)                                       |

Register them in the `$plugins` argument of `Controller::render()`; maps live in `App\Core\AssetRegistry` and `php scripts/check-asset-paths.php` validates the paths.

## Markup (Bootstrap 4 → 5)

| 3.x                                                                                | 4.0.0                                                                             |
| ---------------------------------------------------------------------------------- | --------------------------------------------------------------------------------- |
| `data-toggle`, `data-target`, `data-dismiss`, `data-parent`                        | `data-bs-toggle`, `data-bs-target`, `data-bs-dismiss`, `data-bs-parent`           |
| `.close`                                                                           | `.btn-close`                                                                      |
| `.badge-*` / `.bg-*` on badges and headers                                         | `.text-bg-*`                                                                      |
| `.form-group`, `.input-group-append/-prepend`                                      | `.mb-3`, plain `.input-group` children                                            |
| `.custom-select`, `.custom-control*`, iCheck                                       | `.form-select`, `.form-check*`                                                    |
| `.mr-*`, `.ml-*`, `.pl-*`, `.float-right`, `.text-left/right`                      | `.me-*`, `.ms-*`, `.ps-*`, `.float-end`, `.text-start/end`                        |
| `.btn-default`, `.img-circle`, `.elevation-*`                                      | `.btn-secondary`, `.rounded-circle`, `.shadow-*`                                  |
| `.wrapper`, `.main-header`, `.main-sidebar`, `.content-wrapper`, `.content-header` | `.app-wrapper`, `.app-header`, `.app-sidebar`, `.app-main`, `.app-content-header` |
| `.card` with implicit bottom margin                                                | stacked cards in one column need `.mb-4`                                          |
| `.card-header` carrying `card-outline`                                             | `card-outline card-{color}` goes on the outer `.card`                             |
| Font Awesome `fas fa-*`                                                            | Bootstrap Icons `bi bi-*` (`fa-spin` → `.spinner-border`)                         |
| `html.dark-mode` / `body.dark-mode`                                                | `[data-bs-theme="dark"]` on `<html>`                                              |

Card titles are `h2.card-title`, one `h1` per page; do not use headings for figures or alerts.

## JavaScript

| 3.x                                | 4.0.0                                                                                                                               |
| ---------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| `$.ajax`, `$.post`                 | `Http.get/post(url, data)` → `{ok, status, data}`; it sends `X-Requested-With` and the current CSRF token                           |
| jQuery Validate rules              | `FormValidator.init(form, { … })` with native attributes, `equalTo` and `remote`                                                    |
| `.DataTable({...})` on a `<table>` | `TableUtils.create('#tableX', {...})`; the view prints rows as JSON in `data-rows` on a `<div>` and the controller builds that JSON |
| `$('select').select2()`            | `<select class="tom-select">` (or `ComponentUtils.initSelects`)                                                                     |
| `$('#modal').modal('show')`        | `bootstrap.Modal.getOrCreateInstance(el).show()`                                                                                    |
| `$(document).on('click', '.x', …)` | `document.addEventListener('click', e => e.target.closest('.x') && …)`                                                              |
| `Swal.fire`                        | still only through `ToastUtils` / `AlertUtils`                                                                                      |

A POST that does not reload the page must return `csrf_token`, because the server rotates the token after each successful POST; `Http` stores it for the next request.

## Backend behaviour that changed

- `Role::syncPermissions` replaces only the active permissions of a role; inactive assignments are kept.
- A non-admin can no longer remove from a role permissions they do not hold.
- Updating, toggling or syncing a role that does not exist answers `Role not found.`
- Create/update role responses include `csrf_token`.
- User and role list data include `manage` per row; only the allowed row actions are drawn.
- The CSRF rejection messages are now in English.

## Checklist

1. Port your views, module JS and CSS using the tables above.
2. `grep -rEi "jquery|\$\(|data-(toggle|dismiss|target|parent)=|fa[sbr]? fa-" views public/js/modules` finds nothing.
3. Add dark-mode rules under `[data-bs-theme="dark"]`, using the `--dm-*` tokens and Bootstrap's `--bs-*` variables.
4. `composer check` passes and every screen was opened in light and dark with no console errors.

See `AGENTS.md` for the full conventions and `docs/guides/AJAX_AND_MODULES.md` for the endpoint and module pattern.
