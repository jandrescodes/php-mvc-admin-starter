# Inventario de usos de jQuery (`$` / `jQuery`) en `public/js/` (T2 — Spec 001)

> Migración AdminLTE 3.2.0 → 4.10.1 · Alcance: código propio en `public/js/core/` y `public/js/modules/` (23 archivos, 3.203 líneas).

## Método

- Detección de líneas que contienen `$(` / `jQuery(` o estáticos `$.nombre` (comentarios de línea descartados).
- «Usos» = unión de líneas con llamada `$()` y líneas con `$.estático`; se lista el número de línea de cada una.
- Excluidos del conteo: `public/js/lib/` (runtime vendor) y `public/js/plugins/` (librerías de terceros) — inventariados al final bajo «Contexto».
- No hay alias `jQuery(` ni `noConflict` en el código propio: todo usa `$`.

## Contexto de carga (orden real)

1. `views/layouts/header.php:46` y `views/layouts/auth.php:46` → `js/lib/jquery/jquery.min.js`
2. `views/layouts/footer.php:25` → `js/lib/bootstrap/bootstrap.bundle.min.js` (**v4.6.1**, JS Bootstrap 4)
3. `views/layouts/footer.php:28` → `js/lib/adminlte/adminlte.min.js` (AdminLTE 3, **requiere jQuery**)
4. `views/layouts/footer.php:31` → `core/common-utils.js`
5. `views/layouts/footer.php:36-39` → JS de plugins según página (`AssetRegistry::resolvePluginJs()`)
6. `views/layouts/footer.php:42` → `core/ui-components.js` (después de los plugins para que existan Select2/tooltip)
7. `views/layouts/footer.php:45` → `core/sweetalert-utils.js` · `:48` → `modules/profile/theme-toggle.js` · `:53` → scripts del módulo de la página

jQuery está disponible antes que todo el código propio. AdminLTE 4 ya no necesita jQuery en su JS, pero **se conserva** (C2) por Select2, DataTables y jQuery Validate.

## Resumen

| Archivo | Líneas | Usos | Estáticos `$.x` | Plugins jQuery | Nativo | Tarea |
|---|---|---|---|---|---|---|
| `core/common-datatable.js` | 225 | 5 | `$.extend` ×3 | — | — | sin tarea propia¹ |
| `core/common-utils.js` | 157 | 4 | `$.ajax` ×1 | — | `addEventListener` ×2 | T20 |
| `core/common-validate.js` | 55 | 4 | `$.validator` ×1 | — | — | T21 |
| `core/sweetalert-utils.js` | 365 | **0** | — | — | — | T22 |
| `core/ui-components.js` | 367 | 33 | `$.fn` ×5 | `.select2` ×5, `.tooltip` ×5 | — | T19 |
| `modules/audit-log/index-audit.js` | 196 | 23 | — | `.DataTable` ×1, `.modal` ×1 | — | T35 |
| `modules/auth/accept-invitation.js` | 40 | 5 | — | `.validate` ×1 | — | **sin tarea²** |
| `modules/auth/forgot_password.js` | 29 | 5 | — | `.validate` ×1 | — | **sin tarea²** |
| `modules/auth/login.js` | 39 | 5 | — | `.validate` ×1 | — | **sin tarea²** |
| `modules/auth/reset_password.js` | 39 | 5 | — | `.validate` ×1 | — | **sin tarea²** |
| `modules/dashboard/index-dashboard.js` | 330 | **0** | — | — | `DOMContentLoaded` ×1, `addEventListener` ×2, `classList` ×6 | T36 |
| `modules/permissions/detail-permission.js` | 111 | 17 | `$.ajax` ×2 | `.DataTable` ×1, `.modal` ×2 | — | T33 |
| `modules/permissions/index-permissions.js` | 83 | 16 | `$.ajax` ×1 | `.DataTable` ×1, `.modal` ×1 | — | T32 |
| `modules/permissions/modal-permission.js` | 139 | 29 | `$.ajax` ×1 | `.modal` ×2, `.validate` ×2 | — | T34 |
| `modules/profile/theme-toggle.js` | 65 | **0** | — | — | `DOMContentLoaded` ×1, `addEventListener` ×3, `classList` ×5, `querySelector` ×1 | T23 |
| `modules/roles/detail-role.js` | 55 | 10 | `$.param` ×1, `$.post` ×1 | — | — | T30 |
| `modules/roles/index-roles.js` | 80 | 15 | `$.ajax` ×1 | `.DataTable` ×1, `.modal` ×1 | — | T29 |
| `modules/roles/modal-role.js` | 132 | 26 | `$.ajax` ×1 | `.modal` ×2, `.validate` ×2 | — | T31 |
| `modules/users/create-user.js` | 200 | 50 | — | `.validate` ×1 | — | T25 |
| `modules/users/index-users.js` | 129 | 4 | `$.ajax` ×2 | `.DataTable` ×1 | `DOMContentLoaded` ×1, `addEventListener` ×3, `classList` ×7 | T24 |
| `modules/users/profile-user.js` | 102 | **0** | — | — | `addEventListener` ×3, `fetch` ×1, `querySelector` ×2 | T28 |
| `modules/users/show-user.js` | 87 | 13 | `$.ajax` ×2 | `.tab` ×1 | — | T27 |
| `modules/users/update-user.js` | 179 | 37 | — | `.validate` ×1 | — | T26 |

