# Plan técnico — Spec 001

> Fase 4 de SDD. HOW de la migración. Cada decisión cita el FR/NFR/C que cubre. Versiones y archivos verificados el 2026-11-04 contra npm, GitHub, jsDelivr, SheetJS y el código fuente de AdminLTE 4.10.1 (informes de la sesión).

## Entrega de assets (NFR-1, NFR-3, NFR-6)

Fuente de descarga según la documentación oficial de AdminLTE 4 (rutas Composer y npm), sin bundler:

- **Composer:** `composer require --dev almasaeed2010/adminlte:^4.10` → `vendor/almasaeed2010/adminlte/dist/` (4.10.1; el paquete npm `admin-lte` se quedó en 4.10.0). Solo `css/adminlte.min.css` y `js/adminlte.min.js` se usan; `adminlte.css` ya incluye el CSS de Bootstrap 5.3.8. `require-dev`: producción no instala `vendor/` de desarrollo.
- **npm:** `package.json` privado con `devDependencies` fijas (sin `^`) y `package-lock.json` versionado: `bootstrap`, `overlayscrollbars`, `bootstrap-icons`, `tabulator-tables`, `tom-select`, `chart.js`, `sweetalert2`, `jspdf`, `jspdf-autotable`, y `xlsx` desde el tarball de `cdn.sheetjs.com` (el paquete de npm, 0.18.5, tiene dos CVE). Node 22 LTS (`.nvmrc`; mínimo 18 según AdminLTE). `node_modules/` y `vendor/` fuera de Git.
- **Copia:** `scripts/sync-assets.mjs` (Node sin dependencias) copia solo los archivos listados abajo a `public/` y lo que copia se versiona. Se ejecuta con `npm run assets`. Es un paso de desarrollo, no un build: no transforma, concatena ni minifica.
- **Despliegue:** no existe workflow de despliegue en el repo. La exclusión se define con `.gitattributes` (`export-ignore` para `package.json`, `package-lock.json`, `scripts/`, `.nvmrc`, `docs/`, `specs/`, `deliverables/`, `tests/`) y se documenta la lista para cualquier `rsync`/`scp` del runner; el servidor no necesita Node ni `node_modules/`.
- **Dependabot:** se añade el ecosistema `npm` y se mantiene `composer`. Dos grupos: `adminlte-stack` (Bootstrap, Bootstrap Icons, OverlayScrollbars, Tabulator, Tom Select, Chart.js; con `ignore` de versiones, solo suben con AdminLTE o por aviso de seguridad) y `client-libs` (SweetAlert2, jsPDF, jspdf-autotable; última estable). SheetJS (tarball) no lo vigila Dependabot.
- **CI (NFR-9):** el job `quality` de `.github/workflows/tests.yml` añade `npm ci && npm run assets && git diff --exit-code -- public/` (Node 22). Como un bump de Dependabot solo cambia el lockfile, ese check falla hasta que se ejecute `npm run assets` y se versione la copia. El filtro `paths-ignore` deja de ignorar `package*.json`, `scripts/**`, `composer.lock` y también `public/css/**` y `public/js/**` (si no, un cambio manual solo en `public/` se saltaría el check). El job además corre `scripts/check-asset-paths.php`. Es el único cambio de CI/CD dentro del alcance.
- **Rama de migración:** todo el trabajo va en `feat/adminlte4-migration`; `main` conserva AdminLTE 3.2.0 funcionando (respaldo) hasta que T53 a T55 pasen. Dentro de la rama, las fases C y D dejan pantallas con jQuery rotas a propósito: no hay capa de convivencia (sería código desechable). Hasta la fase E, el gate de las fases B a D se verifica en las pantallas sin JS de módulo (login y errores) y en una página de prueba; el gate completo de cada pantalla es el de la fase E.
- Convención de rutas: `public/css/lib/`, `public/js/lib/` (shell) y `public/css|js/plugins/<lib>/` (plugins). La suma sha384 de cada archivo copiado y su origen se guardan en `AGENTS.md` (sección Dependencias frontend).
- AdminLTE 4.10.1 no trae hojas para Tabulator ni Tom Select, y `adminlte-select2.css` deja de hacer falta.

## Librerías: versión objetivo

