# Tareas — Spec 001

Tareas de menos de 30 min, ordenadas por dependencia. Cada una: sus FR/NFR, una casilla y una línea «Done when:» verificable. En las tareas de interfaz, «verificado» significa checklist manual de la pantalla en claro y oscuro, sin errores de consola, con captura o registro como evidencia (NFR-5).

## Fase A — Inventarios y línea base

- [x] T1. Inventariar clases CSS de AdminLTE 3 en `views/` y `public/css/`. (FR: —)
      Done when: lista escrita con rutas y números de línea. → `docs/adminlte3-class-inventory.md`
- [x] T2. Inventariar usos de `$` / `jQuery` en `public/js/`. (FR: —)
      Done when: lista escrita con rutas y números de línea. → `docs/jquery-usage-inventory.md`
- [x] T3. Inventariar plugins activos en `app/Core/AssetRegistry.php`. (FR: —)
      Done when: lista de plugins y archivos mapeados. → `docs/plugin-assets-inventory.md`
- [ ] T4. Ejecutar `composer check` y registrar la línea base. (NFR-4)
      Done when: `composer check` verde antes de tocar archivos de la app.
- [ ] T5. Inventariar atributos data-API heredados (`data-toggle`, `data-dismiss`, `data-target`, `data-parent`, `data-card-widget`, `data-widget`) en `views/` y `public/js/`. (FR-32)
      Done when: `docs/data-api-inventory.md` lista cada uso con ruta y línea y su equivalente `data-bs-*`/`data-lte-*`.

## Fase B — Fuentes de assets y copia a `public/` (versiones en `plan.md`)

- [ ] T6. Ejecutar `composer require --dev almasaeed2010/adminlte:^4.10` y confirmar que `composer.lock` fija 4.10.1. (FR-1, NFR-3, NFR-6)
      Done when: `vendor/almasaeed2010/adminlte/dist/css/adminlte.min.css` existe y `composer check` sigue verde.
- [ ] T7. Crear `package.json` privado (`devDependencies` fijas: bootstrap 5.3.8, overlayscrollbars 2.16.0, bootstrap-icons 1.13.2, tabulator-tables 6.7.1, tom-select 2.6.2, chart.js 4.6.1, sweetalert2 11.26.25, jspdf 4.2.1, jspdf-autotable 5.0.9, xlsx 0.20.3 desde `cdn.sheetjs.com`), `.nvmrc` (22), `package-lock.json`, y añadir `node_modules/` a `.gitignore`. (FR-33, NFR-1, NFR-6)
      Done when: `npm ci` termina sin errores y `npm ls` muestra las versiones del plan.
- [ ] T8. Escribir `scripts/sync-assets.mjs` + `npm run assets`: copia `adminlte.min.css/js`, `bootstrap.bundle.min.js`, OverlayScrollbars (CSS+JS) y Bootstrap Icons (CSS + `fonts/*.woff2`/`.woff` con ruta relativa) a `public/css|js/lib/`. (FR-1, FR-32, FR-35, NFR-6)
      Done when: tras `npm run assets` los archivos existen con la versión esperada en su cabecera y un `<i class="bi bi-house">` de prueba muestra el icono sin 404 de fuentes.
- [ ] T9. Ampliar el script con Tabulator (`tabulator.min.js` + `tabulator_bootstrap5.min.css`) y Tom Select (`tom-select.complete.min.js` + `tom-select.bootstrap5.min.css`) a `public/**/plugins/`. (FR-11, FR-25, NFR-6)
      Done when: archivos presentes con la versión esperada y el script es idempotente (segunda ejecución sin cambios en `git status`).
- [ ] T10. Ampliar el script con SheetJS (`xlsx.full.min.js`), jsPDF (`jspdf.umd.min.js`) y jspdf-autotable. (FR-11, NFR-6)
      Done when: archivos presentes con las versiones esperadas y su sha384 anotado.
- [ ] T11. Ampliar el script con Chart.js (`chart.umd.min.js`) y SweetAlert2 (JS+CSS), y añadir el ecosistema `npm` a `.github/dependabot.yml`. (FR-17, FR-28, NFR-6)
      Done when: archivos presentes con las versiones esperadas y Dependabot lista composer y npm.
- [ ] T12. Excluir `package.json`, `package-lock.json`, `scripts/` y `node_modules/` del artefacto de despliegue y del docroot, y documentar el flujo `npm ci && npm run assets` en `AGENTS.md` (sección Dependencias frontend, con sha384 y origen). (NFR-1, NFR-6)
      Done when: el workflow de despliegue (o la lista de exclusión) no copia esos archivos y `AGENTS.md` describe el flujo.