**Total: 306 líneas con uso de jQuery en 19 de 23 archivos.** 4 archivos son 100 % nativos.

¹ Compartido por las migraciones de DataTables (T24/T29/T32/T35); solo `$.extend` de configuración, sin cambio previsto salvo por la integración Bootstrap de DataTables (T41).
² Ver «Hallazgos» §7.

## Hallazgos

1. **4 archivos ya son 100 % nativos** y no requieren migración: `sweetalert-utils.js` (Swal puro), `dashboard/index-dashboard.js`, `theme-toggle.js` y `profile-user.js`.
2. **`profile-user.js` es el modelo nativo del proyecto**: `addEventListener`, `querySelector` y `fetch` (línea con `fetch` presente). Es la referencia de estilo para «nativo para lo nuevo» (FR-30 / T20).
3. **Código muerto:** `ajaxRequest()` (`common-utils.js:129`) define un wrapper `$.ajax` que **no se usa en ningún sitio** (ni `public/` ni `views/`). Candidato a eliminar en T20.
4. **Feature detection defensiva:** `ui-components.js` ya guarda `typeof $ !== 'undefined'` y `typeof $.fn.select2 !== 'undefined'` antes de tocar jQuery (líneas 72, 120, 201, 224, 294) — patrón a conservar.
5. **Selectores JS con atributos Bootstrap 4** (renombre BS5 obligatorio):
   - `ui-components.js:217, 220, 246, 290, 292, 332` → `[data-toggle="tooltip"]` debe pasar a `[data-bs-toggle="tooltip"]` (T19).
   - `show-user.js:12` → `a[data-toggle="pill"]` debe pasar a `a[data-bs-toggle="pill"]` (T27).
6. **Envío de formularios create/update user nativo:** `create-user.js:109` y `update-user.js:88` hacen `form.submit()` dentro del `submitHandler` de jQuery Validate — no usan `$.ajax`; solo el botón/envoltorio usa `$`.
7. **Los 4 módulos `auth/` no tenían tarea asignada** en `tasks.md` (hueco detectado en T2). Su jQuery se reduce a `$(document).ready` + `.validate(...)` (configuración de jQuery Validate, conservada por C2). **Resuelto: añadida T49** en `tasks.md` para confirmarlos sin cambios de selectores (FR-8, FR-27).
8. `common-datatable.js` no tiene tarea propia: solo aporta objetos de configuración (`$.extend`) usados por los módulos con `.DataTable(...)`.

## Detalle por archivo — líneas con uso de jQuery

### Core compartido

- **`core/common-datatable.js`** (225 ln) — 5 usos
  - Líneas: 131, 161, 165, 174, 224 · Estáticos: `$.extend` en 165, 174, 224
- **`core/common-utils.js`** (157 ln) — 4 usos
  - Líneas: 47, 48, 49, 130 · Estáticos: `$.ajax` en 130 (`ajaxRequest`, **sin usos**)
  - 47-49: delegación `$(document).on('click', '[data-password-toggle]', …)` (patrón a conservar en T20)
- **`core/common-validate.js`** (55 ln) — 4 usos
  - Líneas: 12, 35, 39, 44 · Estáticos: `$.validator` en 12
- **`core/sweetalert-utils.js`** (365 ln) — **sin jQuery**
- **`core/ui-components.js`** (367 ln) — 33 usos
  - Líneas: 72, 73, 86, 87, 120, 124, 148, 151, 152, 154, 158, 163, 164, 175, 177, 201, 202, 203, 204, 224, 225, 243, 246, 274, 277, 278, 282, 283, 294, 295, 296, 356, 362
  - Estáticos: `$.fn` en 72, 120, 201, 224, 294 · Plugins: `.select2(` en 87, 98, 128, 132, 204; `.tooltip(` en 243, 246, 278, 283, 296
  - 356: `$(function () { … })` (ready global)

### Módulos