| Función                 | Hoy                                                       | Objetivo                                                                                                                      | Destino en `public/`                                      |
| ----------------------- | --------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------- |
| Shell                   | AdminLTE 3.2.0                                            | **AdminLTE 4.10.1** (`adminlte.min.css/js`)                                                                                   | `css/lib/adminlte/`, `js/lib/adminlte/`                   |
| Bootstrap JS            | 4.6.1 (CSS 5.0.2)                                         | **5.3.8** `bootstrap.bundle.min.js` (incluye Popper)                                                                          | `js/lib/bootstrap/`                                       |
| Scroll del menú         | —                                                         | **OverlayScrollbars 2.16.0** (CSS+JS; la versión que usa AdminLTE 4.10.1; opcional, se conserva por ser el scroll de su menú) | `css/lib/overlayscrollbars/`, `js/lib/overlayscrollbars/` |
| Tablas                  | DataTables 1.11.4 + Buttons + Responsive (jQuery)         | **Tabulator 6.6.1** (`tabulator.min.js` + `tabulator_bootstrap5.min.css`)                                                     | `js/plugins/tabulator/`, `css/plugins/tabulator/`         |
| Exportación Excel       | JSZip 3.7.1                                               | **SheetJS 0.20.3**, solo desde `cdn.sheetjs.com` (el paquete de npm, 0.18.5, tiene dos CVE)                                   | `js/plugins/utils/`                                       |
| Exportación PDF cliente | pdfmake 0.2.4                                             | **jsPDF 4.2.1** (primera sin los 13 avisos de 2026) + **jspdf-autotable 5.0.9**                                               | `js/plugins/utils/`                                       |
| Selectores              | Select2 4.0.13 (jQuery)                                   | **Tom Select 2.6.2** `tom-select.complete.min.js` + `tom-select.bootstrap5.min.css` (Apache-2.0)                              | `js/plugins/tom-select/`, `css/plugins/tom-select/`       |
| Fechas                  | `<input>` + botón                                         | **`<input type="date">` nativo** (C9); sin Flatpickr                                                                          | —                                                         |
| Validación              | jQuery Validate 1.19.3                                    | **Validación nativa** (Constraint Validation API) + helper propio                                                             | `js/core/form-validator.js`                               |
| Iconos                  | Font Awesome 6.5.2 (a recontar en T21; ~303 tokens `fa-`) | **Bootstrap Icons 1.13.1** (CSS + `fonts/*.woff2`/`.woff`)                                                                    | `css/lib/bootstrap-icons/`                                |
| Gráficos                | Chart.js 2.9.4                                            | **Chart.js 4.5.1** `chart.umd.min.js`                                                                                         | `js/plugins/chart/`                                       |
| Diálogos                | SweetAlert2 11.12.1                                       | **SweetAlert2 11.26.25** (C10)                                                                                                | `js/plugins/sweetalert2/`, `css/plugins/sweetalert2/`     |
| jQuery                  | 3.6.0                                                     | **Eliminado** (NFR-7)                                                                                                         | —                                                         |
| moment, iCheck          | sin uso / login                                           | **Eliminados**                                                                                                                | —                                                         |

**Criterio de versiones:** las librerías que AdminLTE 4.10.1 usa en sus páginas (Bootstrap 5.3.8, Bootstrap Icons 1.13.1, OverlayScrollbars 2.16.0, Tabulator 6.6.1, Tom Select 2.6.2, Chart.js 4.5.1) se fijan en esas versiones, que son las que el proyecto probó en conjunto. Las que AdminLTE no usa (SweetAlert2, jsPDF, jspdf-autotable, SheetJS) van en la última estable verificada. Subir una de las primeras se decide aparte, con prueba de la pantalla afectada.

Archivos que sobran al terminar (T-limpieza): jQuery; Bootstrap CSS/JS 5.0.2; iCheck (2 copias); todo DataTables (`datatables.min.*` 2.0.8, `jquery.dataTables.min.js`, `*.bootstrap4.*`, Buttons, Responsive); Select2 y `select2-bootstrap4`; jQuery Validate y `additional-methods`; JSZip, pdfmake y `vfs_fonts`; moment; Font Awesome con `webfonts/` y `css/core/webfonts.css`; Chart.js 2; SweetAlert2 11.12.1; AdminLTE 3.

## Estructura de módulos

