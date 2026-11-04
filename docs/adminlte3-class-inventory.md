# Inventario de clases de AdminLTE 3 (T1 — Spec 001)

> Migración AdminLTE 3.2.0 → 4.10.1 · Alcance: `views/**/*.php` + `public/css/core/` + `public/css/modules/`.
> ★ = la clase también está definida en CSS propio del proyecto (cobertura parcial tras la migración).

## Método

- **Clases de AdminLTE 3:** extraídas del CSS local `public/css/lib/adminlte/adminlte.min.css` (2967 clases). Verificado **idéntico** (md5 tras aplanar espacios) al oficial `admin-lte@3.2.0` (npm) y a `ColorlibHQ/AdminLTE@v3.2.0` (GitHub).
- **Ocurrencias:** atributos `class="..."` en `views/**/*.php` (incluidos los dinámicos dentro de `<?= ... ?>`) y selectores `.clase` en `public/css/core/` y `public/css/modules/`, con comentarios multilínea descartados.
- **¿Sigue en AdminLTE 4?:** presencia en `ColorlibHQ/AdminLTE@v4.10.1/dist/css/adminlte.min.css` (2467 clases).
- **¿Cubierta por Bootstrap 5?:** presencia en `public/css/lib/bootstrap/bootstrap.min.css` (v5.0.2, se conserva en la migración).
- **Excluidos:** `public/css/lib/` y `public/css/plugins/` (CSS de terceros) y las clases que AdminLTE 3 no define (propias del proyecto o de plugins).

## Hallazgos de contexto

1. El CSS local de AdminLTE es una copia _beautificada_ **idéntica** al oficial 3.2.0 (sin personalizaciones del proyecto).
2. El bundle de AdminLTE 3.2.0 **empaqueta** en un solo archivo: Bootstrap **4.6.1** CSS completo + estilos de **Select2**, **iCheck**, **SweetAlert2** y un bloque enorme de **`.dark-mode`**.
3. Hay **dos Bootstraps en CSS a la vez**: `header.php:24` carga `bootstrap.min.css` (v5.0.2) y `header.php:30` carga el bundle de AdminLTE (BS4.6.1 embebido, posterior en la cascada). El JS es `bootstrap.bundle.min.js` **v4.6.1** (`footer.php:25`); existe `bootstrap.min.js` v5.0.2 sin usar.
4. El dark-mode actual depende de **dos fuentes**: el bloque `.dark-mode` del CSS de AdminLTE (se pierde con v4) + `public/css/core/dark-mode.css` (se conserva). Tras la migración solo quedará la segunda.
5. El login usa **iCheck** (`auth.php:25` → `icheck-bootstrap.min.css`, tema BS4) además del iCheck embebido en AdminLTE; el checkbox «Remember me» depende de ambos.
6. **Flechas de tooltip:** el JS BS4 actual genera markup `.arrow` (estilizado por el BS4 embebido). **BS5 usa `.tooltip-arrow`/`.popover-arrow`** → los selectores `.arrow` de `ui-components.css:141` y `dark-mode.css` quedarán huérfanos.

## Resumen

| Grupo                         | Clases | Significado                                                           |
| ----------------------------- | ------ | --------------------------------------------------------------------- |
| **A** — migración obligatoria | 59     | ausentes en AdminLTE 4 **y** en Bootstrap 5                           |
| **B** — revisar cascada       | 1      | ausentes en v4 pero cubiertas por BS5.0.2                             |
| **C** — refresco              | 201    | presentes en AdminLTE 4 (solo verificar)                              |
| **Terceros** (en el bundle)   | 30     | no son de AdminLTE; sus CSS propios independientes siguen disponibles |

Total: **291** clases de AdminLTE 3 en uso, de 425 clases distintas halladas en `views/` + CSS propio (las restantes no pertenecen a AdminLTE 3).

---

## Grupo A — sin cobertura en v4 ni BS5 (59) · migración obligatoria

### A1. Estructura y utilidades de AdminLTE 3 (26)

- **`btn-default`** — 24 usos — `views/audit-log/_modal-detail.php`: 73 · `views/audit-log/index.php`: 135 · `views/auth/accept_invitation.php`: 21, 35 · `views/auth/login.php`: 28 · `views/auth/reset_password.php`: 18, 32 · `views/layouts/header.php`: 110, 114 · `views/permissions/_modal_permission.php`: 28 · `views/permissions/detail.php`: 66, 164 · `views/roles/_modal_role.php`: 28 · `views/roles/detail.php`: 69 · `views/users/create.php`: 240, 257, 362 · `views/users/profile.php`: 170, 185, 200 · `views/users/show.php`: 118 · `views/users/update.php`: 235, 252, 401
    - _Botón neutro BS3/AL3; BS5 no lo define._
- **`btn-flat`** — 2 usos — `views/layouts/header.php`: 110, 114
    - _Variante de botón AdminLTE 3._
- **`content`** ★ — 17 usos — `public/css/modules/dashboard/dashboard.css`: 96, 100, 101, 102, 103, 174 · `views/audit-log/index.php`: 19 · `views/dashboard/index.php`: 19 · `views/permissions/detail.php`: 20 · `views/permissions/index.php`: 19 · `views/roles/detail.php`: 20 · `views/roles/index.php`: 19 · `views/users/create.php`: 20 · `views/users/index.php`: 19 · `views/users/profile.php`: 19 · `views/users/show.php`: 20 · `views/users/update.php`: 20
    - _Wrapper de contenido; v4: `app-content`._
- **`content-header`** — 11 usos — `views/audit-log/index.php`: 2 · `views/dashboard/index.php`: 2 · `views/permissions/detail.php`: 2 · `views/permissions/index.php`: 2 · `views/roles/detail.php`: 2 · `views/roles/index.php`: 2 · `views/users/create.php`: 2 · `views/users/index.php`: 2 · `views/users/profile.php`: 2 · `views/users/show.php`: 2 · `views/users/update.php`: 2
    - _v4: `app-content-header`._
- **`content-wrapper`** — 1 uso — `views/layouts/header.php`: 128
    - _v4: `app-content` (+ `app-content-header`)._
- **`dark-mode`** ★ — 142 usos — `public/css/core/dark-mode.css`: 22, 39, 43, 48, 49, 53, 57, 61, 62, 63, 68, 72, 73, 78, 83, 88, 93, 98, 99, 100, 101, 102, 105, 109, 110, 114, 118, 122, 126, 131, 132, 133, 134, 135, 137, 141, 145, 153, 154, 155, 167, 168, 169, 176, 177, 178, 179, 183, 184, 190, 191, 197, 202, 206, 210, 214, 220, 221, 226, 227, 233, 237, 241, 246, 252, 257, 262, 266, 273, 274, 280, 281, 282, 283, 284, 288, 294, 302, 309, 310, 319, 327, 328, 337, 342, 343, 344, 345, 346, 347, 348, 349, 350, 354, 355, 359, 363, 367, 371, 376, 380, 384, 389, 395, 401, 407, 415, 419, 424, 429, 430 · `public/css/modules/dashboard/dashboard.css`: 46, 143, 163 · `public/css/modules/errors/errors-dark.css`: 6, 11, 15, 19, 23, 27, 28 · `public/css/modules/login/login-dark.css`: 9, 16, 21, 26, 31, 35, 39, 44, 50, 54, 61, 62, 68, 74, 75, 76, 95, 100, 105, 129, 133
    - _El CSS de AdminLTE 3.2 incluye un bloque masivo `.dark-mode` (desaparece en v4): tras la migración `public/css/core/dark-mode.css` debe cubrirlo solo (T17/T37)._
- **`elevation-0`** — 1 uso — `views/layouts/sidebar.php`: 29
    - _Sombras AdminLTE 3; v4 no define `elevation-*`._
- **`elevation-1`** — 6 usos — `views/permissions/index.php`: 25, 34, 43 · `views/roles/index.php`: 25, 34, 43
    - _Sombras AdminLTE 3; v4 no define `elevation-*`._
- **`elevation-2`** — 2 usos — `views/layouts/header.php`: 101 · `views/layouts/sidebar.php`: 25
    - _Sombras AdminLTE 3; v4 no define `elevation-*`._
- **`error-content`** ★ — 2 usos — `public/css/modules/errors/errors.css`: 22 · `views/layouts/error.php`: 32
    - _Layout de errores standalone._
- **`error-page`** ★ — 7 usos — `public/css/modules/errors/errors-dark.css`: 6, 11, 15 · `public/css/modules/errors/errors.css`: 13, 35, 39 · `views/layouts/error.php`: 31
    - _Clase del `<body>` de `views/layouts/error.php:31`._
- **`icon`** — 9 usos — `views/dashboard/index.php`: 30, 47, 64, 82, 114, 128 · `views/users/show.php`: 275, 286, 306
    - _Bloque `.icon` de `small-box` (dashboard); confirmar estructura v4 en Fase 2._
- **`img-circle`** — 10 usos — `views/layouts/header.php`: 74, 101 · `views/layouts/sidebar.php`: 29 · `views/users/create.php`: 486 · `views/users/profile.php`: 29, 101 · `views/users/show.php`: 30, 34 · `views/users/update.php`: 528, 530
    - _BS3/AL3; BS5: `rounded-circle`._
- **`list-group-unbordered`** — 4 usos — `views/permissions/detail.php`: 40 · `views/roles/detail.php`: 41 · `views/users/profile.php`: 38 · `views/users/show.php`: 54
    - _Utilidad AdminLTE 3._
- **`main-footer`** ★ — 4 usos — `public/css/core/ui-components.css`: 291, 299, 305 · `views/layouts/footer.php`: 5
    - _v4: `app-footer`._
- **`main-header`** ★ — 3 usos — `public/css/core/dark-mode.css`: 88, 93 · `views/layouts/header.php`: 64
    - _v4: `app-header`._
- **`main-sidebar`** ★ — 2 usos — `public/css/core/dark-mode.css`: 39 · `views/layouts/sidebar.php`: 25
    - _v4: `app-sidebar`._
- **`nav-sidebar`** ★ — 6 usos — `public/css/core/dark-mode.css`: 57, 61, 62, 63, 78 · `views/layouts/sidebar.php`: 35
    - _Menú lateral v3; v4: `sidebar-menu`._