- **`audit-log/index-audit.js`** (196 ln) — 23 usos
  - Líneas: 18, 32, 39, 40, 67, 97, 112, 153, 160, 161, 162, 163, 164, 165, 166, 170, 171, 173, 183, 185, 188, 192, 193
  - Ready en 18 · Plugins: `.DataTable(` en 32, `.modal(` en 188
- **`auth/accept-invitation.js`** (40 ln) — 5 usos · Líneas: 5, 6, 8, 32, 33 · Ready 5 · `.validate(` 8
- **`auth/forgot_password.js`** (29 ln) — 5 usos · Líneas: 1, 2, 6, 21, 22 · Ready 1 · `.validate(` 6
- **`auth/login.js`** (39 ln) — 5 usos · Líneas: 1, 4, 8, 31, 32 · Ready 1 · `.validate(` 8
- **`auth/reset_password.js`** (39 ln) — 5 usos · Líneas: 1, 2, 6, 31, 32 · Ready 1 · `.validate(` 6
- **`dashboard/index-dashboard.js`** (330 ln) — **sin jQuery** (nativo: `DOMContentLoaded`, `addEventListener`, `classList`)
- **`permissions/detail-permission.js`** (111 ln) — 17 usos
  - Líneas: 13, 14, 23, 27, 28, 29, 33, 34, 40, 43, 65, 66, 70, 71, 72, 73, 81
  - Ready 13 · `$.ajax` 43, 81 · `.DataTable(` 23 · `.modal(` 29, 40
- **`permissions/index-permissions.js`** (83 ln) — 16 usos
  - Líneas: 16, 18, 22, 23, 24, 25, 26, 28, 29, 30, 33, 37, 38, 39, 40, 55
  - Ready 16 · `$.ajax` 55 · `.DataTable(` 18 · `.modal(` 33
- **`permissions/modal-permission.js`** (139 ln) — 29 usos
  - Líneas: 16, 21, 30, 31, 58, 59, 60, 61, 63, 64, 65, 66, 68, 69, 70, 73, 85, 86, 91, 97, 100, 128, 130, 131, 132, 133, 134, 135, 136
  - Ready 16 · `$.ajax` 100 · `.validate(` 21, 131 · `.modal(` 73, 97
- **`profile/theme-toggle.js`** (65 ln) — **sin jQuery**
- **`roles/detail-role.js`** (55 ln) — 10 usos
  - Líneas: 4, 5, 11, 12, 15, 16, 27, 30, 37, 43 · Ready 4 · Estáticos: `$.param` y `$.post` en 30
- **`roles/index-roles.js`** (80 ln) — 15 usos
  - Líneas: 14, 16, 20, 21, 22, 23, 25, 26, 27, 30, 34, 35, 36, 37, 52
  - Ready 14 · `$.ajax` 52 · `.DataTable(` 16 · `.modal(` 30
- **`roles/modal-role.js`** (132 ln) — 26 usos
  - Líneas: 16, 21, 30, 31, 57, 58, 59, 60, 62, 63, 64, 65, 67, 68, 69, 72, 84, 85, 88, 94, 97, 125, 127, 128, 129, 130
  - Ready 16 · `$.ajax` 97 · `.validate(` 21, 128 · `.modal(` 72, 94
- **`users/create-user.js`** (200 ln) — 50 usos (el archivo más jQuery-bound)
  - Líneas: 13, 16, 17, 18, 21, 22, 23, 24, 29, 30, 31, 32, 34, 36, 43, 54, 55, 67, 73, 74, 105, 115, 116, 119, 120, 125, 126, 127, 132, 133, 134, 141, 142, 143, 163, 164, 165, 166, 172, 175, 176, 177, 180, 181, 183, 185, 190, 191, 195, 196
  - Ready 13 · `.validate(` 43 · envío nativo `form.submit()` en 109
- **`users/index-users.js`** (129 ln) — 4 usos
  - Líneas: 12, 17, 38, 78 · Ready 12 · `$.ajax` 38, 78 · `.DataTable(` 17
  - Además 6 marcadores nativos (`addEventListener` ×3, `DOMContentLoaded`, `classList` ×7) — ya mixto
- **`users/profile-user.js`** (102 ln) — **sin jQuery** (nativo con `fetch`)
- **`users/show-user.js`** (87 ln) — 13 usos
  - Líneas: 7, 8, 12, 13, 17, 18, 22, 23, 24, 31, 56, 57, 66
  - `$.ajax` 31, 66 · `.tab(` 8 · **12: selector BS4 `a[data-toggle="pill"]`** (renomer a `data-bs-toggle`, Hallazgo 5)
