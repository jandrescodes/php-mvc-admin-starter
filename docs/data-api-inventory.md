# Inventario de atributos data-API heredados (T5 — Spec 001)

> Atributos de Bootstrap 4 / AdminLTE 3 que dejan de funcionar en Bootstrap 5.3 / AdminLTE 4 (FR-32). Búsqueda: `grep -rnE 'data-(toggle|dismiss|target|parent|card-widget|widget)\b'` sobre `views/`, `public/js/core` y `public/js/modules` (código propio; `public/js/lib` y `plugins` son de terceros y se eliminan en T49). Cada fila es un uso, verificado con su línea.

## Resumen

| Atributo                      | Usos   | Equivalente                                                            |
| ----------------------------- | ------ | ---------------------------------------------------------------------- |
| `data-toggle`                 | 36     | `data-bs-toggle` (dropdown, tooltip, collapse, pill, tab)              |
| `data-card-widget="collapse"` | 22     | `data-lte-toggle="card-collapse"`                                      |
| `data-dismiss`                | 8      | `data-bs-dismiss`                                                      |
| `data-target`                 | 8      | `data-bs-target`                                                       |
| `data-parent`                 | 8      | `data-bs-parent` (el acordeón pasa a `.accordion` / `.accordion-item`) |
| `data-widget`                 | 3      | `data-lte-toggle` (`sidebar`, `fullscreen`, `treeview`)                |
| **Total**                     | **85** |                                                                        |

Equivalentes de AdminLTE 4 tomados de `docs/research/adminlte4-docs-2026-11-04.md` (tabla de migración, líneas ~245-248). Los `data-bs-*` son la API de Bootstrap 5.

## Notas

- `views/users/create.php` y `update.php` (líneas 390-497 y 429-498) arman un acordeón con `.btn-link .btn-block .text-left` y `.collapse`; en Bootstrap 5 se reescribe con `.accordion-button` y `data-bs-parent` en cada `.accordion-collapse`.
- Los usos de `ui-components.js` y `show-user.js` son selectores JS (no atributos impresos): `[data-toggle="tooltip"]` pasa a `[data-bs-toggle="tooltip"]` y `a[data-toggle="pill"]` a `a[data-bs-toggle="pill"]`; el evento `shown.bs.tab` se conserva.
- `data-accordion="false"` de `sidebar.php:35` no está en el barrido (no es heredado de la lista); queda para T15.
- Los botones `.close` con `data-dismiss` pasan además a `.btn-close` (ver `docs/adminlte3-class-inventory.md`).

## Usos