- **`navbar-white`** — 1 uso — `views/layouts/header.php`: 64
    - _Variante de navbar v3 (no existe en v4)._
- **`profile-user-img`** — 3 usos — `views/users/profile.php`: 29 · `views/users/show.php`: 30, 34
    - _Vista de perfil de usuario._
- **`profile-username`** — 2 usos — `views/users/profile.php`: 33 · `views/users/show.php`: 40
    - _Vista de perfil de usuario._
- **`pushmenu`** ★ — 1 uso — `public/css/core/dark-mode.css`: 49
    - _Comportamiento JS de AdminLTE 3 (`data-widget="pushmenu"`); v4 renombra la mecánica del sidebar._
- **`right`** — 1 uso — `views/layouts/sidebar.php`: 52
    - _Icono de ángulo del treeview (`sidebar.php:52`); v4 usa otra estructura de menú._
- **`sidebar`** ★ — 2 usos — `public/css/core/dark-mode.css`: 53 · `views/layouts/sidebar.php`: 33
- **`sidebar-light-primary`** ★ — 15 usos — `public/css/core/dark-mode.css`: 39, 43, 48, 49, 53, 57, 61, 62, 63, 68, 72, 73, 78, 83 · `views/layouts/sidebar.php`: 25
    - _Skin del sidebar en `<aside>` (`sidebar.php:25`). AdminLTE 4 **no** define `sidebar-light-*`/`sidebar-dark-*` (verificar sistema de tema v4 en Fase 2)._
- **`wrapper`** — 1 uso — `views/layouts/header.php`: 61
    - _Raíz del layout (`header.php:61`); v4 define `app-wrapper`._

### A2. Clases Bootstrap 4 empaquetadas en `adminlte.min.css`, eliminadas en BS5 (32)

_Clase Bootstrap 4 eliminada en BS5 → reemplazar por su equivalente BS5._

- **`arrow`** ★ — 18 usos — `public/css/core/dark-mode.css`: 342, 343, 344, 345, 346, 347, 348, 349, 350 · `public/css/core/ui-components.css`: 136, 137, 138, 139, 140, 141, 142, 143, 144
    - _Flechas de tooltip BS4 (markup generado por el JS BS4 actual). **BS5 usa `.tooltip-arrow`/`.popover-arrow`** → los selectores `.arrow` de `ui-components.css` y `dark-mode.css` quedarán huérfanos (T19/T37)._
- **`badge-danger`** ★ — 10 usos — `public/css/core/dark-mode.css`: 133 · `views/dashboard/index.php`: 240 · `views/permissions/detail.php`: 47 · `views/permissions/index.php`: 95 · `views/roles/detail.php`: 48 · `views/roles/index.php`: 95 · `views/users/index.php`: 77 · `views/users/profile.php`: 55 · `views/users/show.php`: 81 · `views/users/update.php`: 537
- **`badge-info`** ★ — 3 usos — `public/css/core/dark-mode.css`: 135 · `views/permissions/detail.php`: 54 · `views/roles/detail.php`: 63
- **`badge-pill`** — 24 usos — `views/audit-log/index.php`: 223 · `views/dashboard/index.php`: 236, 238, 240 · `views/permissions/detail.php`: 45, 47, 54 · `views/permissions/index.php`: 90, 95 · `views/roles/detail.php`: 46, 48, 56, 63 · `views/roles/index.php`: 90, 95 · `views/users/index.php`: 73, 75, 77 · `views/users/profile.php`: 53, 55 · `views/users/show.php`: 77, 79, 81, 89
- **`badge-primary`** ★ — 3 usos — `public/css/core/dark-mode.css`: 131 · `views/permissions/index.php`: 90 · `views/roles/index.php`: 90
- **`badge-secondary`** — 3 usos — `views/audit-log/index.php`: 155 · `views/permissions/index.php`: 90 · `views/roles/index.php`: 90
- **`badge-success`** ★ — 11 usos — `public/css/core/dark-mode.css`: 132 · `views/dashboard/index.php`: 236 · `views/permissions/detail.php`: 45 · `views/permissions/index.php`: 95 · `views/roles/detail.php`: 46 · `views/roles/index.php`: 95 · `views/users/create.php`: 489 · `views/users/index.php`: 75 · `views/users/profile.php`: 53 · `views/users/show.php`: 79 · `views/users/update.php`: 535
- **`badge-warning`** ★ — 6 usos — `public/css/core/dark-mode.css`: 134 · `views/dashboard/index.php`: 238 · `views/roles/detail.php`: 56 · `views/users/index.php`: 73 · `views/users/show.php`: 77, 89
- **`btn-block`** — 16 usos — `views/auth/accept_invitation.php`: 45 · `views/auth/forgot_password.php`: 23 · `views/auth/login.php`: 47 · `views/auth/reset_password.php`: 42 · `views/users/create.php`: 357, 362, 390, 411, 432, 453 · `views/users/update.php`: 396, 401, 429, 450, 472, 493
- **`close`** — 4 usos — `views/audit-log/_modal-detail.php`: 14 · `views/permissions/_modal_permission.php`: 7 · `views/permissions/detail.php`: 146 · `views/roles/_modal_role.php`: 7
- **`custom-checkbox`** — 3 usos — `views/roles/detail.php`: 114 · `views/users/create.php`: 338 · `views/users/update.php`: 343
- **`custom-control`** — 4 usos — `views/roles/detail.php`: 114 · `views/users/create.php`: 190, 338 · `views/users/update.php`: 343
- **`custom-control-input`** — 4 usos — `views/roles/detail.php`: 115 · `views/users/create.php`: 191, 339 · `views/users/update.php`: 344
- **`custom-control-label`** — 4 usos — `views/roles/detail.php`: 120 · `views/users/create.php`: 193, 343 · `views/users/update.php`: 349
- **`custom-file`** — 3 usos — `views/users/create.php`: 286 · `views/users/profile.php`: 105 · `views/users/update.php`: 280
- **`custom-file-input`** ★ — 4 usos — `public/css/core/dark-mode.css`: 401 · `views/users/create.php`: 287 · `views/users/profile.php`: 106 · `views/users/update.php`: 281
- **`custom-file-label`** ★ — 6 usos — `public/css/core/dark-mode.css`: 389, 395, 401 · `views/users/create.php`: 289 · `views/users/profile.php`: 107 · `views/users/update.php`: 283
- **`custom-switch`** — 1 uso — `views/users/create.php`: 190
- **`dropdown-menu-right`** — 1 uso — `views/layouts/header.php`: 98
- **`float-right`** — 16 usos — `views/layouts/footer.php`: 7 · `views/layouts/header.php`: 112 · `views/permissions/detail.php`: 43, 53 · `views/roles/detail.php`: 44, 55, 62 · `views/users/profile.php`: 41, 45, 51 · `views/users/show.php`: 57, 61, 66, 70, 75, 88
- **`float-sm-right`** — 11 usos — `views/audit-log/index.php`: 9 · `views/dashboard/index.php`: 9 · `views/permissions/detail.php`: 9 · `views/permissions/index.php`: 9 · `views/roles/detail.php`: 9 · `views/roles/index.php`: 9 · `views/users/create.php`: 9 · `views/users/index.php`: 9 · `views/users/profile.php`: 9 · `views/users/show.php`: 9 · `views/users/update.php`: 9
- **`font-weight-bold`** — 2 usos — `views/audit-log/_modal-detail.php`: 56, 64
- **`font-weight-light`** — 1 uso — `views/layouts/sidebar.php`: 30
- **`form-group`** — 60 usos — `views/audit-log/index.php`: 41, 57, 73, 89, 109 · `views/auth/accept_invitation.php`: 15, 29 · `views/auth/forgot_password.php`: 11 · `views/auth/login.php`: 11, 22 · `views/auth/reset_password.php`: 12, 26 · `views/permissions/_modal_permission.php`: 17, 22 · `views/permissions/detail.php`: 151 · `views/roles/_modal_role.php`: 17, 22 · `views/users/create.php`: 42, 56, 70, 86, 100, 130, 146, 160, 204, 219, 234, 251, 283, 331 · `views/users/profile.php`: 121, 132, 161, 176, 191 · `views/users/show.php`: 155, 166, 180, 191, 205, 216, 230, 241, 260 · `views/users/update.php`: 43, 57, 71, 87, 101, 132, 148, 163, 194, 229, 246, 277, 334, 376, 384
- **`ml-2`** — 1 uso — `views/audit-log/index.php`: 155
- **`ml-auto`** — 1 uso — `views/layouts/header.php`: 82
- **`mr-1`** — 69 usos — `views/audit-log/_modal-detail.php`: 10, 74 · `views/audit-log/index.php`: 27, 133, 136, 154, 162 · `views/auth/accept_invitation.php`: 7 · `views/dashboard/index.php`: 142, 163, 182, 205, 210 · `views/permissions/detail.php`: 42, 52, 78, 93, 145 · `views/roles/detail.php`: 43, 54, 61, 81, 87, 102, 105, 136 · `views/users/create.php`: 194, 321, 324, 378, 391, 412, 433, 454, 477 · `views/users/profile.php`: 40, 44, 50, 72, 77, 145, 157, 208 · `views/users/show.php`: 56, 60, 64, 74, 87, 90, 106, 115, 134, 139, 144, 261, 269 · `views/users/update.php`: 224, 324, 327, 377, 385, 397, 402, 417, 430, 451, 473, 494, 518
- **`mr-2`** — 20 usos — `views/audit-log/index.php`: 160 · `views/auth/accept_invitation.php`: 46 · `views/auth/forgot_password.php`: 24 · `views/auth/login.php`: 48 · `views/auth/reset_password.php`: 43 · `views/permissions/detail.php`: 81 · `views/users/create.php`: 31, 119, 179, 273, 309 · `views/users/profile.php`: 94, 118 · `views/users/update.php`: 32, 121, 183, 215, 267, 312, 366
- **`mr-sm-2`** — 1 uso — `views/users/update.php`: 395
- **`text-left`** — 8 usos — `views/users/create.php`: 390, 411, 432, 453 · `views/users/update.php`: 429, 450, 472, 493
- **`text-monospace`** — 2 usos — `views/audit-log/_modal-detail.php`: 44 · `views/audit-log/index.php`: 230
- **`thead-light`** ★ — 3 usos — `public/css/core/dark-mode.css`: 168, 169 · `views/dashboard/index.php`: 221

