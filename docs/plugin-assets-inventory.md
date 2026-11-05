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
- **Los mapas mezclan librerías de terceros con código propio**: `datatables` incluye `core/common-datatable.js` y `validate` incluye `core/common-validate.js` (`AssetRegistry.php:58, 71`). Al reescribir los mapas en T13, decidir si los helpers first-party salen del mapa.

**Verificación de existencia: 24/24 archivos presentes (0 missing).**

## Mapa de plugins

### CSS — `getPluginCssMap()` (6 archivos, todos existen)

| Clave        | Archivos                                           | Nota migración                                               |
| ------------ | -------------------------------------------------- | ------------------------------------------------------------ |
| `datatables` | `plugins/datatables/datatables.min.css`            | genérico, sirve para BS5                                     |
|              | `plugins/datatables/dataTables.bootstrap4.min.css` | ⚠️ integración **BS4** → se elimina con DataTables (C7, T49) |
|              | `plugins/datatables/responsive.bootstrap4.min.css` | ⚠️ **BS4** → se elimina con DataTables (C7, T49)             |
|              | `plugins/datatables/buttons.bootstrap4.min.css`    | ⚠️ **BS4** → se elimina con DataTables (C7, T49)             |
| `select2`    | `plugins/select2/select2.min.css`                  | genérico                                                     |
|              | `plugins/select2/select2-bootstrap4.min.css`       | ⚠️ tema **BS4** → se elimina con Select2 (C5, T49)           |

### JS — `getPluginJsMap()` (18 archivos, todos existen)

| Clave               | Archivos                                                                   | Nota migración                                   |
| ------------------- | -------------------------------------------------------------------------- | ------------------------------------------------ |
| `datatables`        | `jquery.dataTables.min.js`                                                 | núcleo, jQuery; se sustituye por Tabulator (C7)  |
|                     | `dataTables.bootstrap4.min.js`                                             | ⚠️ **BS4** → se elimina con DataTables (C7, T49) |
|                     | `dataTables.responsive.min.js`                                             | genérico                                         |
|                     | `responsive.bootstrap4.min.js`                                             | ⚠️ **BS4** → se elimina con DataTables (C7, T49) |
|                     | `dataTables.buttons.min.js`                                                | genérico                                         |
|                     | `buttons.bootstrap4.min.js`                                                | ⚠️ **BS4** → se elimina con DataTables (C7, T49) |
|                     | `buttons.html5.min.js` · `buttons.print.min.js` · `buttons.colVis.min.js`  | genéricos (export)                               |
|                     | `core/common-datatable.js`                                                 | **código propio** dentro del mapa                |
| `datatables-export` | `plugins/utils/jszip.min.js` · `pdfmake.min.js` · `vfs_fonts.js`           | requeridos por `buttons.html5`; sin CSS          |
| `select2`           | `plugins/select2/select2.min.js`                                           | jQuery; se sustituye por Tom Select (C5)         |
| `validate`          | `plugins/validations/jquery.validate.min.js` · `additional-methods.min.js` | jQuery; se sustituye por validación nativa (C11) |
|                     | `core/common-validate.js`                                                  | **código propio** dentro del mapa                |
| `chart`             | `plugins/chart/Chart.js`                                                   | sin jQuery; sin CSS                              |

**Resumen de ataduras BS4 en el registry: 7 archivos** (4 CSS + 3 JS de integración DataTables… ver nota) — en total: `dataTables.bootstrap4.css/js`, `responsive.bootstrap4.css/js`, `buttons.bootstrap4.css/js`, `select2-bootstrap4.min.css` = **7**.

## Assets globales (fuera del registry)

### `views/layouts/header.php` (páginas con layout de app)

