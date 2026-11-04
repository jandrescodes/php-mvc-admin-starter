# Inventario de plugins y assets en `AssetRegistry.php` (T3 — Spec 001)

> Migración AdminLTE 3.2.0 → 4.10.1 · Alcance: `app/Core/AssetRegistry.php` + layouts que cargan assets globales + matriz de plugins por página.

## Método

- Lectura completa de `AssetRegistry.php` (mapas CSS/JS + funciones `resolve*`).
- Existencia en disco verificada archivo por archivo (`public/css/<ruta>` para CSS, `public/js/<ruta>` para JS).
- Matriz de uso: tercer argumento (`$plugins`), cuarto (`$module_scripts`) y quinto (`$module_styles`) de `Controller::render()` en cada controller, más los assets hardcodeados de `header.php`, `footer.php`, `auth.php` y `error.php`.

## Estructura actual de `AssetRegistry`

- `getPluginCssMap(): array` — 2 claves → 6 archivos CSS (`AssetRegistry.php:26-38`)
- `getPluginJsMap(): array` — 5 claves → 18 archivos JS (`AssetRegistry.php:47-76`)
- `resolvePluginCss($plugins)` / `resolvePluginJs($plugins)` → `resolve()` itera las claves pedidas y concatena sus rutas; claves desconocidas se ignoran silenciosamente (`AssetRegistry.php:108-122`).
- **Los mapas mezclan librerías de terceros con código propio**: `datatables` incluye `core/common-datatable.js` y `validate` incluye `core/common-validate.js` (`AssetRegistry.php:58, 71`). Al reescribir los mapas en T41, decidir si los helpers first-party salen del mapa.

**Verificación de existencia: 24/24 archivos presentes (0 missing).**

## Mapa de plugins

### CSS — `getPluginCssMap()` (6 archivos, todos existen)

| Clave | Archivos | Nota migración |
|---|---|---|
| `datatables` | `plugins/datatables/datatables.min.css` | genérico, sirve para BS5 |
| | `plugins/datatables/dataTables.bootstrap4.min.css` | ⚠️ integración **BS4** → sustituir por `dataTables.bootstrap5` (T41) |
| | `plugins/datatables/responsive.bootstrap4.min.css` | ⚠️ **BS4** → `responsive.bootstrap5` (T41) |
| | `plugins/datatables/buttons.bootstrap4.min.css` | ⚠️ **BS4** → `buttons.bootstrap5` (T41) |
| `select2` | `plugins/select2/select2.min.css` | genérico |
| | `plugins/select2/select2-bootstrap4.min.css` | ⚠️ tema **BS4** → tema Select2 para BS5 (T41) |

### JS — `getPluginJsMap()` (18 archivos, todos existen)

| Clave | Archivos | Nota migración |
|---|---|---|
| `datatables` | `jquery.dataTables.min.js` | núcleo, jQuery (C2 se conserva) |
| | `dataTables.bootstrap4.min.js` | ⚠️ **BS4** → `.bootstrap5` (T41) |
| | `dataTables.responsive.min.js` | genérico |
| | `responsive.bootstrap4.min.js` | ⚠️ **BS4** → `.bootstrap5` (T41) |
| | `dataTables.buttons.min.js` | genérico |
| | `buttons.bootstrap4.min.js` | ⚠️ **BS4** → `.bootstrap5` (T41) |
| | `buttons.html5.min.js` · `buttons.print.min.js` · `buttons.colVis.min.js` | genéricos (export) |
| | `core/common-datatable.js` | **código propio** dentro del mapa |
| `datatables-export` | `plugins/utils/jszip.min.js` · `pdfmake.min.js` · `vfs_fonts.js` | requeridos por `buttons.html5`; sin CSS |
| `select2` | `plugins/select2/select2.min.js` | jQuery (C2) |
| `validate` | `plugins/validations/jquery.validate.min.js` · `additional-methods.min.js` | jQuery (C2) |
| | `core/common-validate.js` | **código propio** dentro del mapa |
| `chart` | `plugins/chart/Chart.js` | sin jQuery; sin CSS |