- [ ] T13. Reescribir los mapas de `AssetRegistry` con las rutas nuevas y el orden de carga del plan. (FR-33, NFR-2)
      Done when: `composer test:unit` verde y cada ruta del mapa existe en disco.

## Fase C — Shell, tema e iconos

- [ ] T14. Reescribir `views/layouts/header.php`: `<head>` con anti-FOUC, `data-lte-color-mode="off"`, `app-wrapper`, `app-header` y menú de usuario. (FR-1, FR-4, FR-5, FR-21)
      Done when: verificado; sin parpadeo al recargar en oscuro.
- [ ] T15. Reescribir `views/layouts/sidebar.php`: `app-sidebar`, `sidebar-menu`, `data-lte-toggle="sidebar"`/treeview, filtrado por permiso e ítem activo. (FR-2, FR-3, FR-4, FR-6)
      Done when: verificado con un usuario sin permisos y con el admin; el botón colapsa y expande el menú.
- [ ] T16. Reescribir `views/layouts/footer.php`: `app-footer`, versión y orden de carga de scripts. (FR-1, FR-7)
      Done when: verificado; sin errores de consola.
- [ ] T17. Adaptar `views/layouts/messages.php`: toasts por `ToastUtils` y alerta `<noscript>` de Bootstrap 5. (FR-9)
      Done when: un mensaje flash aparece con y sin JavaScript.
- [ ] T18. Reescribir el interruptor de tema: `theme-toggle.js` escribe `data-bs-theme` y `localStorage['theme']`; fallback a `prefers-color-scheme`. (FR-20, FR-22)
      Done when: verificado; la elección persiste al recargar y entre páginas, y `html.dark-mode` ya no aparece en el repo.
- [ ] T19. Reescribir `views/layouts/auth.php` como página independiente con las clases de login de AdminLTE 4. (FR-8, FR-21)
      Done when: login, olvido, restablecimiento e invitación renderizan en ambos temas.
- [ ] T20. Reescribir `views/layouts/error.php` y `views/errors/{403,404,500}.php` sin `error-page`; tonalidad por `[data-tone]`. (FR-31)
      Done when: los tres códigos renderizan en ambos temas.
- [ ] T21. Mapa de iconos: tabla de los 67 iconos de Font Awesome usados a su equivalente `bi bi-*` en `docs/icon-map.md`. (FR-35)
      Done when: los 67 tienen equivalente verificado contra el CSS 1.13.2 o una nota de rediseño (`fa-stack`, `fa-spin`).

## Fase D — Helpers JS nativos (sin jQuery)

- [ ] T22. `public/js/core/http.js`: `fetch` con `X-Requested-With`, token CSRF vigente y `URLSearchParams`; manejo de JSON y errores. (FR-28, FR-29, FR-30, NFR-7)
      Done when: una petición de prueba recibe JSON (no HTML) en un 403 de CSRF y el siguiente envío usa el token nuevo.
- [ ] T23. `common-utils.js`: toggle de contraseña, delegación de eventos y selectores `data-bs-*`; eliminar `ajaxRequest` muerto. (FR-30, FR-32, NFR-7)
      Done when: el toggle funciona en login y en un formulario admin; sin referencias a jQuery.
- [ ] T24. `public/js/core/form-validator.js`: validación nativa, `.invalid-feedback`, unicidad con `fetch` + `AbortController` + debounce, `equalTo` y envío AJAX con indicador de carga. (FR-14, FR-27, FR-29, NFR-7)
      Done when: un formulario de prueba marca los campos inválidos, rechaza un correo repetido y no deja `<id>-error` duplicado de AdminLTE.
- [ ] T25. `ui-components.js`: Tom Select (`maxOptions: null`, `allowEmptyOption`, etiquetas) y tooltips delegados de Bootstrap 5. (FR-25, FR-26, FR-24)
      Done when: un select con búsqueda y un tooltip funcionan en una página y dentro de un modal, sin `dropdownParent`.
- [ ] T26. `public/js/core/table-utils.js`: opciones de Tabulator, formatters con `escapeHtml`, búsqueda global, selector de columnas, textos en español. (FR-11, FR-26, NFR-8)
      Done when: una tabla de prueba con una celda con marcado HTML la muestra como texto.
- [ ] T27. `table-utils.js`: exportación Copiar, CSV, Excel, PDF e Imprimir. (FR-11, NFR-8)
      Done when: los cinco formatos descargan una tabla con acentos y una celda con marcado sin `<b>…</b>` literal.