| Línea | Asset                                                                  | Migración                                                      |
| ----- | ---------------------------------------------------------------------- | -------------------------------------------------------------- |
| 24    | `css/lib/bootstrap/bootstrap.min.css` (v5.0.2)                         | eliminar: AdminLTE 4 ya incluye el CSS de Bootstrap 5.3.8 (T8) |
| 26-27 | `css/lib/fontawesome/all.min.css` + `css/core/webfonts.css`            | **se elimina**: Bootstrap Icons (C8, T8/T21, T49)              |
| 30    | `css/lib/adminlte/adminlte.min.css` (v3, **incluye BS4.6.1 embebido**) | reemplazar por v4 (T8, T14)                                    |
| 32    | `css/core/ui-components.css`                                           | sin cambio                                                     |
| 34    | `css/core/dark-mode.css`                                               | reescribir sobre `[data-bs-theme]` (T44)                       |
| 43-44 | SweetAlert2 CSS + JS (hardcodeado, **siempre**)                        | sin cambio                                                     |
| 46    | `js/lib/jquery/jquery.min.js`                                          | **se elimina** (C2, NFR-7)                                     |
| 49-53 | `$module_styles` → `css/modules/<x>.css`                               | sin cambio                                                     |

### `views/layouts/footer.php`

| Línea | Asset                                                   | Migración                                                       |
| ----- | ------------------------------------------------------- | --------------------------------------------------------------- |
| 25    | `js/lib/bootstrap/bootstrap.bundle.min.js` (**v4.6.1**) | reemplazar por Bootstrap 5.3.8 bundle, con Popper incluido (T8) |
| 28    | `js/lib/adminlte/adminlte.min.js` (v3, requiere jQuery) | reemplazar v4 (T8, T16)                                         |
| 31    | `js/core/common-utils.js`                               | T23                                                             |
| 36-39 | JS de plugins vía `resolvePluginJs($plugins)`           | T13                                                             |
| 42    | `js/core/ui-components.js`                              | T25                                                             |
| 45    | `js/core/sweetalert-utils.js`                           | T28                                                             |
| 48    | `js/modules/profile/theme-toggle.js`                    | T18                                                             |
| 53    | `$module_scripts` → `js/modules/<x>.js`                 | T29-T43                                                         |

### `views/layouts/auth.php` (login/forgot/reset/accept — hardcodeado, sin registry)

| Línea     | Asset                                                           | Nota                                          |
| --------- | --------------------------------------------------------------- | --------------------------------------------- |
| 22-24     | Bootstrap CSS, Font Awesome, AdminLTE CSS                       | mismos que header (T8, T14)                   |
| 25        | `css/lib/bootstrap/icheck-bootstrap.min.css`                    | tema BS4 — reemplazar por `.form-check` (T42) |
| 26, 28-30 | webfonts, dark-mode, `login.css`, `login-dark.css`              | T18, T44, T46                                 |
| 31-33     | SweetAlert2 CSS/JS + `sweetalert-utils.js`                      | —                                             |
| 46-48     | jQuery, Bootstrap bundle (BS4), AdminLTE JS                     | T8, T16                                       |
| 49-51     | `jquery.validate` + `additional-methods` + `common-validate.js` | hardcodeado aquí, **no** pasa por registry    |
| 52, 56    | `common-utils.js`, `theme-toggle.js`                            | T23, T18                                      |
| 53-55     | `$module_scripts` → `js/modules/auth/<x>.js`                    | T42, T43                                      |

### `views/layouts/error.php` (403/404/500)

Solo Font Awesome, `webfonts.css`, `dark-mode.css`, `errors.css`, `errors-dark.css`. **Sin Bootstrap, sin jQuery, sin plugins.**

## Matriz de plugins por página