### A3. Propios / a revisar (1)

- **`focus`** ★ — 1 uso — `public/css/modules/login/login.css`: 278
    - _Selector propio `.btn-primary.focus` (`login.css:278`) sin HTML que lo active — candidato a limpiar, no a migrar._

---

## Grupo B — ausente en v4, cubierta por Bootstrap 5 (1)

_No requiere cambio de nombre; revisar que la cascada no la pise tras quitar el BS4 embebido._

- **`navbar-light`** — 1 uso — `views/layouts/header.php`: 64

---

## Grupo C — presentes en AdminLTE 4 (201) · solo refresco

No necesitan cambio de nombre; verificar visualmente al actualizar el CSS (T18/T45).

- **`accordion`** — 2 — `views/users/create.php`: 386 · `views/users/update.php`: 425
- **`active`** ★ — 34 — `public/css/core/dark-mode.css`: 78, 141, 309, 310, 354, 355, 359, 363, 367, 371, 376, 380 · `public/css/core/ui-components.css`: 321, 322, 323, 324, 325, 326 · `views/audit-log/index.php`: 11 · `views/dashboard/index.php`: 11 · `views/layouts/sidebar.php`: 48 · `views/permissions/detail.php`: 12 · `views/permissions/index.php`: 11 · `views/roles/detail.php`: 12 · `views/roles/index.php`: 11 · `views/users/create.php`: 12 · `views/users/index.php`: 11 · `views/users/profile.php`: 11, 71, 87 · `views/users/show.php`: 12, 133, 152 · `views/users/update.php`: 12
- **`alert`** — 10 — `views/layouts/messages.php`: 19 · `views/permissions/detail.php`: 92 · `views/roles/detail.php`: 86 · `views/users/create.php`: 491 · `views/users/profile.php`: 156 · `views/users/show.php`: 274, 285, 305 · `views/users/update.php`: 223, 540
- **`alert-info`** — 3 — `views/users/profile.php`: 156 · `views/users/show.php`: 274 · `views/users/update.php`: 223
- **`alert-light`** — 2 — `views/users/create.php`: 491 · `views/users/update.php`: 540
- **`alert-success`** — 1 — `views/users/show.php`: 285
- **`alert-warning`** — 3 — `views/permissions/detail.php`: 92 · `views/roles/detail.php`: 86 · `views/users/show.php`: 305
- **`align-items-center`** — 3 — `views/layouts/header.php`: 73 · `views/users/index.php`: 25 · `views/users/profile.php`: 97
- **`align-items-sm-center`** — 2 — `views/permissions/index.php`: 56 · `views/roles/index.php`: 56
- **`align-items-start`** — 2 — `views/permissions/index.php`: 56 · `views/roles/index.php`: 56
- **`badge`** — 28 — `views/audit-log/index.php`: 155, 223 · `views/dashboard/index.php`: 236, 238, 240 · `views/permissions/detail.php`: 45, 47, 54 · `views/permissions/index.php`: 90, 95 · `views/roles/detail.php`: 46, 48, 56, 63 · `views/roles/index.php`: 90, 95 · `views/users/create.php`: 489 · `views/users/index.php`: 73, 75, 77 · `views/users/profile.php`: 53, 55 · `views/users/show.php`: 77, 79, 81, 89 · `views/users/update.php`: 535, 537
- **`bg-danger`** ★ — 2 — `public/css/core/dark-mode.css`: 102, 284
- **`bg-info`** ★ — 7 — `public/css/core/dark-mode.css`: 101, 282 · `views/audit-log/_modal-detail.php`: 7 · `views/dashboard/index.php`: 59 · `views/permissions/index.php`: 25 · `views/roles/index.php`: 25 · `views/users/show.php`: 296
- **`bg-light`** ★ — 5 — `public/css/core/dark-mode.css`: 167 · `views/audit-log/_modal-detail.php`: 57 · `views/users/show.php`: 295 · `views/users/update.php`: 378, 386
- **`bg-primary`** ★ — 5 — `public/css/core/dark-mode.css`: 98, 280 · `public/css/core/ui-components.css`: 147 · `views/dashboard/index.php`: 25 · `views/roles/_modal_role.php`: 5
- **`bg-secondary`** — 1 — `views/dashboard/index.php`: 123
- **`bg-success`** ★ — 6 — `public/css/core/dark-mode.css`: 99, 281 · `views/dashboard/index.php`: 77 · `views/permissions/detail.php`: 144 · `views/permissions/index.php`: 34 · `views/roles/index.php`: 34
- **`bg-warning`** ★ — 7 — `public/css/core/dark-mode.css`: 100, 283 · `views/dashboard/index.php`: 42, 109 · `views/permissions/_modal_permission.php`: 5 · `views/permissions/index.php`: 43 · `views/roles/index.php`: 43
- **`border-0`** — 8 — `views/users/create.php`: 387, 408, 429, 450 · `views/users/update.php`: 426, 447, 469, 490
- **`border-bottom-0`** — 2 — `views/users/profile.php`: 68 · `views/users/show.php`: 130
- **`brand-image`** ★ — 3 — `public/css/modules/login/login.css`: 46, 194 · `views/layouts/sidebar.php`: 29
- **`brand-link`** ★ — 5 — `public/css/core/dark-mode.css`: 43, 48, 49, 137 · `views/layouts/sidebar.php`: 27
- **`brand-text`** — 2 — `views/layouts/header.php`: 76 · `views/layouts/sidebar.php`: 30
- **`breadcrumb`** — 11 — `views/audit-log/index.php`: 9 · `views/dashboard/index.php`: 9 · `views/permissions/detail.php`: 9 · `views/permissions/index.php`: 9 · `views/roles/detail.php`: 9 · `views/roles/index.php`: 9 · `views/users/create.php`: 9 · `views/users/index.php`: 9 · `views/users/profile.php`: 9 · `views/users/show.php`: 9 · `views/users/update.php`: 9
- **`breadcrumb-item`** — 27 — `views/audit-log/index.php`: 10, 11 · `views/dashboard/index.php`: 10, 11 · `views/permissions/detail.php`: 10, 11, 12 · `views/permissions/index.php`: 10, 11 · `views/roles/detail.php`: 10, 11, 12 · `views/roles/index.php`: 10, 11 · `views/users/create.php`: 10, 11, 12 · `views/users/index.php`: 10, 11 · `views/users/profile.php`: 10, 11 · `views/users/show.php`: 10, 11, 12 · `views/users/update.php`: 10, 11, 12
- **`btn`** ★ — 105 — `public/css/core/dark-mode.css`: 137 · `public/css/core/ui-components.css`: 262, 263, 269, 270 · `public/css/modules/login/login-dark.css`: 100 · `views/audit-log/_modal-detail.php`: 73 · `views/audit-log/index.php`: 29, 132, 135, 160, 165, 235 · `views/auth/accept_invitation.php`: 21, 35, 45 · `views/auth/forgot_password.php`: 23 · `views/auth/login.php`: 28, 47 · `views/auth/reset_password.php`: 18, 32, 42 · `views/dashboard/index.php`: 96, 209 · `views/layouts/error.php`: 36 · `views/layouts/header.php`: 110, 114 · `views/permissions/_modal_permission.php`: 28, 31 · `views/permissions/detail.php`: 60, 66, 81, 85, 116, 119, 164, 167 · `views/permissions/index.php`: 59, 62, 99, 102, 109 · `views/roles/_modal_role.php`: 28, 31 · `views/roles/detail.php`: 69, 101, 104, 135 · `views/roles/index.php`: 59, 62, 101, 105, 112 · `views/users/create.php`: 33, 121, 181, 240, 257, 275, 311, 320, 323, 357, 362, 380, 390, 411, 432, 453, 479 · `views/users/index.php`: 28, 31, 82, 85, 89, 96 · `views/users/profile.php`: 144, 170, 185, 200, 207 · `views/users/show.php`: 98, 103, 111, 118, 268 · `views/users/update.php`: 34, 123, 185, 217, 235, 252, 269, 314, 323, 326, 368, 396, 401, 419, 429, 450, 472, 493, 520
- **`btn-danger`** ★ — 7 — `public/css/core/dark-mode.css`: 118 · `views/permissions/detail.php`: 119 · `views/permissions/index.php`: 109 · `views/roles/index.php`: 112 · `views/users/index.php`: 96 · `views/users/profile.php`: 207 · `views/users/show.php`: 111
- **`btn-group`** ★ — 12 — `public/css/modules/permissions/permissions.css`: 5, 6 · `public/css/modules/roles/roles.css`: 5 · `public/css/modules/users/users.css`: 34 · `views/audit-log/index.php`: 131 · `views/permissions/detail.php`: 115 · `views/permissions/index.php`: 98 · `views/roles/detail.php`: 100 · `views/roles/index.php`: 100 · `views/users/create.php`: 319 · `views/users/index.php`: 81 · `views/users/update.php`: 322
- **`btn-info`** ★ — 7 — `public/css/core/dark-mode.css`: 126 · `views/audit-log/index.php`: 235 · `views/permissions/detail.php`: 116 · `views/permissions/index.php`: 99 · `views/roles/index.php`: 101 · `views/users/index.php`: 82 · `views/users/show.php`: 268
- **`btn-link`** — 9 — `views/dashboard/index.php`: 96 · `views/users/create.php`: 390, 411, 432, 453 · `views/users/update.php`: 429, 450, 472, 493
- **`btn-outline-primary`** — 3 — `views/roles/detail.php`: 101 · `views/users/create.php`: 320 · `views/users/update.php`: 323
- **`btn-outline-secondary`** — 3 — `views/roles/detail.php`: 104 · `views/users/create.php`: 323 · `views/users/update.php`: 326
- **`btn-primary`** ★ — 20 — `public/css/core/dark-mode.css`: 105, 109, 110 · `public/css/modules/login/login.css`: 104, 113, 120, 277, 278 · `views/audit-log/index.php`: 132 · `views/auth/forgot_password.php`: 23 · `views/auth/login.php`: 47 · `views/auth/reset_password.php`: 42 · `views/dashboard/index.php`: 209 · `views/permissions/index.php`: 59 · `views/roles/_modal_role.php`: 31 · `views/roles/detail.php`: 135 · `views/roles/index.php`: 59 · `views/users/create.php`: 357 · `views/users/index.php`: 28 · `views/users/profile.php`: 144
- **`btn-secondary`** — 2 — `views/users/index.php`: 89 · `views/users/show.php`: 103
- **`btn-sm`** ★ — 30 — `public/css/modules/permissions/permissions.css`: 5, 6 · `public/css/modules/roles/roles.css`: 5 · `public/css/modules/users/users.css`: 34 · `views/audit-log/_modal-detail.php`: 73 · `views/audit-log/index.php`: 160, 235 · `views/dashboard/index.php`: 209 · `views/permissions/detail.php`: 81, 116, 119 · `views/permissions/index.php`: 59, 99, 102, 109 · `views/roles/detail.php`: 101, 104 · `views/roles/index.php`: 59, 101, 105, 112 · `views/users/create.php`: 320, 323 · `views/users/index.php`: 28, 82, 85, 89, 96 · `views/users/update.php`: 323, 326
- **`btn-success`** ★ — 8 — `public/css/core/dark-mode.css`: 114 · `views/audit-log/index.php`: 160 · `views/auth/accept_invitation.php`: 45 · `views/permissions/detail.php`: 81, 167 · `views/permissions/index.php`: 109 · `views/roles/index.php`: 112 · `views/users/index.php`: 96
- **`btn-tool`** — 22 — `views/audit-log/index.php`: 29, 165 · `views/permissions/detail.php`: 85 · `views/permissions/index.php`: 62 · `views/roles/index.php`: 62 · `views/users/create.php`: 33, 121, 181, 275, 311, 380, 479 · `views/users/index.php`: 31 · `views/users/update.php`: 34, 123, 185, 217, 269, 314, 368, 419, 520
- **`btn-warning`** ★ — 8 — `public/css/core/dark-mode.css`: 122 · `views/permissions/_modal_permission.php`: 31 · `views/permissions/detail.php`: 60 · `views/permissions/index.php`: 102 · `views/roles/index.php`: 105 · `views/users/index.php`: 85 · `views/users/show.php`: 98 · `views/users/update.php`: 396
- **`callout`** — 8 — `views/users/create.php`: 397, 418, 439, 460 · `views/users/update.php`: 436, 457, 479, 500
- **`callout-info`** — 5 — `views/users/create.php`: 397, 418, 460 · `views/users/update.php`: 436, 479
- **`callout-success`** — 1 — `views/users/update.php`: 500
- **`callout-warning`** — 2 — `views/users/create.php`: 439 · `views/users/update.php`: 457
- **`card`** ★ — 53 — `public/css/modules/dashboard/dashboard.css`: 132, 137, 143, 176, 187 · `public/css/modules/login/login-dark.css`: 21 · `views/audit-log/index.php`: 25, 151 · `views/auth/accept_invitation.php`: 1 · `views/auth/forgot_password.php`: 1 · `views/auth/login.php`: 1 · `views/auth/reset_password.php`: 1 · `views/dashboard/index.php`: 140, 161, 180, 202 · `views/permissions/detail.php`: 25, 76 · `views/permissions/index.php`: 54 · `views/roles/detail.php`: 26, 79 · `views/roles/index.php`: 54 · `views/users/create.php`: 29, 117, 177, 271, 307, 376, 387, 408, 429, 450, 475 · `views/users/index.php`: 23 · `views/users/profile.php`: 25, 67, 92, 116 · `views/users/show.php`: 26, 129 · `views/users/update.php`: 30, 119, 181, 213, 265, 310, 364, 415, 426, 447, 469, 490, 516
- **`card-body`** — 48 — `views/audit-log/index.php`: 34, 170 · `views/auth/accept_invitation.php`: 5 · `views/auth/forgot_password.php`: 5 · `views/auth/login.php`: 5 · `views/auth/reset_password.php`: 5 · `views/dashboard/index.php`: 144, 165, 184, 215 · `views/permissions/detail.php`: 26, 90 · `views/permissions/index.php`: 68 · `views/roles/detail.php`: 27, 85, 97 · `views/roles/index.php`: 68 · `views/users/create.php`: 38, 126, 186, 280, 316, 385, 396, 417, 438, 459, 484 · `views/users/index.php`: 38 · `views/users/profile.php`: 26, 83, 96, 120 · `views/users/show.php`: 27, 149 · `views/users/update.php`: 39, 128, 190, 222, 274, 319, 373, 424, 435, 456, 478, 499, 525
- **`card-danger`** ★ — 5 — `public/css/core/dark-mode.css`: 371 · `public/css/core/ui-components.css`: 326, 367, 371 · `views/users/update.php`: 213
- **`card-footer`** — 4 — `views/roles/detail.php`: 132 · `views/users/create.php`: 354 · `views/users/profile.php`: 143 · `views/users/update.php`: 393
- **`card-header`** ★ — 48 — `public/css/modules/login/login-dark.css`: 26, 31 · `public/css/modules/login/login.css`: 64, 69, 199 · `views/audit-log/index.php`: 26, 152 · `views/auth/accept_invitation.php`: 2 · `views/auth/forgot_password.php`: 2 · `views/auth/login.php`: 2 · `views/auth/reset_password.php`: 2 · `views/dashboard/index.php`: 141, 162, 181, 203 · `views/permissions/detail.php`: 77 · `views/permissions/index.php`: 55 · `views/roles/detail.php`: 80 · `views/roles/index.php`: 55 · `views/users/create.php`: 30, 118, 178, 272, 308, 377, 388, 409, 430, 451, 476 · `views/users/index.php`: 24 · `views/users/profile.php`: 68, 93, 117 · `views/users/show.php`: 130 · `views/users/update.php`: 31, 120, 182, 214, 266, 311, 365, 416, 427, 448, 470, 491, 517
- **`card-info`** ★ — 14 — `public/css/core/dark-mode.css`: 359 · `public/css/core/ui-components.css`: 322, 331, 335 · `views/dashboard/index.php`: 161 · `views/permissions/detail.php`: 76 · `views/roles/detail.php`: 79 · `views/users/create.php`: 117, 376 · `views/users/profile.php`: 116 · `views/users/show.php`: 26, 129 · `views/users/update.php`: 119, 415
- **`card-outline`** ★ — 40 — `public/css/modules/login/login.css`: 58 · `views/audit-log/index.php`: 25, 151 · `views/auth/accept_invitation.php`: 1 · `views/auth/forgot_password.php`: 1 · `views/auth/login.php`: 1 · `views/auth/reset_password.php`: 1 · `views/dashboard/index.php`: 140, 161, 180, 202 · `views/permissions/detail.php`: 25, 76 · `views/permissions/index.php`: 54 · `views/roles/detail.php`: 26, 79 · `views/roles/index.php`: 54 · `views/users/create.php`: 29, 117, 177, 271, 307, 376, 475 · `views/users/index.php`: 23 · `views/users/profile.php`: 25, 67, 92, 116 · `views/users/show.php`: 26, 129 · `views/users/update.php`: 30, 119, 181, 213, 265, 310, 364, 415, 516
- **`card-outline-tabs`** ★ — 27 — `public/css/core/dark-mode.css`: 354, 355, 359, 363, 367, 371, 376, 380, 384 · `public/css/core/ui-components.css`: 321, 322, 323, 324, 325, 326, 331, 335, 340, 344, 349, 353, 358, 362, 367, 371 · `views/users/profile.php`: 67 · `views/users/show.php`: 129
- **`card-primary`** ★ — 21 — `public/css/core/dark-mode.css`: 355 · `public/css/core/ui-components.css`: 324, 349, 353 · `public/css/modules/login/login.css`: 58 · `views/audit-log/index.php`: 151 · `views/auth/forgot_password.php`: 1 · `views/auth/login.php`: 1 · `views/auth/reset_password.php`: 1 · `views/dashboard/index.php`: 140, 202 · `views/permissions/detail.php`: 25 · `views/permissions/index.php`: 54 · `views/roles/detail.php`: 26 · `views/roles/index.php`: 54 · `views/users/create.php`: 29, 475 · `views/users/index.php`: 23 · `views/users/profile.php`: 25, 67 · `views/users/update.php`: 30
- **`card-secondary`** — 4 — `views/audit-log/index.php`: 25 · `views/users/create.php`: 307 · `views/users/update.php`: 310, 364
- **`card-success`** ★ — 9 — `public/css/core/dark-mode.css`: 363 · `public/css/core/ui-components.css`: 323, 340, 344 · `views/auth/accept_invitation.php`: 1 · `views/dashboard/index.php`: 180 · `views/users/create.php`: 271 · `views/users/profile.php`: 92 · `views/users/update.php`: 265
- **`card-title`** — 29 — `views/audit-log/index.php`: 27, 153 · `views/dashboard/index.php`: 142, 163, 182, 204 · `views/permissions/detail.php`: 78 · `views/permissions/index.php`: 57 · `views/roles/detail.php`: 81 · `views/roles/index.php`: 57 · `views/users/create.php`: 31, 119, 179, 273, 309, 378, 477 · `views/users/index.php`: 26 · `views/users/profile.php`: 94, 118 · `views/users/update.php`: 32, 121, 183, 215, 267, 312, 366, 417, 518
- **`card-tools`** — 23 — `views/audit-log/index.php`: 28, 157 · `views/dashboard/index.php`: 208 · `views/permissions/detail.php`: 79 · `views/permissions/index.php`: 58 · `views/roles/index.php`: 58 · `views/users/create.php`: 32, 120, 180, 274, 310, 379, 478 · `views/users/index.php`: 27 · `views/users/update.php`: 33, 122, 184, 216, 268, 313, 367, 418, 519
- **`card-warning`** ★ — 7 — `public/css/core/dark-mode.css`: 367 · `public/css/core/ui-components.css`: 325, 358, 362 · `views/users/create.php`: 177 · `views/users/update.php`: 181, 516
- **`col-12`** — 32 — `views/audit-log/_modal-detail.php`: 23, 38 · `views/audit-log/index.php`: 24, 72, 88, 108, 130, 150 · `views/auth/accept_invitation.php`: 44 · `views/auth/forgot_password.php`: 22 · `views/auth/login.php`: 37, 46 · `views/auth/reset_password.php`: 41 · `views/dashboard/index.php`: 95, 201 · `views/permissions/index.php`: 23, 32, 41, 53 · `views/roles/detail.php`: 134 · `views/roles/index.php`: 23, 32, 41, 53 · `views/users/create.php`: 356, 361 · `views/users/index.php`: 22 · `views/users/show.php`: 259, 267, 304 · `views/users/update.php`: 395, 400
- **`col-5`** — 7 — `views/audit-log/_modal-detail.php`: 25, 28, 31, 34, 40, 43, 46
- **`col-6`** — 2 — `views/audit-log/index.php`: 40, 56
- **`col-7`** — 7 — `views/audit-log/_modal-detail.php`: 26, 29, 32, 35, 41, 44, 47
- **`col-lg-12`** — 1 — `views/dashboard/index.php`: 179
- **`col-lg-2`** — 3 — `views/audit-log/index.php`: 40, 88, 108
- **`col-lg-3`** — 2 — `views/audit-log/index.php`: 56, 72
- **`col-lg-4`** — 2 — `views/dashboard/index.php`: 108, 122
- **`col-lg-6`** — 6 — `views/dashboard/index.php`: 24, 41, 58, 76, 139, 160
- **`col-md-12`** — 8 — `views/roles/detail.php`: 99 · `views/users/create.php`: 129, 189, 318, 330 · `views/users/update.php`: 131, 321, 333
- **`col-md-4`** — 27 — `views/audit-log/index.php`: 40, 56, 72, 88, 108 · `views/permissions/detail.php`: 24 · `views/permissions/index.php`: 23, 32, 41 · `views/roles/detail.php`: 25, 113 · `views/roles/index.php`: 23, 32, 41 · `views/users/create.php`: 41, 55, 69, 337, 375 · `views/users/profile.php`: 24 · `views/users/show.php`: 24, 294 · `views/users/update.php`: 42, 56, 70, 342, 414
- **`col-md-5`** — 1 — `views/users/profile.php`: 98
- **`col-md-6`** — 37 — `views/audit-log/_modal-detail.php`: 23, 38 · `views/dashboard/index.php`: 24, 41, 58, 76, 108, 122 · `views/users/create.php`: 85, 99, 145, 159, 203, 218, 233, 250, 282, 295 · `views/users/show.php`: 154, 165, 179, 190, 204, 215, 229, 240 · `views/users/update.php`: 86, 100, 147, 162, 193, 228, 245, 276, 289, 375, 383
- **`col-md-7`** — 1 — `views/users/profile.php`: 104
- **`col-md-8`** — 6 — `views/permissions/detail.php`: 75 · `views/roles/detail.php`: 78 · `views/users/create.php`: 24 · `views/users/profile.php`: 66 · `views/users/show.php`: 128 · `views/users/update.php`: 24
- **`col-sm-6`** — 29 — `views/audit-log/index.php`: 5, 8 · `views/dashboard/index.php`: 5, 8 · `views/permissions/detail.php`: 5, 8 · `views/permissions/index.php`: 5, 8, 23, 32, 41 · `views/roles/detail.php`: 5, 8 · `views/roles/index.php`: 5, 8, 23, 32, 41 · `views/users/create.php`: 5, 8 · `views/users/index.php`: 5, 8 · `views/users/profile.php`: 5, 8 · `views/users/show.php`: 5, 8, 294 · `views/users/update.php`: 5, 8
- **`col-sm-auto`** — 5 — `views/roles/detail.php`: 134 · `views/users/create.php`: 356, 361 · `views/users/update.php`: 395, 400
- **`col-xl-3`** — 6 — `views/dashboard/index.php`: 24, 41, 58, 76, 108, 122
- **`col-xl-4`** — 3 — `views/dashboard/index.php`: 139, 160, 179
- **`collapse`** — 8 — `views/users/create.php`: 395, 416, 437, 458 · `views/users/update.php`: 434, 455, 477, 498
- **`collapsed-card`** — 2 — `views/audit-log/index.php`: 25 · `views/users/update.php`: 213
- **`container-fluid`** — 22 — `views/audit-log/index.php`: 3, 20 · `views/dashboard/index.php`: 3, 20 · `views/permissions/detail.php`: 3, 21 · `views/permissions/index.php`: 3, 20 · `views/roles/detail.php`: 3, 21 · `views/roles/index.php`: 3, 20 · `views/users/create.php`: 3, 21 · `views/users/index.php`: 3, 20 · `views/users/profile.php`: 3, 20 · `views/users/show.php`: 3, 21 · `views/users/update.php`: 3, 21
- **`d-block`** — 1 — `views/roles/detail.php`: 123
- **`d-flex`** — 7 — `views/layouts/header.php`: 73 · `views/permissions/detail.php`: 59 · `views/permissions/index.php`: 56 · `views/roles/detail.php`: 68 · `views/roles/index.php`: 56 · `views/users/index.php`: 25 · `views/users/show.php`: 97
- **`d-inline`** — 1 — `views/layouts/header.php`: 112
- **`d-md-table-cell`** — 2 — `views/dashboard/index.php`: 226, 243
- **`d-none`** — 9 — `views/audit-log/_modal-detail.php`: 55, 63 · `views/dashboard/index.php`: 224, 226, 233, 243 · `views/layouts/footer.php`: 7 · `views/users/create.php`: 296 · `views/users/update.php`: 299
- **`d-sm-inline`** — 1 — `views/layouts/footer.php`: 7
- **`d-sm-none`** — 1 — `views/layouts/header.php`: 72
- **`d-sm-table-cell`** — 2 — `views/dashboard/index.php`: 224, 233
- **`disabled`** ★ — 4 — `public/css/core/dark-mode.css`: 220, 221 · `public/css/core/ui-components.css`: 263, 270
- **`dropdown`** — 1 — `views/layouts/header.php`: 94
- **`dropdown-item`** ★ — 1 — `public/css/core/dark-mode.css`: 137
- **`dropdown-menu`** — 1 — `views/layouts/header.php`: 98
- **`dropdown-menu-lg`** — 1 — `views/layouts/header.php`: 98
- **`dropdown-toggle`** — 1 — `views/layouts/header.php`: 95
- **`fade`** — 9 — `views/audit-log/_modal-detail.php`: 1 · `views/permissions/_modal_permission.php`: 2 · `views/permissions/detail.php`: 141 · `views/roles/_modal_role.php`: 2 · `views/users/profile.php`: 87, 155 · `views/users/show.php`: 152, 256, 283
- **`flex-column`** — 3 — `views/layouts/sidebar.php`: 35 · `views/permissions/index.php`: 56 · `views/roles/index.php`: 56
- **`flex-sm-row`** — 2 — `views/permissions/index.php`: 56 · `views/roles/index.php`: 56
- **`flex-wrap`** — 2 — `views/users/index.php`: 25 · `views/users/show.php`: 97
- **`form-control`** ★ — 73 — `public/css/core/dark-mode.css`: 153, 154, 155 · `public/css/core/ui-components.css`: 162, 163 · `public/css/modules/login/login-dark.css`: 44, 50, 54, 74, 75, 76, 85, 86, 87 · `public/css/modules/login/login.css`: 84, 92, 283 · `views/audit-log/index.php`: 43, 59, 75, 100, 120 · `views/auth/accept_invitation.php`: 18, 32 · `views/auth/forgot_password.php`: 14 · `views/auth/login.php`: 14, 25 · `views/auth/reset_password.php`: 15, 29 · `views/permissions/_modal_permission.php`: 19, 24 · `views/permissions/detail.php`: 153 · `views/roles/_modal_role.php`: 19, 24 · `views/users/create.php`: 48, 62, 76, 88, 106, 136, 152, 166, 206, 221, 237, 254 · `views/users/profile.php`: 127, 138, 167, 182, 197 · `views/users/show.php`: 161, 172, 186, 197, 211, 222, 236, 247, 262 · `views/users/update.php`: 49, 63, 77, 89, 107, 138, 154, 169, 196, 232, 249, 378, 386
- **`form-text`** — 7 — `views/permissions/_modal_permission.php`: 20 · `views/roles/_modal_role.php`: 20 · `views/users/create.php`: 245, 292 · `views/users/profile.php`: 109 · `views/users/update.php`: 240, 286
- **`h3`** — 4 — `views/auth/accept_invitation.php`: 3 · `views/auth/forgot_password.php`: 3 · `views/auth/login.php`: 3 · `views/auth/reset_password.php`: 3
- **`hold-transition`** — 2 — `views/layouts/auth.php`: 36 · `views/layouts/header.php`: 60
- **`img-fluid`** — 3 — `views/users/profile.php`: 29 · `views/users/show.php`: 30, 34
- **`img-thumbnail`** ★ — 10 — `public/css/core/dark-mode.css`: 407 · `views/users/create.php`: 298, 486 · `views/users/index.php`: 68 · `views/users/profile.php`: 101 · `views/users/update.php`: 293, 296, 301, 528, 530
- **`info-box`** — 7 — `views/permissions/index.php`: 24, 33, 42 · `views/roles/index.php`: 24, 33, 42 · `views/users/show.php`: 295
- **`info-box-content`** — 7 — `views/permissions/index.php`: 26, 35, 44 · `views/roles/index.php`: 26, 35, 44 · `views/users/show.php`: 297
- **`info-box-icon`** — 7 — `views/permissions/index.php`: 25, 34, 43 · `views/roles/index.php`: 25, 34, 43 · `views/users/show.php`: 296
- **`info-box-number`** — 6 — `views/permissions/index.php`: 28, 37, 46 · `views/roles/index.php`: 28, 37, 46
- **`info-box-text`** — 7 — `views/permissions/index.php`: 27, 36, 45 · `views/roles/index.php`: 27, 36, 45 · `views/users/show.php`: 298
- **`inner`** — 6 — `views/dashboard/index.php`: 26, 43, 60, 78, 110, 124
- **`input-group`** ★ — 43 — `public/css/modules/login/login.css`: 287 · `views/audit-log/index.php`: 91, 111 · `views/auth/accept_invitation.php`: 17, 31 · `views/auth/forgot_password.php`: 13 · `views/auth/login.php`: 13, 24 · `views/auth/reset_password.php`: 14, 28 · `views/users/create.php`: 44, 58, 72, 102, 132, 148, 162, 236, 253, 285 · `views/users/profile.php`: 123, 134, 163, 178, 193 · `views/users/show.php`: 157, 168, 182, 193, 207, 218, 232, 243 · `views/users/update.php`: 45, 59, 73, 103, 134, 150, 165, 231, 248, 279
- **`input-group-append`** ★ — 15 — `public/css/core/ui-components.css`: 162 · `views/auth/accept_invitation.php`: 20, 34 · `views/auth/forgot_password.php`: 15 · `views/auth/login.php`: 16, 27 · `views/auth/reset_password.php`: 17, 31 · `views/users/create.php`: 239, 256 · `views/users/profile.php`: 169, 184, 199 · `views/users/update.php`: 234, 251
- **`input-group-prepend`** ★ — 30 — `public/css/core/ui-components.css`: 163 · `views/audit-log/index.php`: 92, 112 · `views/users/create.php`: 45, 59, 73, 103, 133, 149, 163 · `views/users/profile.php`: 124, 135, 164, 179, 194 · `views/users/show.php`: 158, 169, 183, 194, 208, 219, 233, 244 · `views/users/update.php`: 46, 60, 74, 104, 135, 151, 166
- **`input-group-text`** ★ — 36 — `public/css/core/ui-components.css`: 158, 162, 163 · `public/css/modules/login/login-dark.css`: 61 · `public/css/modules/login/login.css`: 97 · `views/audit-log/index.php`: 93, 113 · `views/auth/forgot_password.php`: 16 · `views/auth/login.php`: 17 · `views/users/create.php`: 46, 60, 74, 104, 134, 150, 164 · `views/users/profile.php`: 125, 136, 165, 180, 195 · `views/users/show.php`: 159, 170, 184, 195, 209, 220, 234, 245 · `views/users/update.php`: 47, 61, 75, 105, 136, 152, 167
- **`invalid-feedback`** ★ — 2 — `public/css/core/ui-components.css`: 173 · `public/css/modules/login/login.css`: 161
- **`is-invalid`** ★ — 6 — `public/css/core/ui-components.css`: 70, 74, 75, 81, 85, 86
- **`is-valid`** ★ — 7 — `public/css/core/ui-components.css`: 92, 96, 97, 103, 107, 108, 190
- **`justify-content-between`** — 8 — `views/permissions/_modal_permission.php`: 27 · `views/permissions/detail.php`: 59, 163 · `views/permissions/index.php`: 56 · `views/roles/_modal_role.php`: 27 · `views/roles/index.php`: 56 · `views/users/index.php`: 25 · `views/users/show.php`: 97
- **`justify-content-center`** — 1 — `views/roles/detail.php`: 68
- **`list-group`** — 4 — `views/permissions/detail.php`: 40 · `views/roles/detail.php`: 41 · `views/users/profile.php`: 38 · `views/users/show.php`: 54
- **`list-group-item`** ★ — 15 — `public/css/core/dark-mode.css`: 415, 419 · `views/permissions/detail.php`: 41, 51 · `views/roles/detail.php`: 42, 53, 60 · `views/users/profile.php`: 39, 43, 49 · `views/users/show.php`: 55, 59, 63, 73, 86
- **`login-box`** ★ — 3 — `public/css/modules/login/login.css`: 33, 184 · `views/layouts/auth.php`: 42
- **`login-box-msg`** ★ — 6 — `public/css/modules/login/login-dark.css`: 39 · `public/css/modules/login/login.css`: 76 · `views/auth/accept_invitation.php`: 6 · `views/auth/forgot_password.php`: 6 · `views/auth/login.php`: 6 · `views/auth/reset_password.php`: 6
- **`login-card-body`** ★ — 6 — `public/css/modules/login/login-dark.css`: 35 · `public/css/modules/login/login.css`: 52 · `views/auth/accept_invitation.php`: 5 · `views/auth/forgot_password.php`: 5 · `views/auth/login.php`: 5 · `views/auth/reset_password.php`: 5
- **`login-logo`** ★ — 1 — `public/css/modules/login/login.css`: 41
- **`login-page`** ★ — 23 — `public/css/modules/login/login-dark.css`: 16, 21, 26, 31, 35, 39, 44, 50, 54, 61, 62, 68, 74, 75, 76, 85, 86, 87, 95, 100 · `public/css/modules/login/login.css`: 24, 170 · `views/layouts/auth.php`: 36
- **`m-0`** — 1 — `views/dashboard/index.php`: 6
- **`m-3`** — 1 — `views/layouts/messages.php`: 19
- **`mb-0`** — 36 — `views/audit-log/_modal-detail.php`: 9, 24, 35, 39, 47, 58, 65 · `views/dashboard/index.php`: 220 · `views/permissions/detail.php`: 32 · `views/roles/detail.php`: 33, 86 · `views/users/create.php`: 387, 389, 398, 408, 410, 419, 429, 431, 440, 450, 452, 461 · `views/users/profile.php`: 132 · `views/users/update.php`: 426, 428, 437, 447, 449, 458, 469, 471, 480, 490, 492, 501
- **`mb-1`** — 11 — `views/audit-log/_modal-detail.php`: 26, 29, 32, 41, 44, 56, 64 · `views/auth/accept_invitation.php`: 52 · `views/auth/forgot_password.php`: 30 · `views/auth/login.php`: 54 · `views/auth/reset_password.php`: 49
- **`mb-2`** — 12 — `views/audit-log/_modal-detail.php`: 23 · `views/audit-log/index.php`: 41, 57, 73, 89, 109 · `views/auth/login.php`: 36 · `views/permissions/index.php`: 57 · `views/roles/detail.php`: 114 · `views/roles/index.php`: 57 · `views/users/create.php`: 356 · `views/users/update.php`: 395
- **`mb-3`** — 21 — `views/audit-log/_modal-detail.php`: 52, 55 · `views/dashboard/index.php`: 94 · `views/permissions/detail.php`: 27, 37, 40 · `views/roles/detail.php`: 28, 38, 41, 98 · `views/users/create.php`: 188, 317 · `views/users/profile.php`: 38, 92, 116 · `views/users/show.php`: 54, 98, 103, 111, 118 · `views/users/update.php`: 320
- **`mb-md-0`** — 1 — `views/audit-log/_modal-detail.php`: 23
- **`mb-sm-0`** — 4 — `views/permissions/index.php`: 57 · `views/roles/index.php`: 57 · `views/users/create.php`: 356 · `views/users/update.php`: 395
- **`menu-open`** ★ — 2 — `public/css/core/dark-mode.css`: 61 · `views/layouts/sidebar.php`: 47
- **`modal`** — 4 — `views/audit-log/_modal-detail.php`: 1 · `views/permissions/_modal_permission.php`: 2 · `views/permissions/detail.php`: 141 · `views/roles/_modal_role.php`: 2
- **`modal-body`** — 4 — `views/audit-log/_modal-detail.php`: 19 · `views/permissions/_modal_permission.php`: 12 · `views/permissions/detail.php`: 150 · `views/roles/_modal_role.php`: 12
- **`modal-content`** — 4 — `views/audit-log/_modal-detail.php`: 4 · `views/permissions/_modal_permission.php`: 4 · `views/permissions/detail.php`: 143 · `views/roles/_modal_role.php`: 4
- **`modal-dialog`** — 4 — `views/audit-log/_modal-detail.php`: 3 · `views/permissions/_modal_permission.php`: 3 · `views/permissions/detail.php`: 142 · `views/roles/_modal_role.php`: 3
- **`modal-dialog-centered`** — 4 — `views/audit-log/_modal-detail.php`: 3 · `views/permissions/_modal_permission.php`: 3 · `views/permissions/detail.php`: 142 · `views/roles/_modal_role.php`: 3
- **`modal-dialog-scrollable`** — 1 — `views/audit-log/_modal-detail.php`: 3
- **`modal-footer`** — 4 — `views/audit-log/_modal-detail.php`: 72 · `views/permissions/_modal_permission.php`: 27 · `views/permissions/detail.php`: 163 · `views/roles/_modal_role.php`: 27
- **`modal-header`** ★ — 9 — `public/css/core/dark-mode.css`: 280, 281, 282, 283, 284 · `views/audit-log/_modal-detail.php`: 7 · `views/permissions/_modal_permission.php`: 5 · `views/permissions/detail.php`: 144 · `views/roles/_modal_role.php`: 5
- **`modal-lg`** — 1 — `views/audit-log/_modal-detail.php`: 3
- **`modal-title`** — 4 — `views/audit-log/_modal-detail.php`: 9 · `views/permissions/_modal_permission.php`: 6 · `views/permissions/detail.php`: 145 · `views/roles/_modal_role.php`: 6
- **`mt-2`** — 5 — `views/audit-log/_modal-detail.php`: 52 · `views/permissions/detail.php`: 32 · `views/roles/detail.php`: 33 · `views/users/profile.php`: 33 · `views/users/update.php`: 299
- **`mt-3`** — 9 — `views/auth/accept_invitation.php`: 52 · `views/auth/forgot_password.php`: 30 · `views/auth/login.php`: 54, 60 · `views/auth/reset_password.php`: 49 · `views/users/create.php`: 487, 491 · `views/users/update.php`: 532, 540
- **`mt-4`** — 1 — `views/layouts/sidebar.php`: 34
- **`nav`** — 4 — `views/layouts/sidebar.php`: 35, 55 · `views/users/profile.php`: 69 · `views/users/show.php`: 131
- **`nav-header`** ★ — 1 — `public/css/core/dark-mode.css`: 83
- **`nav-icon`** — 6 — `views/layouts/sidebar.php`: 40, 49, 59, 67, 75, 88
- **`nav-item`** ★ — 24 — `public/css/core/dark-mode.css`: 57, 61, 62, 63, 68, 72, 73, 78 · `views/layouts/header.php`: 67, 72, 83, 89, 94 · `views/layouts/sidebar.php`: 38, 47, 57, 65, 73, 86 · `views/users/profile.php`: 70, 75 · `views/users/show.php`: 132, 137, 142
- **`nav-link`** ★ — 50 — `public/css/core/dark-mode.css`: 57, 61, 62, 63, 68, 72, 73, 78, 93, 137, 354, 355, 359, 363, 367, 371, 376, 380 · `public/css/core/ui-components.css`: 321, 322, 323, 324, 325, 326, 331, 335, 340, 344, 349, 353, 358, 362, 367, 371 · `views/layouts/header.php`: 68, 73, 84, 90, 95 · `views/layouts/sidebar.php`: 39, 48, 58, 66, 74, 87 · `views/users/profile.php`: 71, 76 · `views/users/show.php`: 133, 138, 143
- **`nav-pills`** — 1 — `views/layouts/sidebar.php`: 35
- **`nav-tabs`** ★ — 11 — `public/css/core/dark-mode.css`: 354, 355, 359, 363, 367, 371, 376, 380, 384 · `views/users/profile.php`: 69 · `views/users/show.php`: 131
- **`nav-treeview`** ★ — 4 — `public/css/core/dark-mode.css`: 68, 72, 73 · `views/layouts/sidebar.php`: 55
- **`navbar`** ★ — 3 — `public/css/core/dark-mode.css`: 88, 93 · `views/layouts/header.php`: 64
- **`navbar-expand`** — 1 — `views/layouts/header.php`: 64
- **`navbar-nav`** — 2 — `views/layouts/header.php`: 66, 82
- **`p-0`** — 6 — `views/dashboard/index.php`: 96, 215 · `views/users/create.php`: 385 · `views/users/profile.php`: 68 · `views/users/show.php`: 130 · `views/users/update.php`: 424
- **`p-2`** — 22 — `views/audit-log/_modal-detail.php`: 57 · `views/dashboard/index.php`: 236, 238, 240 · `views/permissions/detail.php`: 45, 47, 54 · `views/permissions/index.php`: 90, 95 · `views/roles/detail.php`: 46, 48, 56, 63 · `views/roles/index.php`: 90, 95 · `views/users/index.php`: 73, 75, 77 · `views/users/show.php`: 77, 79, 81, 89
- **`page-item`** ★ — 1 — `public/css/core/dark-mode.css`: 141
- **`page-link`** ★ — 2 — `public/css/core/dark-mode.css`: 141, 145
- **`pt-5`** — 3 — `views/dashboard/index.php`: 151, 170, 189
- **`px-2`** — 1 — `views/audit-log/index.php`: 223
- **`py-2`** — 2 — `views/audit-log/_modal-detail.php`: 7, 72
- **`py-3`** — 1 — `views/audit-log/_modal-detail.php`: 19
- **`py-4`** — 1 — `views/dashboard/index.php`: 217
- **`rounded`** — 1 — `views/audit-log/_modal-detail.php`: 57
- **`row`** ★ — 80 — `public/css/modules/dashboard/dashboard.css`: 96, 100, 101, 102, 103, 174 · `views/audit-log/_modal-detail.php`: 22, 24, 39 · `views/audit-log/index.php`: 4, 23, 38, 129, 149 · `views/auth/accept_invitation.php`: 43 · `views/auth/forgot_password.php`: 21 · `views/auth/login.php`: 36, 45 · `views/auth/reset_password.php`: 40 · `views/dashboard/index.php`: 4, 23, 94, 106, 137, 200 · `views/permissions/detail.php`: 4, 22 · `views/permissions/index.php`: 4, 22, 52 · `views/roles/detail.php`: 4, 22, 98, 111, 133 · `views/roles/index.php`: 4, 22, 52 · `views/users/create.php`: 4, 22, 39, 83, 127, 143, 188, 201, 231, 281, 317, 329, 333, 355 · `views/users/index.php`: 4, 21 · `views/users/profile.php`: 4, 21, 97 · `views/users/show.php`: 4, 22, 153, 178, 203, 228, 258, 266, 291 · `views/users/update.php`: 4, 22, 40, 84, 129, 145, 191, 226, 275, 320, 332, 337, 374, 394
- **`show`** — 4 — `views/users/create.php`: 395 · `views/users/profile.php`: 87 · `views/users/show.php`: 152 · `views/users/update.php`: 434
- **`sidebar-mini`** — 1 — `views/layouts/header.php`: 60
- **`small`** — 8 — `views/audit-log/_modal-detail.php`: 24, 39, 56, 64 · `views/audit-log/index.php`: 228, 230 · `views/dashboard/index.php`: 96 · `views/users/profile.php`: 41
- **`small-box`** ★ — 11 — `public/css/core/dark-mode.css`: 98, 99, 100, 101, 102 · `views/dashboard/index.php`: 25, 42, 59, 77, 109, 123
- **`small-box-footer`** ★ — 10 — `public/css/modules/dashboard/dashboard.css`: 59 · `views/dashboard/index.php`: 32, 36, 49, 53, 66, 70, 83, 115, 129
- **`sr-only`** — 7 — `views/auth/accept_invitation.php`: 16, 30 · `views/auth/forgot_password.php`: 12 · `views/auth/login.php`: 12, 23 · `views/auth/reset_password.php`: 13, 27
- **`sticky-top`** — 3 — `views/users/create.php`: 475 · `views/users/profile.php`: 25 · `views/users/update.php`: 516
- **`tab-content`** — 2 — `views/users/profile.php`: 84 · `views/users/show.php`: 150
- **`tab-pane`** — 5 — `views/users/profile.php`: 87, 155 · `views/users/show.php`: 152, 256, 283
- **`table`** — 7 — `views/audit-log/_modal-detail.php`: 65 · `views/audit-log/index.php`: 172 · `views/dashboard/index.php`: 220 · `views/permissions/detail.php`: 97 · `views/permissions/index.php`: 69 · `views/roles/index.php`: 69 · `views/users/index.php`: 39
- **`table-bordered`** — 5 — `views/audit-log/index.php`: 172 · `views/permissions/detail.php`: 97 · `views/permissions/index.php`: 69 · `views/roles/index.php`: 69 · `views/users/index.php`: 39
- **`table-borderless`** — 1 — `views/audit-log/_modal-detail.php`: 65
- **`table-hover`** — 6 — `views/audit-log/index.php`: 172 · `views/dashboard/index.php`: 220 · `views/permissions/detail.php`: 97 · `views/permissions/index.php`: 69 · `views/roles/index.php`: 69 · `views/users/index.php`: 39
- **`table-responsive`** — 1 — `views/dashboard/index.php`: 219
- **`table-sm`** — 6 — `views/audit-log/_modal-detail.php`: 65 · `views/audit-log/index.php`: 172 · `views/permissions/detail.php`: 97 · `views/permissions/index.php`: 69 · `views/roles/index.php`: 69 · `views/users/index.php`: 39
- **`table-striped`** — 5 — `views/audit-log/index.php`: 172 · `views/permissions/detail.php`: 97 · `views/permissions/index.php`: 69 · `views/roles/index.php`: 69 · `views/users/index.php`: 39
- **`text-break`** — 1 — `views/audit-log/_modal-detail.php`: 47
- **`text-center`** — 69 — `views/audit-log/index.php`: 176, 177, 178, 179, 180, 181, 182, 201, 202, 212, 227, 230, 233 · `views/auth/accept_invitation.php`: 2, 52 · `views/auth/forgot_password.php`: 2, 30 · `views/auth/login.php`: 2, 54, 60 · `views/auth/reset_password.php`: 2, 49 · `views/dashboard/index.php`: 151, 170, 189, 217 · `views/layouts/messages.php`: 19 · `views/permissions/detail.php`: 27, 37, 114 · `views/permissions/index.php`: 72, 73, 74, 75, 76, 77, 86, 89, 94, 97 · `views/roles/detail.php`: 28, 38 · `views/roles/index.php`: 72, 73, 74, 75, 76, 77, 86, 89, 94, 99 · `views/users/create.php`: 295, 484 · `views/users/index.php`: 50, 62, 67, 71, 80 · `views/users/profile.php`: 27, 33, 36, 98 · `views/users/show.php`: 28, 40, 44, 267 · `views/users/update.php`: 289, 525
- **`text-danger`** — 22 — `views/permissions/_modal_permission.php`: 18 · `views/permissions/detail.php`: 152 · `views/roles/_modal_role.php`: 18 · `views/users/create.php`: 43, 57, 87, 101, 161, 205, 235, 252, 399 · `views/users/profile.php`: 162, 177, 192 · `views/users/update.php`: 44, 58, 88, 102, 164, 195, 438
- **`text-decoration-none`** — 1 — `views/layouts/footer.php`: 16
- **`text-muted`** — 35 — `views/audit-log/_modal-detail.php`: 25, 28, 31, 34, 40, 43, 46, 56, 64 · `views/audit-log/index.php`: 209 · `views/auth/login.php`: 61 · `views/dashboard/index.php`: 151, 170, 189, 217 · `views/layouts/footer.php`: 8 · `views/permissions/_modal_permission.php`: 20 · `views/permissions/detail.php`: 33, 37 · `views/roles/_modal_role.php`: 20 · `views/roles/detail.php`: 34, 38, 123 · `views/users/create.php`: 245, 292, 488 · `views/users/profile.php`: 36, 41, 45, 109 · `views/users/show.php`: 44, 70 · `views/users/update.php`: 240, 286, 533
- **`text-nowrap`** — 1 — `views/audit-log/index.php`: 202
- **`text-primary`** — 2 — `views/permissions/detail.php`: 29 · `views/roles/detail.php`: 30
- **`text-secondary`** — 1 — `views/dashboard/index.php`: 96
- **`text-uppercase`** — 2 — `views/audit-log/_modal-detail.php`: 56, 64
- **`text-white`** — 4 — `views/audit-log/_modal-detail.php`: 9, 14 · `views/permissions/detail.php`: 30 · `views/roles/detail.php`: 31
- **`text-white-50`** — 1 — `views/audit-log/_modal-detail.php`: 12
- **`tooltip`** ★ — 6 — `public/css/core/dark-mode.css`: 337, 342 · `public/css/core/ui-components.css`: 118, 125, 136, 147
- **`tooltip-inner`** ★ — 3 — `public/css/core/dark-mode.css`: 337 · `public/css/core/ui-components.css`: 125, 147
- **`user-footer`** — 1 — `views/layouts/header.php`: 108
- **`user-header`** — 1 — `views/layouts/header.php`: 100
- **`user-menu`** — 1 — `views/layouts/header.php`: 94
- **`w-100`** — 1 — `views/roles/detail.php`: 135