- [ ] T28. `sweetalert-utils.js`: pasar `theme` según `data-bs-theme`; conservar `escapeHtml`; sin jQuery. (FR-23, FR-28, NFR-7)
      Done when: toast y diálogo se ven bien en claro y oscuro.

## Fase E — Pantallas (markup + JS de cada una)

- [ ] T29. `views/roles/index.php` + `index-roles.js`: tabla con Tabulator, estado y borrado con confirmación. (FR-11, FR-12, FR-16, FR-26, FR-35)
      Done when: verificado; la tabla pagina, ordena, busca y exporta.
- [ ] T30. `views/roles/_modal_role.php` + `modal-role.js`: modal crear/editar, validación y sincronizar permisos. (FR-12, FR-13, FR-14, FR-15, FR-25, FR-27, FR-32)
      Done when: verificado; el Tom Select abre dentro del modal y la unicidad del nombre se valida.
- [ ] T31. `views/roles/detail.php` + `detail-role.js`. (FR-11, FR-13)
      Done when: verificado; el detalle muestra los permisos del rol.
- [ ] T32. `views/permissions/index.php` + `index-permissions.js`. (FR-11, FR-12, FR-26)
      Done when: verificado; la tabla de permisos pagina, ordena, busca y exporta.
- [ ] T33. `views/permissions/_modal_permission.php` + `modal-permission.js`. (FR-12, FR-14, FR-25, FR-27, FR-32)
      Done when: verificado; la unicidad del nombre se valida en el modal.
- [ ] T34. `views/permissions/detail.php` + `detail-permission.js`: asignar y revocar a usuarios. (FR-13, FR-15, FR-16)
      Done when: verificado; asignar y revocar piden confirmación y notifican.
- [ ] T35. `views/users/index.php` + `index-users.js`: tabla, filtros y cambio de estado con confirmación. (FR-11, FR-12, FR-16, FR-26, FR-32)
      Done when: verificado; la desactivación pide confirmación y las acciones respetan permisos por fila.
- [ ] T36. `views/users/create.php` + `create-user.js`: formulario, validación, selectores e invitación. (FR-12, FR-14, FR-25, FR-27, FR-28, FR-29)
      Done when: verificado; crear usuario valida la unicidad de correo y documento y notifica.
- [ ] T37. `views/users/update.php` + `update-user.js`: formulario y lista de permisos con casillas. (FR-12, FR-13, FR-15, FR-27, FR-34)
      Done when: verificado; se conservan los valores al desmarcar permisos.
- [ ] T38. `views/users/show.php` + `show-user.js`: detalle, pestañas y desbloqueo. (FR-11, FR-12, FR-32)
      Done when: verificado; las pestañas cambian y desbloquear funciona.
- [ ] T39. `views/users/profile.php` + `profile-user.js`: perfil, cambio de contraseña y pestañas, con `http.js`. (FR-12, FR-27, FR-32)
      Done when: verificado; la contraseña se actualiza sin cerrar sesión y la petición lleva `X-Requested-With`.
- [ ] T40. `views/audit-log/index.php`, `_modal-detail.php` + `index-audit.js`: tabla de auditoría, filtro de fechas nativo, detalle y enlace de exportación del servidor. (FR-18, FR-19, FR-32, FR-36)
      Done when: verificado; el filtro por fechas funciona con teclado, el PDF del servidor se descarga y el detalle escapa valores de usuario.
- [ ] T41. `views/dashboard/index.php` + `index-dashboard.js`: cajas de métricas y gráficos con la API de Chart.js 4. (FR-17, FR-23)
      Done when: verificado; los gráficos se leen bien en ambos temas.
- [ ] T42. `views/auth/login.php` + `login.js`: «Recordarme» con `.form-check`, validación nativa y sin iCheck. (FR-8, FR-27, FR-34)
      Done when: verificado; iniciar sesión con y sin «Recordarme» funciona.
- [ ] T43. `views/auth/{forgot_password,reset_password,accept_invitation}.php` + sus JS. (FR-8, FR-10, FR-27)
      Done when: verificado; los tres flujos validan y envían, y la bienvenida aparece al primer inicio.

## Fase F — Estilos

- [ ] T44. `dark-mode.css`: pasar los selectores `html.dark-mode` a `[data-bs-theme="dark"]` y retirar parches de utilidades Bootstrap ya cubiertos. (FR-20, FR-23)
      Done when: `grep dark-mode public/css` solo encuentra el nombre del archivo; claro/oscuro verificados en una pantalla.