- `views/layouts/` → `header.php`, `sidebar.php`, `footer.php`, `messages.php`, `auth.php`, `error.php` y `views/errors/*` (FR-1 a FR-10, FR-31)
- `views/{users,roles,permissions,audit-log,dashboard,auth}/**` → markup Bootstrap 5 / AdminLTE 4, iconos Bootstrap Icons y datos de tabla en JSON dentro de un atributo `data-*` del contenedor (FR-11 a FR-19, FR-27, FR-34, FR-35, NFR-8)
- `public/js/core/` → helpers nativos nuevos y reescritos (FR-14, FR-25 a FR-30, FR-32):
    - `http.js` (nuevo): `fetch` con `X-Requested-With: XMLHttpRequest`, token CSRF vigente y `URLSearchParams`; sustituye `$.ajax`/`$.post`.
    - `form-validator.js` (nuevo, reemplaza `common-validate.js`): `novalidate` + `setCustomValidity` + `.was-validated`, unicidad con `fetch`, `AbortController` y debounce, y envío AJAX.
    - `table-utils.js` (reemplaza `common-datatable.js`): configuración de Tabulator, formatters con `escapeHtml`, búsqueda global, columnas visibles, exportación.
    - `ui-components.js`: Tom Select, tooltips y utilidades de modal con `bootstrap.Modal`.
    - `common-utils.js`, `sweetalert-utils.js`: sin jQuery.
- `public/js/modules/*` → un archivo por pantalla, sin jQuery (FR-11 a FR-19)
- `public/js/modules/profile/theme-toggle.js` → interruptor de tema (FR-20, FR-22)
- `public/css/core/*`, `public/css/modules/**` → estilos propios sobre `[data-bs-theme]` (FR-23)
- `app/Core/AssetRegistry.php` → mapas de assets (claves nuevas: `tabulator`, `tom-select`, `export`, `chart`; los helpers propios los añaden T22, T24 y T26a; `scripts/check-asset-paths.php` valida las rutas). Única excepción de PHP: solo rutas y orden (FR-33, NFR-2)

## Modelo de datos

Sin cambios.

## Algoritmos y lógica no trivial

- **Tema (FR-20 a FR-22):** `<html data-lte-color-mode="off">` desactiva el módulo de color de AdminLTE; el interruptor propio (`localStorage['theme']`, `light|dark`, fallback `prefers-color-scheme`) escribe `data-bs-theme` en `<html>`; el anti-FOUC inline en `<head>` lo aplica antes del primer pintado. `html.dark-mode` desaparece del JS en T18 y de las hojas en T44; `index-dashboard.js` (MutationObserver sobre la clase `dark-mode`, líneas 30 y 107) pasa a observar `data-bs-theme` en T41. AdminLTE 4, Tabulator (`tabulator_bootstrap5`) y Tom Select (variables `--bs-*`) siguen `data-bs-theme`; solo el borde del desplegable de Tom Select queda fijo y se corrige con una regla propia.
- **SweetAlert2 (FR-23):** cada llamada pasa `theme: 'dark'|'light'` (o `bootstrap-5-dark|light`) leído de `data-bs-theme`. Nunca `'auto'` ni `'bootstrap-5'` a secas: siguen al sistema, no al interruptor.
- **Tablas (FR-11, NFR-8, C7):** Tabulator no se inicializa sobre un `<table>` ya renderizado (pierde `data-*` de la fila y exporta el HTML crudo). La vista imprime los datos como JSON en un atributo `data-*` del contenedor, incluidas las banderas de permiso por fila; los badges y botones se dibujan en `formatter` con `escapeHtml`. Sin endpoint nuevo.
    - Búsqueda global: un `<input>` con `setFilter`. Paginación, orden y `layout` desde las opciones de Tabulator.
    - Columnas visibles: dropdown de Bootstrap con `column.toggle()` (Tabulator no trae colvis).
    - Tooltips tras redibujar: una sola instancia delegada `new bootstrap.Tooltip(document.body, {selector: '[data-bs-toggle="tooltip"]'})`.
    - Idioma: textos de Tabulator en español vía `langs`.