- **`users/update-user.js`** (179 ln) — 37 usos
  - Líneas: 13, 16, 18, 29, 30, 42, 53, 84, 94, 95, 98, 99, 104, 105, 106, 111, 112, 113, 120, 121, 122, 142, 143, 144, 145, 151, 154, 155, 156, 159, 160, 162, 164, 169, 170, 174, 175
  - Ready 13 · `.validate(` 18 · envío nativo `form.submit()` en 88

## Estáticos jQuery (hotspots para T20)

| Estático | Usos | Ubicaciones |
|---|---|---|
| `$.ajax` | 11 | `common-utils.js:130` (muerto) · `detail-permission.js:43, 81` · `index-permissions.js:55` · `modal-permission.js:100` · `index-roles.js:52` · `modal-role.js:97` · `index-users.js:38, 78` · `show-user.js:31, 66` |
| `$.post` + `$.param` | 1 + 1 | `detail-role.js:30` |
| `$.extend` | 3 | `common-datatable.js:165, 174, 224` |
| `$.validator` | 1 | `common-validate.js:12` |
| `$.fn` (feature detection) | 5 | `ui-components.js:72, 120, 201, 224, 294` |

## Llamadas a plugins jQuery (permanecen en jQuery — C2)

| Plugin | Llamadas | Archivos |
|---|---|---|
| `.select2(` | 5 | `ui-components.js` |
| `.DataTable(` | 5 | `index-audit`, `detail-permission`, `index-permissions`, `index-roles`, `index-users` |
| `.validate(` | 10 | 4× `auth/*` + `modal-permission` ×2 + `modal-role` ×2 + `create-user` + `update-user` |
| `.modal(` | 9 | `index-audit`, `detail-permission` ×2, `index-permissions`, `modal-permission` ×2, `index-roles`, `modal-role` ×2 |
| `.tooltip(` | 5 | `ui-components.js` |
| `.tab(` | 1 | `show-user.js` |

`.modal` / `.tab` / `.tooltip` dependen del **JS de Bootstrap**: con BS5 siguen existiendo pero requieren el markup/atributos `data-bs-*` (ver Hallazgo 5) y el orden de carga correcto (T19, T41).

## Contexto — runtime y terceros

### `public/js/lib/` (runtime, no «nuestro» código)

| Archivo | Estado | Destino |
|---|---|---|
| `jquery/jquery.min.js` | cargado en `header.php:46` / `auth.php:46` | **se conserva** (C2) |
| `bootstrap/bootstrap.bundle.min.js` | v4.6.1, cargado en `footer.php:25` | reemplazar por v5.3.8 + Popper (T8/T10) |
| `bootstrap/bootstrap.min.js` | v5.0.2, **sin usar** | candidato a eliminar o unificar en T8 |
| `adminlte/adminlte.min.js` | AdminLTE 3 (requiere jQuery), `footer.php:28` | reemplazar por v4 (T7/T13) — v4 no requiere jQuery |

### `public/js/plugins/` (terceros)

| Requieren jQuery | No requieren jQuery |
|---|---|
| `datatables/*` (`jquery.dataTables`, `dataTables.bootstrap4`, `dataTables.responsive`, `responsive.bootstrap4`, `dataTables.buttons`, `buttons.bootstrap4`, `buttons.html5`, `buttons.colVis`, `buttons.print`) · `select2/select2.min.js` · `validations/jquery.validate.min.js` + `additional-methods.min.js` | `chart/Chart.js` · `sweetalert2/sweetalert2.min.js` · `moment/*` · `utils/jszip.min.js`, `pdfmake.min.js`, `vfs_fonts.js` (requeridos por export de DataTables) |

⚠️ Los integradores con sufijo **`.bootstrap4`** (`dataTables.bootstrap4`, `responsive.bootstrap4`, `buttons.bootstrap4`) están atados a BS4 — revisar equivalentes BS5 en T41.

## Mapa a tareas de la Fase 2/3

- **T19** → `core/ui-components.js` (Select2 + tooltips BS5 + selectores `data-bs-toggle`)
- **T20** → `core/common-utils.js` (delegación conservada; eliminar `ajaxRequest` muerto)
- **T21** → `core/common-validate.js` (+ configuración `.validate` de los módulos)
- **T22** → `core/sweetalert-utils.js` (ya nativo; revisión `.html()`/`escapeHtml`)
- **T23** → `modules/profile/theme-toggle.js` (ya nativo)
- **T24-T36** → módulos según tabla del «Resumen»
- **T41** → `AssetRegistry` + integradores DataTables `.bootstrap4`
- **T49** → módulos `auth/*` (Hallazgo 7)
- **Sin tarea** → `common-datatable.js` (Hallazgo 8)