- [ ] T45. Eliminar los parches de `.dataTables_*`, `.select2*` y `.swal2-*`; añadir el ajuste del borde del desplegable de Tom Select. (FR-23)
      Done when: tabla, selector y diálogo se ven bien en oscuro.
- [ ] T46. `public/css/modules/login/*` y `errors/*` (claro y oscuro) sin clases eliminadas. (FR-8, FR-23, FR-31)
      Done when: login y errores verificados en ambos temas.
- [ ] T47. `public/css/modules/dashboard/*` y `audit-log/*`. (FR-17, FR-18, FR-23)
      Done when: dashboard y auditoría verificados en ambos temas.
- [ ] T48. `public/css/modules/users|roles|permissions/*` y `public/css/core/ui-components.css`. (FR-23, FR-32)
      Done when: ninguna referencia a clases eliminadas en v4 (barrido con el inventario T1).

## Fase G — Limpieza y verificación

- [ ] T49. Borrar los archivos obsoletos de la lista del plan y quitar `css/core/webfonts.css`. (FR-33, NFR-7)
      Done when: `grep` de cada nombre borrado da cero resultados y ninguna página da 404.
- [ ] T50. Barridos de cierre: atributos heredados, jQuery y Font Awesome. (FR-32, FR-35, NFR-7)
      Done when: cero resultados de `data-(toggle|dismiss|target|parent|card-widget|widget)=`, de `jQuery|\$\(|\$\.` en `views/` y `public/js/core|modules`, y de `fa-` en `views/` y `public/`.
- [ ] T51. Auditoría axe-core por pantalla en claro y oscuro (transiciones desactivadas; tema con el control real tras limpiar `localStorage`). (FR-23, FR-24)
      Done when: cero violaciones de contraste y de accesibilidad en las pantallas del checklist C3.
- [ ] T52. Ejecutar `composer check` y confirmar que Composer solo añadió `almasaeed2010/adminlte` en `require-dev` y que `npm ci && npm run assets` no deja cambios sin versionar. (NFR-1, NFR-3, NFR-4, NFR-6)
      Done when: lint + stan + unit + integration verdes, `composer.json` solo difiere en esa línea y `git status` queda limpio tras `npm run assets`.
- [ ] T53. Prueba manual del camino feliz (C3): login, CRUD de usuarios, roles y permisos, asignar/revocar, auditoría con exportaciones, dashboard, perfil, tema, menú. (FR-1 a FR-19, FR-20 a FR-23, NFR-5)
      Done when: checklist completo con evidencia.
- [ ] T54. Prueba manual de casos límite: formulario vacío, permiso denegado, token CSRF vencido, doble envío de borrado, unicidad repetida, tema persistido. (FR-14, FR-15, FR-20, FR-21, FR-30)
      Done when: cada caso responde como en la versión anterior.
- [ ] T55. Prueba en móvil y escritorio, y solo con teclado. (FR-1, FR-2, FR-24)
      Done when: menú, modales y tablas operables en 375 px y 1280 px y con teclado.
- [ ] T56. Actualizar `AGENTS.md`: pila (AdminLTE 4 / Bootstrap 5.3, sin jQuery), tabla de versiones, origen y sha384 de cada librería (SheetJS y jsPDF a vigilar a mano), orden de carga, tema por `data-bs-theme`, lecciones nuevas; corregir menciones obsoletas. (NFR-6)
      Done when: `AGENTS.md` coincide con el estado final del repo.

## Cierre de la feature

_Casillas sueltas, fuera de la numeración `Tn`; no se implementan con `/sdd:implement`._

- [ ] Verificar todos los FR (`/sdd:validate` → `specs/001-adminlte-migration/validation.md`).
- [ ] Confirmar que `/sdd:validate` movió la feature a `Done ✅` en `docs/roadmap.md`, enlazando esta carpeta.
- [ ] Tras `/sdd:validate`, eliminar los documentos de trabajo `docs/adminlte3-class-inventory.md`, `docs/jquery-usage-inventory.md`, `docs/plugin-assets-inventory.md`, `docs/data-api-inventory.md` y `docs/research/`, y quitar sus enlaces de `AGENTS.md` y `docs/analisis-migracion-adminlte4.md`. Antes, confirmar que spec, plan y `AGENTS.md` recogen todo lo que aún sirva.
- [ ] Actualizar `CHANGELOG.md` y el tag de versión (sin prefijo `v`).
- [ ] Despliegue: no hay pasos manuales; `docs/`, `specs/` y `deliverables/` se excluyen del artefacto.