- **Exportación (FR-11, C12):** Copiar, CSV e Imprimir son nativos de Tabulator. Excel exige `XLSX` global (SheetJS) y PDF exige `jsPDF` + `autoTable`. Si se pasa `title`, Tabulator pisa `didDrawPage`: el pie de página se hace en un `documentProcessing`. Verificar con una celda con marcado HTML (que no salga como `<b>…</b>`).
- **Selectores (FR-25):** Tom Select con `maxOptions: null` (por defecto 50 oculta opciones en selects de usuarios/actores), `allowEmptyOption` donde haya «All», sin `dropdownParent`. Cada `<select>` lleva `<label for>` (Tom Select reasigna `for` y pone `aria-labelledby`). `minimumResultsForSearch` no tiene equivalente directo: se usa `controlInput` según el caso.
- **Validación (FR-14, FR-27, C11):** `form-validator.js` valida con la API nativa y marca `.is-invalid` + `.invalid-feedback` por campo. La regla `remote` pasa a `fetch` con debounce y `AbortController` contra `/users/check-email` y `/users/check-document` (las respuestas `true` o mensaje no cambian). La cabecera `X-Requested-With: XMLHttpRequest` es obligatoria: `csrfCheck` y `ErrorHandler` dependen de ella. Los campos que valida el helper llevan `disable-adminlte-validations`, porque `initAccessibility` de AdminLTE crea `<id>-error` en el evento `invalid` y nunca quita `.is-invalid`.
- **Modales, pestañas, tooltips, AJAX (FR-32):** `bootstrap.Modal.getOrCreateInstance`, `bootstrap.Tab`, `bootstrap.Tooltip`, `fetch` + `URLSearchParams` y delegación con `closest`. `ajaxRequest()` es código muerto y se elimina. Sin jQuery cargado, `$().modal` no existe: no queda ningún uso.
- **Fechas (FR-36, C9):** `<input type="date">` nativo en el filtro de auditoría; sin Flatpickr.
- **Iconos (FR-35, C8):** mapa de los 67 iconos distintos de Font Awesome a Bootstrap Icons (clase `bi bi-…`), verificado contra el CSS 1.13.1. `fa-spin` pasa a `spinner-border`; `fa-stack` no tiene equivalente y se rediseña.
- **Orden de carga:** Bootstrap bundle → AdminLTE → OverlayScrollbars → SweetAlert2 → plugins (Tabulator, Tom Select, Chart.js; SheetJS y jsPDF solo donde hay exportación) → helpers propios (`http`, `form-validator`, `table-utils`, `ui-components`) → módulo de la pantalla.
- **CSRF (FR-30):** la rotación del token no cambia; `http.js` reutiliza el token devuelto tras cada envío. Crear y editar un rol desde el modal devuelven `csrf_token` para encadenar la sincronización de permisos sin recargar. Los rechazos responden «Your session has expired. Reload the page and try again.» (AJAX, 403) o «Invalid security token. Please try again.» (envío normal) (CHG-3).
- **Reglas de roles y acciones por fila (FR-15, FR-37, FR-38, FR-39, CHG-3):** `Role::syncPermissions` borra solo las asignaciones de permisos activos y reinserta con `INSERT IGNORE`, de modo que las asignaciones inactivas se conservan (el formulario solo lista activos). `RoleController::syncPermissions` calcula los permisos activos que se retiran y exige poseerlos con `Auth::canGrantPermissionIds()`. `update`, `toggleStatus` y `syncPermissions` responden «Role not found.» si el rol no existe. `User::getAll()` expone `role_is_system` y los controladores de usuarios y roles envían `manage` por fila (roles: administrador o rol no de sistema); el formatter solo dibuja los botones permitidos y el servidor sigue rechazando.
- **Scripts inline de los layouts:** `baseUrl` y `csrfToken` pasan de `<script>` inline a `<meta name="base-url">` / `<meta name="csrf-token">` leídos por `http.js`; así los layouts quedan listos para una CSP (solo el anti-FOUC sigue inline, con nonce si se añade CSP). Alcance: `header.php`, `auth.php`, `error.php` y `messages.php` (T14, T17, T19, T20).
- **Clases armadas en JS:** el barrido de clases de Bootstrap 4 incluye las que el JS genera (`badge-*`, `custom-file-*`, `form-group`, `mr-*`/`ml-*`, `fa-*`), no solo las vistas y el CSS.
- **Peso de la auditoría:** `AuditLogController` no pone límite de filas y los datos viajan en el HTML. Se acepta como riesgo del proyecto (spec § Riesgos aceptados); T40 mide el tamaño del HTML y lo anota en `AGENTS.md`.

