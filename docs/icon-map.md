# Mapa de iconos: Font Awesome 6 → Bootstrap Icons 1.13.1

Tarea T21 de `specs/001-adminlte-migration` (FR-35, C8). Recuento hecho el 2026-11-05 con
`grep -oE '\bfa-[a-z0-9-]+'` sobre `views/`, `public/js/core|modules` y `public/css/core|modules`
(sin `public/css/lib` ni plugins).

## Recuento

- **62 iconos distintos y 259 usos.** El plan y C8 hablan de 67: no pude reproducir esa cifra; la diferencia pudo venir del recuento previo a T14–T20 (que ya quitaron los iconos de `header.php`, `sidebar.php` y los layouts) o de contar modificadores y nombres de fuente (`fa-spin`, `fa-2x`, `fa-stack*`, `fa-solid-900`…), que aquí se excluyen.
- Los 62 tienen equivalente `bi-*` comprobado contra `public/css/lib/bootstrap-icons/bootstrap-icons.min.css`
  (2078 iconos; un script confirmó que cada clase de la tabla existe), salvo `spinner` (ver notas).
- Un solo uso con estilo `far` (`calendar-plus` en `users/update.php`); Bootstrap Icons no distingue estilos.

## Tabla

| Font Awesome              | Usos | Bootstrap Icons           | Nota                                                                                                                   |
| ------------------------- | ---: | ------------------------- | ---------------------------------------------------------------------------------------------------------------------- |
| `fa-minus`                |   20 | `bi-dash-lg`              |                                                                                                                        |
| `fa-key`                  |   19 | `bi-key`                  | En `fa-stack` de `permissions/detail.php`; el resto son iconos sueltos.                                                |
| `fa-eye-slash`            |   13 | `bi-eye-slash`            |                                                                                                                        |
| `fa-spinner`              |   12 | —                         | Rediseño: `spinner-border spinner-border-sm` (no es un icono); ver nota `fa-spin`.                                     |
| `fa-home`                 |   11 | `bi-house-door`           |                                                                                                                        |
| `fa-save`                 |   11 | `bi-floppy`               |                                                                                                                        |
| `fa-envelope`             |   10 | `bi-envelope`             |                                                                                                                        |
| `fa-times`                |   10 | `bi-x-lg`                 |                                                                                                                        |
| `fa-id-card`              |    7 | `bi-person-vcard`         | Tarjeta de documento de identidad.                                                                                     |
| `fa-lock`                 |    7 | `bi-lock`                 |                                                                                                                        |
| `fa-users`                |    7 | `bi-people`               |                                                                                                                        |
| `fa-check`                |    6 | `bi-check-lg`             |                                                                                                                        |
| `fa-phone`                |    6 | `bi-telephone`            |                                                                                                                        |
| `fa-user-alt`             |    6 | `bi-person`               |                                                                                                                        |
| `fa-arrow-circle-right`   |    5 | `bi-arrow-right-circle`   |                                                                                                                        |
| `fa-edit`                 |    5 | `bi-pencil-square`        | `pencil-square` conserva el aspecto de lápiz sobre cuadro.                                                             |
| `fa-eye`                  |    5 | `bi-eye`                  |                                                                                                                        |
| `fa-map-marker-alt`       |    5 | `bi-geo-alt`              |                                                                                                                        |
| `fa-user`                 |    5 | `bi-person`               |                                                                                                                        |
| `fa-user-plus`            |    5 | `bi-person-plus`          |                                                                                                                        |
| `fa-user-tag`             |    5 | `bi-person-badge`         | En `fa-stack` de `roles/detail.php`; el resto son iconos sueltos.                                                      |
| `fa-address-card`         |    4 | `bi-person-lines-fill`    | Sin tarjeta de dirección en Bootstrap Icons; la ficha con líneas es la más cercana.                                    |
| `fa-history`              |    4 | `bi-clock-history`        |                                                                                                                        |
| `fa-info-circle`          |    4 | `bi-info-circle`          |                                                                                                                        |
| `fa-plus`                 |    4 | `bi-plus-lg`              |                                                                                                                        |
| `fa-toggle-on`            |    4 | `bi-toggle-on`            |                                                                                                                        |
| `fa-arrow-left`           |    3 | `bi-arrow-left`           |                                                                                                                        |
| `fa-check-circle`         |    3 | `bi-check-circle`         |                                                                                                                        |
| `fa-check-square`         |    3 | `bi-check-square`         |                                                                                                                        |
| `fa-image`                |    3 | `bi-image`                |                                                                                                                        |
| `fa-shield-alt`           |    3 | `bi-shield-lock`          |                                                                                                                        |
| `fa-square`               |    3 | `bi-square`               |                                                                                                                        |
| `fa-at`                   |    2 | `bi-at`                   |                                                                                                                        |
| `fa-calendar-alt`         |    2 | `bi-calendar3`            |                                                                                                                        |
| `fa-circle`               |    2 | `bi-circle`               | Solo dentro de `fa-stack` (ver rediseño).                                                                              |
| `fa-envelope-open-text`   |    2 | `bi-envelope-open`        |                                                                                                                        |
| `fa-exclamation-triangle` |    2 | `bi-exclamation-triangle` |                                                                                                                        |
| `fa-lock-open`            |    2 | `bi-unlock`               |                                                                                                                        |
| `fa-paper-plane`          |    2 | `bi-send`                 |                                                                                                                        |
| `fa-question-circle`      |    2 | `bi-question-circle`      |                                                                                                                        |
| `fa-sign-in-alt`          |    2 | `bi-box-arrow-in-right`   |                                                                                                                        |
| `fa-times-circle`         |    2 | `bi-x-circle`             |                                                                                                                        |
| `fa-user-lock`            |    2 | `bi-person-lock`          |                                                                                                                        |
| `fa-address-book`         |    1 | `bi-journal-bookmark`     | Equivalente aproximado (libreta/diario).                                                                               |
| `fa-calendar-plus`        |    1 | `bi-calendar-plus`        |                                                                                                                        |
| `fa-camera`               |    1 | `bi-camera`               |                                                                                                                        |
| `fa-chart-line`           |    1 | `bi-graph-up`             |                                                                                                                        |
| `fa-chevron-down`         |    1 | `bi-chevron-down`         |                                                                                                                        |
| `fa-circle-notch`         |    1 | `bi-pie-chart`            | Se usa como icono del título «Users by Status»; se sustituye por un gráfico circular, que describe mejor el contenido. |
| `fa-clock`                |    1 | `bi-clock`                |                                                                                                                        |
| `fa-file-pdf`             |    1 | `bi-file-earmark-pdf`     |                                                                                                                        |
| `fa-filter`               |    1 | `bi-funnel`               |                                                                                                                        |
| `fa-info`                 |    1 | `bi-info-lg`              |                                                                                                                        |
| `fa-map-marked-alt`       |    1 | `bi-map`                  |                                                                                                                        |
| `fa-search`               |    1 | `bi-search`               |                                                                                                                        |
| `fa-sliders-h`            |    1 | `bi-sliders`              |                                                                                                                        |
| `fa-sync-alt`             |    1 | `bi-arrow-repeat`         |                                                                                                                        |
| `fa-tag`                  |    1 | `bi-tag`                  |                                                                                                                        |
| `fa-user-check`           |    1 | `bi-person-check`         |                                                                                                                        |
| `fa-user-edit`            |    1 | `bi-person-gear`          | Persona con engranaje: no existe persona con lápiz; el significado (editar usuario) se mantiene por el contexto.       |
| `fa-user-minus`           |    1 | `bi-person-dash`          |                                                                                                                        |
| `fa-user-slash`           |    1 | `bi-person-slash`         |                                                                                                                        |

## Rediseños (sin equivalente directo)

- **`fa-spin` (12 usos, más la regla `.btn:disabled .fa-spinner` y `@keyframes fa-spin` en `public/css/core/ui-components.css`):** el icono girando pasa a `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`. El JS que hoy hace `removeClass('fa-x').addClass('fa-spinner fa-spin')` (auth y módulos de usuarios, roles y permisos) deberá insertar ese `span` en lugar de cambiar clases; lo cubren las tareas de cada módulo.
- **`fa-stack` (2 usos: `views/roles/detail.php:29`, `views/permissions/detail.php:28`):** círculo azul con icono blanco encima. Rediseño: `<span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white fs-2" style="width:3rem;height:3rem"><i class="bi bi-person-badge"></i></span>` (con la clase de tamaño propia en CSS en lugar de `style` inline). Los `fa-circle` y el `fa-2x` desaparecen con él.
- **`fa-2x` / `fa-stack-1x` / `fa-stack-2x`:** modificadores de tamaño del `fa-stack`; no se trasladan.
- **`public/css/core/webfonts.css`:** solo declara `@font-face` de Font Awesome; se borra en T49.