**Resumen de ataduras BS4 en el registry: 7 archivos** (4 CSS + 3 JS de integración DataTables… ver nota) — en total: `dataTables.bootstrap4.css/js`, `responsive.bootstrap4.css/js`, `buttons.bootstrap4.css/js`, `select2-bootstrap4.min.css` = **7**.

## Assets globales (fuera del registry)

### `views/layouts/header.php` (páginas con layout de app)

| Línea | Asset | Migración |
|---|---|---|
| 24 | `css/lib/bootstrap/bootstrap.min.css` (v5.0.2) | unificar con v5.3.8 (T8) |
| 26-27 | `css/lib/fontawesome/all.min.css` + `css/core/webfonts.css` | sin cambio |
| 30 | `css/lib/adminlte/adminlte.min.css` (v3, **incluye BS4.6.1 embebido**) | reemplazar por v4 (T7/T9) |
| 32 | `css/core/ui-components.css` | sin cambio |
| 34 | `css/core/dark-mode.css` | revisar tokens v4 (T37) |
| 43-44 | SweetAlert2 CSS + JS (hardcodeado, **siempre**) | sin cambio |
| 46 | `js/lib/jquery/jquery.min.js` | **se conserva** (C2) |
| 49-53 | `$module_styles` → `css/modules/<x>.css` | sin cambio |

### `views/layouts/footer.php`

| Línea | Asset | Migración |
|---|---|---|
| 25 | `js/lib/bootstrap/bootstrap.bundle.min.js` (**v4.6.1**) | reemplazar v5.3.8 + Popper (T10) |
| 28 | `js/lib/adminlte/adminlte.min.js` (v3, requiere jQuery) | reemplazar v4 (T7/T13) |
| 31 | `js/core/common-utils.js` | T20 |
| 36-39 | JS de plugins vía `resolvePluginJs($plugins)` | T41 |
| 42 | `js/core/ui-components.js` | T19 |
| 45 | `js/core/sweetalert-utils.js` | T22 |
| 48 | `js/modules/profile/theme-toggle.js` | T23 |
| 53 | `$module_scripts` → `js/modules/<x>.js` | T24-T36 |

### `views/layouts/auth.php` (login/forgot/reset/accept — hardcodeado, sin registry)

| Línea | Asset | Nota |
|---|---|---|
| 22-24 | Bootstrap CSS, Font Awesome, AdminLTE CSS | mismos que header (T7/T9) |
| 25 | `css/lib/bootstrap/icheck-bootstrap.min.css` | tema BS4 — revisar con BS5 (T45) |
| 26, 28-30 | webfonts, dark-mode, `login.css`, `login-dark.css` | T17/T37 |
| 31-33 | SweetAlert2 CSS/JS + `sweetalert-utils.js` | — |
| 46-48 | jQuery, Bootstrap bundle (BS4), AdminLTE JS | T10/T13 |
| 49-51 | `jquery.validate` + `additional-methods` + `common-validate.js` | hardcodeado aquí, **no** pasa por registry |
| 52, 56 | `common-utils.js`, `theme-toggle.js` | T20/T23 |
| 53-55 | `$module_scripts` → `js/modules/auth/<x>.js` | T49 |

### `views/layouts/error.php` (403/404/500)

Solo Font Awesome, `webfonts.css`, `dark-mode.css`, `errors.css`, `errors-dark.css`. **Sin Bootstrap, sin jQuery, sin plugins.**

## Matriz de plugins por página