| Archivo:línea                                | Atributo                        | Equivalente                       | Tarea     |
| -------------------------------------------- | ------------------------------- | --------------------------------- | --------- |
| `public/js/core/ui-components.js:214`        | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T25       |
| `public/js/core/ui-components.js:217`        | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T25       |
| `public/js/core/ui-components.js:220`        | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T25       |
| `public/js/core/ui-components.js:232`        | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T25       |
| `public/js/core/ui-components.js:246`        | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T25       |
| `public/js/core/ui-components.js:290`        | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T25       |
| `public/js/core/ui-components.js:292`        | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T25       |
| `public/js/core/ui-components.js:332`        | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T25       |
| `public/js/modules/users/show-user.js:12`    | `data-toggle="pill"`            | `data-bs-toggle="pill"`           | T38       |
| `views/audit-log/_modal-detail.php:14`       | `data-dismiss="modal"`          | `data-bs-dismiss="modal"`         | T40b      |
| `views/audit-log/_modal-detail.php:73`       | `data-dismiss="modal"`          | `data-bs-dismiss="modal"`         | T40b      |
| `views/audit-log/index.php:29`               | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T40a      |
| `views/audit-log/index.php:165`              | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T40a      |
| `views/audit-log/index.php:236`              | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T40a      |
| `views/layouts/header.php:68`                | `data-widget="pushmenu"`        | `data-lte-toggle="sidebar"`       | T14       |
| `views/layouts/header.php:90`                | `data-widget="fullscreen"`      | `data-lte-toggle="fullscreen"`    | T14       |
| `views/layouts/header.php:95`                | `data-toggle="dropdown"`        | `data-bs-toggle="dropdown"`       | T14       |
| `views/layouts/sidebar.php:35`               | `data-widget="treeview"`        | `data-lte-toggle="treeview"`      | T15       |
| `views/permissions/_modal_permission.php:7`  | `data-dismiss="modal"`          | `data-bs-dismiss="modal"`         | T33       |
| `views/permissions/_modal_permission.php:28` | `data-dismiss="modal"`          | `data-bs-dismiss="modal"`         | T33       |
| `views/permissions/detail.php:85`            | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T34       |
| `views/permissions/detail.php:116`           | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T34       |
| `views/permissions/detail.php:122`           | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T34       |
| `views/permissions/detail.php:146`           | `data-dismiss="modal"`          | `data-bs-dismiss="modal"`         | T34       |
| `views/permissions/detail.php:164`           | `data-dismiss="modal"`          | `data-bs-dismiss="modal"`         | T34       |
| `views/permissions/index.php:62`             | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T32       |
| `views/permissions/index.php:99`             | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T32       |
| `views/permissions/index.php:106`            | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T32       |
| `views/permissions/index.php:113`            | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T32       |
| `views/roles/_modal_role.php:7`              | `data-dismiss="modal"`          | `data-bs-dismiss="modal"`         | T30       |
| `views/roles/_modal_role.php:28`             | `data-dismiss="modal"`          | `data-bs-dismiss="modal"`         | T30       |
| `views/roles/index.php:62`                   | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T29       |
| `views/roles/index.php:102`                  | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T29       |
| `views/roles/index.php:109`                  | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T29       |
| `views/roles/index.php:116`                  | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T29       |
| `views/users/create.php:33`                  | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T36a/T36b |
| `views/users/create.php:121`                 | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T36a/T36b |
| `views/users/create.php:181`                 | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T36a/T36b |
| `views/users/create.php:275`                 | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T36a/T36b |
| `views/users/create.php:311`                 | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T36a/T36b |
| `views/users/create.php:380`                 | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T36a/T36b |
| `views/users/create.php:390`                 | `data-toggle="collapse"`        | `data-bs-toggle="collapse"`       | T36a/T36b |
| `views/users/create.php:390`                 | `data-target="#collapseOne"`    | `data-bs-target`                  | T36a/T36b |
| `views/users/create.php:395`                 | `data-parent="#accordionGuide"` | `data-bs-parent`                  | T36a/T36b |
| `views/users/create.php:411`                 | `data-toggle="collapse"`        | `data-bs-toggle="collapse"`       | T36a/T36b |
| `views/users/create.php:411`                 | `data-target="#collapseTwo"`    | `data-bs-target`                  | T36a/T36b |
| `views/users/create.php:416`                 | `data-parent="#accordionGuide"` | `data-bs-parent`                  | T36a/T36b |
| `views/users/create.php:432`                 | `data-toggle="collapse"`        | `data-bs-toggle="collapse"`       | T36a/T36b |
| `views/users/create.php:432`                 | `data-target="#collapseThree"`  | `data-bs-target`                  | T36a/T36b |
| `views/users/create.php:437`                 | `data-parent="#accordionGuide"` | `data-bs-parent`                  | T36a/T36b |
| `views/users/create.php:453`                 | `data-toggle="collapse"`        | `data-bs-toggle="collapse"`       | T36a/T36b |
| `views/users/create.php:453`                 | `data-target="#collapseFour"`   | `data-bs-target`                  | T36a/T36b |
| `views/users/create.php:458`                 | `data-parent="#accordionGuide"` | `data-bs-parent`                  | T36a/T36b |
| `views/users/create.php:479`                 | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T36a/T36b |
| `views/users/index.php:31`                   | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T35a/T35b |
| `views/users/index.php:82`                   | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T35a/T35b |
| `views/users/index.php:85`                   | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T35a/T35b |
| `views/users/index.php:92`                   | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T35a/T35b |
| `views/users/index.php:101`                  | `data-toggle="tooltip"`         | `data-bs-toggle="tooltip"`        | T35a/T35b |
| `views/users/profile.php:71`                 | `data-toggle="tab"`             | `data-bs-toggle="tab"`            | T39       |
| `views/users/profile.php:76`                 | `data-toggle="tab"`             | `data-bs-toggle="tab"`            | T39       |
| `views/users/show.php:133`                   | `data-toggle="pill"`            | `data-bs-toggle="pill"`           | T38       |
| `views/users/show.php:138`                   | `data-toggle="pill"`            | `data-bs-toggle="pill"`           | T38       |
| `views/users/show.php:143`                   | `data-toggle="pill"`            | `data-bs-toggle="pill"`           | T38       |
| `views/users/update.php:34`                  | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T37       |
| `views/users/update.php:123`                 | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T37       |
| `views/users/update.php:185`                 | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T37       |
| `views/users/update.php:217`                 | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T37       |
| `views/users/update.php:269`                 | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T37       |
| `views/users/update.php:314`                 | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T37       |
| `views/users/update.php:368`                 | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T37       |
| `views/users/update.php:419`                 | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T37       |
| `views/users/update.php:429`                 | `data-toggle="collapse"`        | `data-bs-toggle="collapse"`       | T37       |
| `views/users/update.php:429`                 | `data-target="#collapseOne"`    | `data-bs-target`                  | T37       |
| `views/users/update.php:434`                 | `data-parent="#accordionGuide"` | `data-bs-parent`                  | T37       |
| `views/users/update.php:450`                 | `data-toggle="collapse"`        | `data-bs-toggle="collapse"`       | T37       |
| `views/users/update.php:450`                 | `data-target="#collapseTwo"`    | `data-bs-target`                  | T37       |
| `views/users/update.php:455`                 | `data-parent="#accordionGuide"` | `data-bs-parent`                  | T37       |
| `views/users/update.php:472`                 | `data-toggle="collapse"`        | `data-bs-toggle="collapse"`       | T37       |
| `views/users/update.php:472`                 | `data-target="#collapseThree"`  | `data-bs-target`                  | T37       |
| `views/users/update.php:477`                 | `data-parent="#accordionGuide"` | `data-bs-parent`                  | T37       |
| `views/users/update.php:493`                 | `data-toggle="collapse"`        | `data-bs-toggle="collapse"`       | T37       |
| `views/users/update.php:493`                 | `data-target="#collapseFour"`   | `data-bs-target`                  | T37       |
| `views/users/update.php:498`                 | `data-parent="#accordionGuide"` | `data-bs-parent`                  | T37       |
| `views/users/update.php:520`                 | `data-card-widget="collapse"`   | `data-lte-toggle="card-collapse"` | T37       |