---

## Terceros embebidos en el bundle de AdminLTE 3 (30)

No son clases de AdminLTE: sus estilos vienen incluidos en `adminlte.min.css` **y** de sus CSS independientes, que siguen disponibles. Se documentan para no confundirlas con clases v3 a migrar.

### Select2 (13)

_También en `plugins/select2/select2.min.css` (vía AssetRegistry). El tema `select2-bootstrap4.min.css` debe migrarse a tema Bootstrap 5 (T19/T41)._

- **`select2-container--bootstrap4`** ★ — 37 usos — `public/css/core/dark-mode.css`: 226, 227, 233, 237, 241, 246, 252, 257, 262, 266, 273, 274 · `public/css/core/ui-components.css`: 13, 19, 20, 25, 26, 36, 54, 61, 70, 74, 75, 81, 85, 86, 92, 96, 97, 103, 107, 108, 210, 214, 245, 246, 252
- **`select2-container--focus`** ★ — 7 usos — `public/css/core/dark-mode.css`: 273 · `public/css/core/ui-components.css`: 26, 74, 85, 96, 107, 245
- **`select2-container--open`** ★ — 8 usos — `public/css/core/dark-mode.css`: 274 · `public/css/core/ui-components.css`: 19, 25, 75, 86, 97, 108, 246
- **`select2-dropdown`** ★ — 3 usos — `public/css/core/dark-mode.css`: 241 · `public/css/core/ui-components.css`: 36, 210
- **`select2-results__option`** ★ — 3 usos — `public/css/core/dark-mode.css`: 252, 262 · `public/css/core/ui-components.css`: 214
- **`select2-results__option--highlighted`** ★ — 2 usos — `public/css/core/dark-mode.css`: 257 · `public/css/core/ui-components.css`: 54
- **`select2-search__field`** ★ — 1 uso — `public/css/core/dark-mode.css`: 246
- **`select2-selection`** ★ — 17 usos — `public/css/core/dark-mode.css`: 273, 274 · `public/css/core/ui-components.css`: 70, 74, 75, 81, 85, 86, 92, 96, 97, 103, 107, 108, 245, 246, 252
- **`select2-selection--multiple`** ★ — 2 usos — `public/css/core/dark-mode.css`: 227, 266
- **`select2-selection--single`** ★ — 8 usos — `public/css/core/dark-mode.css`: 226, 233, 237 · `public/css/core/ui-components.css`: 13, 19, 20, 25, 26
- **`select2-selection__arrow`** ★ — 4 usos — `public/css/core/dark-mode.css`: 237 · `public/css/core/ui-components.css`: 13, 19, 20
- **`select2-selection__choice`** ★ — 1 uso — `public/css/core/dark-mode.css`: 266
- **`select2-selection__rendered`** ★ — 1 uso — `public/css/core/dark-mode.css`: 233