| Página (controller) | `$plugins`                                    | `$module_scripts`                                               | `$module_styles`          |
| ------------------- | --------------------------------------------- | --------------------------------------------------------------- | ------------------------- |
| Dashboard           | `chart`                                       | `dashboard/index-dashboard`                                     | `dashboard/dashboard`     |
| Users index         | `datatables`, `datatables-export`             | `users/index-users`                                             | `users/users`             |
| Users create        | `select2`, `validate`                         | `users/create-user`                                             | `users/users`             |
| Users update        | `select2`, `validate`                         | `users/update-user`                                             | `users/users`             |
| Users show          | _(ninguno)_                                   | `users/show-user`                                               | `users/users`             |
| Users profile       | _(ninguno)_                                   | `users/profile-user`                                            | `users/users`             |
| Roles index         | `datatables`, `datatables-export`, `validate` | `roles/modal-role`, `roles/index-roles`                         | `roles/roles`             |
| Roles detail        | `validate`                                    | `roles/detail-role`                                             | —                         |
| Permissions index   | `datatables`, `datatables-export`, `validate` | `permissions/modal-permission`, `permissions/index-permissions` | `permissions/permissions` |
| Permissions detail  | `datatables`, `select2`, `validate`           | `permissions/modal-permission`, `permissions/detail-permission` | `permissions/permissions` |
| Audit log           | `datatables`, `datatables-export`, `select2`  | `audit-log/index-audit`                                         | `audit-log/audit-log`     |
| Auth (4 páginas)    | _(hardcodeado en `auth.php`)_                 | `auth/<página>`                                                 | —                         |
| Errors              | _(ninguno)_                                   | —                                                               | —                         |

Notas de la matriz:

- **`select2` en Audit log** se usa: la vista declara `<select class="select2">` en los filtros (`views/audit-log/index.php:43, 59, 75`) y `ComponentUtils.initAll()` los auto-inicializa (`ui-components.js:358`).
- **`datatables` + `validate` en Permissions detail**: ambos necesarios (tabla de usuarios + modal assign/revoke).
- Ninguna página pide `chart` fuera del dashboard.

## Hallazgos

1. **7 archivos atados a Bootstrap 4** en el registry (3 CSS y 3 JS de integración DataTables, más el tema Select2 CSS = **7**) — sustitución obligatoria en **T13** por los builds `bs5` y el tema oficial `adminlte-select2`; sin ellos la tabla/perfil de DataTables y el Select2 quedarán sin estilo.
2. **Código propio dentro de los mapas de plugins** (`core/common-datatable.js`, `core/common-validate.js`) — considerar sacarlos en T13 para que el mapa represente solo librerías de terceros.
3. **`public/js/plugins/moment/` (`moment.min.js` + `es.js`) está sin usar**: cero referencias en `app/`, `views/` ni código propio de `public/js/core|modules`. Candidato a eliminar (T49).
4. **Duplicado iCheck**: existen `css/lib/bootstrap/icheck-bootstrap.min.css` (usado por `auth.php:25`) y `css/plugins/icheck-bootstrap/icheck-bootstrap.min.css` (sin referencias). Conservar el de `lib/`, eliminar el de `plugins/` o eliminar ambos al migrar el login (T42, T49).
5. **`auth.php` no pasa por `AssetRegistry`**: los assets de las páginas standalone están hardcodeados (validate incluido). Si en el futuro se quiere unificar el criterio de carga, hacerlo en T13/T19 sin romper FR-8.
6. **El orden de carga actual es correcto para jQuery**: `jquery.min.js` (header:46 / auth:46) precede a los plugins del footer y a los módulos — condición que debe mantenerse tras la migración (T8/T9).
7. **`resolve()` ignora claves desconocidas sin avisar** (`AssetRegistry.php:113-114`): un typo en `$plugins` falla en silencio. Comportamiento a conservar o endurecer con un `trigger_error`/log en T13 (decisión, no bloqueante).

## Mapa a tareas

- **T6-T12** → archivos de `public/css/lib/`, `public/js/lib/` y `public/**/plugins/` (versiones en `plan.md`)
- **T14-T16** → reescritura de los assets globales de `header.php` / `footer.php`
- **T13** → reescribir `getPluginCssMap()` / `getPluginJsMap()` (mapas nuevos para Tabulator, Tom Select, SheetJS, jsPDF y Chart.js 4; sin helpers propios dentro del mapa; los 7 archivos BS4, `moment` e iCheck se borran en T49)
- **T19** → assets hardcodeados de `auth.php` (validate sin registry)
- **T49** → borrado de archivos obsoletos