| Página (controller) | `$plugins` | `$module_scripts` | `$module_styles` |
|---|---|---|---|
| Dashboard | `chart` | `dashboard/index-dashboard` | `dashboard/dashboard` |
| Users index | `datatables`, `datatables-export` | `users/index-users` | `users/users` |
| Users create | `select2`, `validate` | `users/create-user` | `users/users` |
| Users update | `select2`, `validate` | `users/update-user` | `users/users` |
| Users show | *(ninguno)* | `users/show-user` | `users/users` |
| Users profile | *(ninguno)* | `users/profile-user` | `users/users` |
| Roles index | `datatables`, `datatables-export`, `validate` | `roles/modal-role`, `roles/index-roles` | `roles/roles` |
| Roles detail | `validate` | `roles/detail-role` | — |
| Permissions index | `datatables`, `datatables-export`, `validate` | `permissions/modal-permission`, `permissions/index-permissions` | `permissions/permissions` |
| Permissions detail | `datatables`, `select2`, `validate` | `permissions/modal-permission`, `permissions/detail-permission` | `permissions/permissions` |
| Audit log | `datatables`, `datatables-export`, `select2` | `audit-log/index-audit` | `audit-log/audit-log` |
| Auth (4 páginas) | *(hardcodeado en `auth.php`)* | `auth/<página>` | — |
| Errors | *(ninguno)* | — | — |

Notas de la matriz:

- **`select2` en Audit log** se usa: la vista declara `<select class="select2">` en los filtros (`views/audit-log/index.php:43, 59, 75`) y `ComponentUtils.initAll()` los auto-inicializa (`ui-components.js:358`).
- **`datatables` + `validate` en Permissions detail**: ambos necesarios (tabla de usuarios + modal assign/revoke).
- Ninguna página pide `chart` fuera del dashboard.

## Hallazgos

1. **7 archivos atados a Bootstrap 4** en el registry (4 CSS + 3 JS de integración, más el tema Select2) — sustitución obligatoria en **T41** por equivalentes `.bootstrap5` / tema BS5; sin ellos la tabla/perfil de DataTables y el Select2 quedarán sin estilo.
2. **Código propio dentro de los mapas de plugins** (`core/common-datatable.js`, `core/common-validate.js`) — considerar sacarlos en T41 para que el mapa represente solo librerías de terceros.
3. **`public/js/plugins/moment/` (`moment.min.js` + `es.js`) está sin usar**: cero referencias en `app/`, `views/` ni código propio de `public/js/core|modules`. Candidato a eliminar (junto con la limpieza de T41).
4. **Duplicado iCheck**: existen `css/lib/bootstrap/icheck-bootstrap.min.css` (usado por `auth.php:25`) y `css/plugins/icheck-bootstrap/icheck-bootstrap.min.css` (sin referencias). Conservar el de `lib/`, eliminar el de `plugins/` o unificar al migrar el login (T45).
5. **`auth.php` no pasa por `AssetRegistry`**: los assets de las páginas standalone están hardcodeados (validate incluido). Si en el futuro se quiere unificar el criterio de carga, hacerlo en T41/T48 sin romper FR-8.
6. **El orden de carga actual es correcto para jQuery**: `jquery.min.js` (header:46 / auth:46) precede a los plugins del footer y a los módulos — condición que debe mantenerse tras la migración (T9/T10).
7. **`resolve()` ignora claves desconocidas sin avisar** (`AssetRegistry.php:113-114`): un typo en `$plugins` falla en silencio. Comportamiento a conservar o endurecer con un `trigger_error`/log en T41 (decisión, no bloqueante).

## Mapa a tareas

- **T7/T8** → archivos de `public/css/lib/` y `public/js/lib/` (AdminLTE 4 + BS5 + Popper + OverlayScrollbars + Bootstrap Icons)
- **T9/T10** → reescritura de los assets globales de `header.php` / `footer.php`
- **T41** → reescribir `getPluginCssMap()` / `getPluginJsMap()` (sustituir 7 archivos BS4, valorar sacar helpers propios, limpieza `moment`/iCheck duplicado)
- **T42/T43** → verificar `resolvePlugin*()` y referencias en views
- **T49** → assets hardcodeados de `auth.php` (validate sin registry)