### SweetAlert2 (7)

_También en `plugins/sweetalert2/sweetalert2.min.css` (`header.php:43`), cargado en todas las páginas._

- **`swal2-error`** ★ — 1 uso — `public/css/modules/login/login.css`: 230
- **`swal2-icon`** ★ — 4 usos — `public/css/modules/login/login.css`: 225, 230, 235, 240
- **`swal2-info`** ★ — 1 uso — `public/css/modules/login/login.css`: 240
- **`swal2-popup`** ★ — 1 uso — `public/css/core/dark-mode.css`: 424
- **`swal2-success`** ★ — 1 uso — `public/css/modules/login/login.css`: 225
- **`swal2-title`** ★ — 2 usos — `public/css/core/dark-mode.css`: 429 · `public/css/modules/login/login.css`: 219
- **`swal2-warning`** ★ — 1 uso — `public/css/modules/login/login.css`: 235

### Bootstrap 4 — flechas tooltip (`.bs-tooltip-*`) (5)

_Markup del JS BS4 actual; BS5 renombra a `.tooltip-arrow`/`.popover-arrow` (ver Hallazgo 6)._

- **`bs-tooltip-auto`** ★ — 8 usos — `public/css/core/dark-mode.css`: 344, 346, 348, 350 · `public/css/core/ui-components.css`: 138, 140, 142, 144
- **`bs-tooltip-bottom`** ★ — 2 usos — `public/css/core/dark-mode.css`: 345 · `public/css/core/ui-components.css`: 139
- **`bs-tooltip-left`** ★ — 2 usos — `public/css/core/dark-mode.css`: 347 · `public/css/core/ui-components.css`: 141
- **`bs-tooltip-right`** ★ — 2 usos — `public/css/core/dark-mode.css`: 349 · `public/css/core/ui-components.css`: 143
- **`bs-tooltip-top`** ★ — 2 usos — `public/css/core/dark-mode.css`: 343 · `public/css/core/ui-components.css`: 137