## Decisiones técnicas

- **Regla de adopción (CHG-2):** lo que AdminLTE 4 cubre se reemplaza; lo propio que no cubre se conserva y se adapta. Excepciones justificadas: SweetAlert2 (C10), interruptor de tema propio sobre `data-bs-theme`, `ToastUtils`/`AlertUtils`, fechas nativas (C9) y exportación con SheetJS/jsPDF (C12). Cualquier otra se añade aquí con su motivo. (FR-33)
- **Stack sin jQuery del preview de AdminLTE 4:** Tabulator, Tom Select, Bootstrap Icons, validación nativa. Alcance: solo los componentes que el proyecto ya usa. DataTables 3.1.5 (ya sin jQuery) se reevaluó el 2026-11-05 y se descartó: el objetivo es adoptar los componentes tal como AdminLTE 4 los documenta, además de la práctica; AdminLTE 3.2.0 sigue en otros proyectos como respaldo. (C2, C5, C7, C8, C11)
- **Datos de las tablas desde la vista, no desde un endpoint nuevo:** respeta «backend sin cambios» y la regla de AGENTS.md de pasar datos a JS por `data-*`. (C7)
- **Exportación con paridad** (Excel + PDF) y reducción documentada si la prueba falla. (C12)
- **SweetAlert2 se conserva:** AdminLTE no lo reemplaza y sustituirlo obliga a reescribir `confirm`, `loading`, `welcome` y 24 llamadas directas a `Swal`. (C10)
- **Fechas nativas (en lugar de Flatpickr, que AdminLTE 4 sí documenta):** Flatpickr deja el campo `readonly` sin `allowInput` (sale de la validación), `altInput` desconecta el label y su tema oscuro es global. (C9)
- **Composer y npm solo en desarrollo, sin CDN en tiempo de ejecución:** siguen la documentación oficial de AdminLTE 4, dan versiones fijas con lockfile y Dependabot. El script de copia mantiene `public/` estático y el despliegue sin Node. (C1, NFR-1, NFR-3, NFR-6)

## Contrato de interfaz

Rutas y endpoints sin cambios; las respuestas de crear y editar rol añaden `csrf_token` (CHG-3). Los endpoints AJAX siguen devolviendo `{success: bool, message?: string}` con `$this->jsonResponse()`; rechazos de negocio en HTTP 200 + `success:false`; 403 solo para CSRF/middleware. Las peticiones `fetch` llevan `X-Requested-With: XMLHttpRequest`.

## Estrategia de verificación

- **Gate de cada tarea de frontend (NFR-5):** checklist manual en navegador (Playwright MCP o manual) con captura o registro de consola como evidencia, de la pantalla tocada y en ambos temas. `composer check` no ejercita el frontend (el CI ignora `public/`): se usa como regresión de PHP en la línea base y al cierre.
- **Axe-core** por pantalla en claro y oscuro, con transiciones desactivadas y el tema activado con el control real tras limpiar `localStorage` (FR-23).
- **Barridos de cierre:** cero resultados de `data-toggle|data-dismiss|data-target|data-parent|data-card-widget|data-widget` (FR-32); cero `jQuery|\$\(|\$\.` en `views/` y `public/js/core|modules` (NFR-7); cero `fa-` (FR-35); ningún archivo de la lista «sobran» en `public/`.
- **Exportaciones:** Excel, PDF, CSV, imprimir y copiar probados con una celda con marcado HTML y acentos (NFR-8); PDF de auditoría del servidor sin regresión (FR-19).

## Riesgos