### Font Awesome (4)

_Provisto por `lib/fontawesome/all.min.css` + `core/webfonts.css`; no depende de AdminLTE._

- **`fa`** ★ — 1 uso — `public/css/core/webfonts.css`: 51
- **`fab`** ★ — 1 uso — `public/css/core/webfonts.css`: 43
- **`far`** ★ — 2 usos — `public/css/core/webfonts.css`: 47 · `views/users/update.php`: 377
- **`fas`** ★ — 242 usos — `public/css/core/ui-components.css`: 305 · `public/css/core/webfonts.css`: 52 · `views/audit-log/_modal-detail.php`: 10, 74 · `views/audit-log/index.php`: 10, 27, 30, 96, 116, 133, 136, 154, 162, 166, 239 · `views/auth/accept_invitation.php`: 7, 23, 37, 46 · `views/auth/forgot_password.php`: 16, 24 · `views/auth/login.php`: 17, 30, 48 · `views/auth/reset_password.php`: 20, 34, 43 · `views/dashboard/index.php`: 10, 30, 33, 47, 50, 64, 67, 82, 84, 98, 100, 114, 116, 128, 142, 163, 182, 205, 210 · `views/layouts/auth.php`: 40 · `views/layouts/error.php`: 37 · `views/layouts/footer.php`: 10 · `views/layouts/header.php`: 68, 86, 91, 96 · `views/layouts/sidebar.php`: 40, 49, 52, 59, 67, 75, 88 · `views/permissions/_modal_permission.php`: 29, 32 · `views/permissions/detail.php`: 10, 11, 29, 30, 42, 52, 64, 67, 78, 82, 86, 93, 117, 123, 145, 165, 168 · `views/permissions/index.php`: 10, 25, 34, 43, 60, 63, 100, 107, 114 · `views/roles/_modal_role.php`: 29, 32 · `views/roles/detail.php`: 10, 11, 30, 31, 43, 54, 61, 70, 81, 87, 102, 105, 136 · `views/roles/index.php`: 10, 25, 34, 43, 60, 63, 103, 110, 117 · `views/users/create.php`: 10, 11, 31, 34, 46, 60, 74, 104, 119, 122, 134, 150, 164, 179, 182, 194, 241, 258, 273, 276, 309, 312, 321, 324, 358, 363, 378, 381, 391, 412, 433, 454, 477, 480, 492 · `views/users/index.php`: 10, 29, 32, 83, 86, 93, 102 · `views/users/profile.php`: 10, 40, 44, 50, 72, 77, 94, 118, 125, 136, 145, 157, 165, 171, 180, 186, 195, 201, 208 · `views/users/show.php`: 10, 11, 56, 60, 64, 74, 87, 90, 99, 106, 115, 119, 134, 139, 144, 159, 170, 184, 195, 209, 220, 234, 245, 261, 269, 275, 286, 296, 306 · `views/users/update.php`: 10, 11, 32, 35, 47, 61, 75, 105, 121, 124, 136, 152, 167, 183, 186, 215, 218, 224, 236, 253, 267, 270, 312, 315, 324, 327, 366, 369, 385, 397, 402, 417, 420, 430, 451, 473, 494, 518, 521, 541

### iCheck (1)

_También en `lib/bootstrap/icheck-bootstrap.min.css` (`auth.php:25`, tema BS4) — revisar con BS5 en el login (T45)._

- **`icheck-primary`** ★ — 2 usos — `public/css/modules/login/login-dark.css`: 95 · `views/auth/login.php`: 38

---

## Referencia — familias de clases que AdminLTE 4 sí define

Extraídas del CSS de `AdminLTE@v4.10.1` para orientar la Fase 2 (confirmar markup oficial al implementar):

- **Layout:** `app-wrapper`, `app-header`, `app-main`, `app-main-wrapper`, `app-content`, `app-content-header`, `app-content-top-area`, `app-content-bottom-area`, `app-footer`, `app-loaded`
- **Sidebar:** `app-sidebar`, `app-sidebar-wrapper`, `sidebar-wrapper`, `sidebar-brand`, `sidebar-menu`, `sidebar-search`, `sidebar-overlay`, `sidebar-mini`, `sidebar-open`, `sidebar-collapse`, `sidebar-expand`, `sidebar-without-hover`
- **Navbar:** `navbar-brand`, `navbar-nav`, `navbar-expand`, `navbar-toggler`, `navbar-dark`, `navbar-custom-menu`, `navbar-search`, `navbar-badge`

Mapping orientativo (v3 → v4): `wrapper`→`app-wrapper` · `main-header`→`app-header` · `content`→`app-content` · `content-header`→`app-content-header` · `main-footer`→`app-footer` · `main-sidebar`→`app-sidebar` · `nav-sidebar`→`sidebar-menu`.