- **Tabulator frente a DataTables 3:** se mantiene Tabulator por decisión de producto (CHG-1). Coste asumido: datos en JSON, formatters, colvis propio y exportación con SheetJS/jsPDF.
- **Tabulator reescribe cada tabla** (datos JSON, formatters, filtros): es el trabajo más grande → una tabla por tarea, de la más simple (roles) a la más compleja (auditoría).
- **Desfase npm↔`public/`:** mitigado por el check de CI de NFR-9.
- **SheetJS solo en `cdn.sheetjs.com`:** entra a `package.json` como tarball con URL fija; Dependabot no lo vigilará; se anota la versión y el origen en `AGENTS.md` y se revisa a mano.
- **jsPDF publica avisos con frecuencia:** versión fijada en 4.2.1 y revisión en cada release del proyecto.
- **Versiones alineadas con AdminLTE 4.10.1:** Tabulator 6.6.1, Chart.js 4.5.1, OverlayScrollbars 2.16.0 y Bootstrap Icons 1.13.1 quedan una versión menor por detrás de la última; Dependabot propondrá los bumps y se prueban uno a uno.
- **Chart.js 4 activa opciones ya escritas con sintaxis v3+** y cambia el aspecto del dashboard → revisión visual en ambos temas.
- **Tom Select:** `maxOptions`, etiquetas accesibles y borde del desplegable en oscuro → pruebas específicas.
- **Validación nativa:** pérdida de las reglas `remote`, `equalTo` y contraseña de Validate → reescritas en el helper y probadas con casos límite.
- **`initAccessibility` de AdminLTE** inyecta skip links, `id="main"`, `role="table"` y `<id>-error` → clase `disable-adminlte-validations`; comprobar colisiones.
- **fetch sin `X-Requested-With`** hace que `csrfCheck` y `ErrorHandler` respondan HTML en vez de JSON (hoy `profile-user.js` ya lo hace sin la cabecera) → centralizar en `http.js`.
- **Clases de AdminLTE 3 sin equivalente** (`error-page`, `user-panel`, `elevation-*`, `btn-default`, `img-circle`, `navbar-light`) → reescritura guiada por el inventario.
- **Regresión de negocio** → checklist de C3 por módulo.

## FR cubiertos por cada parte

| Parte                                                    | FR / NFR                                  |
| -------------------------------------------------------- | ----------------------------------------- |
| `views/layouts/header.php`                               | FR-1, FR-4, FR-5, FR-21                   |
| `views/layouts/sidebar.php`                              | FR-2, FR-3, FR-4, FR-6                    |
| `views/layouts/footer.php`                               | FR-1, FR-7                                |
| `views/layouts/messages.php`                             | FR-9                                      |
| `views/layouts/auth.php`                                 | FR-8, FR-21                               |
| `views/layouts/error.php`, `views/errors/*`              | FR-31                                     |
| `views/users/*`, `views/roles/*`, `views/permissions/*`  | FR-11 a FR-16, FR-27, FR-32, FR-35, NFR-8 |
| `views/audit-log/*`                                      | FR-18, FR-19, FR-36                       |
| `views/dashboard/index.php`                              | FR-17                                     |
| `views/auth/*`                                           | FR-8, FR-10, FR-27, FR-34                 |
| `public/js/core/http.js`                                 | FR-28, FR-29, FR-30, NFR-7                |
| `public/js/core/form-validator.js`                       | FR-14, FR-27, NFR-7                       |
| `public/js/core/table-utils.js`                          | FR-11, FR-26, NFR-8                       |
| `public/js/core/ui-components.js`                        | FR-25, FR-26, FR-32                       |
| `public/js/core/common-utils.js`                         | FR-30, FR-32                              |
| `public/js/core/sweetalert-utils.js`                     | FR-10, FR-16, FR-23, FR-28                |
| `public/js/modules/{users,roles,permissions}/*`          | FR-11 a FR-16, FR-28, FR-29               |
| `public/js/modules/audit-log/index-audit.js`             | FR-18, FR-19, FR-36                       |
| `public/js/modules/dashboard/index-dashboard.js`         | FR-17, FR-23                              |
| `public/js/modules/auth/*`                               | FR-8, FR-27                               |
| `public/js/modules/profile/theme-toggle.js`              | FR-20, FR-22                              |
| `public/css/core/*`, `public/css/modules/**`             | FR-20, FR-23                              |
| `app/Core/AssetRegistry.php` y `public/**/{lib,plugins}` | FR-33, NFR-1, NFR-6, NFR-7                |
| `.github/workflows/tests.yml`, `.gitattributes`          | NFR-9, NFR-1                              |
| `README.md`, `CONTRIBUTING.md`, `AGENTS.md`              | NFR-6 (documentación)                     |
| `Role::syncPermissions`, `RoleController`, `User::getAll`, `Controller::csrfCheck` | FR-15, FR-30, FR-37, FR-38, FR-39 (CHG-3) |
| Todo el trabajo                                          | FR-24, NFR-2, NFR-4, NFR-5                |
