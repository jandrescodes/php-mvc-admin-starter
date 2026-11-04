# Investigación AdminLTE 4: hechos verificados (2026-11-04)

Fuentes descargadas en el scratchpad:

- `al4-gh/AdminLTE-4.10.1/`: tarball del tag de GitHub `v4.10.1` (`https://github.com/ColorlibHQ/AdminLTE/archive/refs/tags/v4.10.1.tar.gz`)
- `al4-npm/package/`: tarball npm `admin-lte@4.10.0`
- `bs-mig/`: guías de migración oficiales de Bootstrap (`twbs/bootstrap@v5.0.2 site/content/docs/5.0/migration.md` y `@v5.3.8 site/src/content/docs/migration.mdx`)
- `bs538/`: `bootstrap@5.3.8` (`bootstrap.bundle.js` y `bootstrap.css`, de jsDelivr)
- `dt3/`: `datatables.net@3.1.5/js/dataTables.js`

Las rutas `src/...` y `dist/...` de abajo son relativas a `al4-gh/AdminLTE-4.10.1/`.

---

## 1. Versiones, contenido de `dist/` y dependencias

### Última estable: depende del canal (verificado)

| Canal                              | Versión                     | Fecha      | Fuente                                                             |
| ---------------------------------- | --------------------------- | ---------- | ------------------------------------------------------------------ |
| GitHub release/tag                 | **v4.10.1** (no prerelease) | 2026-10-08 | `api.github.com/repos/ColorlibHQ/AdminLTE/releases`                |
| Packagist `almasaeed2010/adminlte` | **v4.10.1**                 | 2026-10-08 | `repo.packagist.org/p2/almasaeed2010/adminlte.json`                |
| npm `admin-lte` (`latest`)         | **4.10.0**                  | 2026-10-01 | `npm view admin-lte dist-tags` → `{latest: 4.10.0, legacy: 4.8.0}` |

- **4.10.1 NO está en npm.** Las notas del release lo dicen: "Npm publication is handled separately by the maintainer". Por eso `https://cdn.jsdelivr.net/npm/admin-lte@4.10.1/...` da **404** (comprobado), aunque los propios docs de AdminLTE (`src/html/components/docs/deployment.mdx`) muestran esa URL. Sí responden 200: `cdn.jsdelivr.net/npm/admin-lte@4.10.0/...` y `cdn.jsdelivr.net/gh/ColorlibHQ/AdminLTE@v4.10.1/...`.
- El release v4.10.1 de GitHub trae como assets `admin-lte-4.10.1.tgz`, `admin-lte-v4.10.1.zip` y `SHA256SUMS`.
- Cambios de 4.10.1: toolchain, demos y correcciones de responsive/a11y/contraste. "Public AdminLTE component APIs … remain unchanged" (cuerpo del release).
- El inventario del repo (`docs/adminlte3-class-inventory.md`) ya comparó contra `v4.10.1` de GitHub, lo cual es correcto.
- **`composer.json` del paquete** (`composer.json`): no declara dependencias y su `description` sigue diciendo "based on Bootstrap 4" (texto obsoleto). Packagist sirve el zipball del tag, sin `export-ignore` en `.gitattributes`, así que `composer require almasaeed2010/adminlte:^4` trae todo el repo: `dist/` compilado, más `src/`, el HTML de las demos y los tests. El README (`README.md:82-85`) documenta esta vía oficialmente.

### Contenido real de `dist/` (4.10.1; npm 4.10.0 tiene los mismos nombres de archivo)

- `dist/css/`: `adminlte(.rtl)(.min).css`, `adminlte-colors*.css`, `adminlte-colors-v3*.css`, `adminlte-select2*.css`, `adminlte-docs*.css` (este último solo para la documentación; "never ship it", según `deployment.mdx`), y sus `.map`.
- `dist/js/`: `adminlte.js`, `adminlte.min.js` (UMD, 27.8 KB), `adminlte.esm(.min).js` y `types/*.d.ts`.
- `dist/assets/img/`.
- **El tag de GitHub** además incluye en `dist/` el sitio de demos compilado (`starter.html`, `index*.html`, `examples/`, `pages/`, `widgets/`, `docs/`…). El tarball de npm solo trae `css/`, `js/` y `assets/`.
- **`adminlte.css` YA INCLUYE el CSS de Bootstrap 5.3** compilado: `src/scss/adminlte.scss` importa `bootstrap/scss/*`, y `dist/css/adminlte.css` arranca con `:root,[data-bs-theme=light]{--bs-blue…}`. No hay que cargar `bootstrap.min.css` aparte. Lo confirma `deployment.mdx`: "AdminLTE — includes Bootstrap CSS via its imports".
- **`adminlte.js` NO incluye el JS de Bootstrap.** `package.json` declara `peerDependencies: { bootstrap: "^5.3.8" }` y ninguna `dependency`.
- **No hay build con jQuery.** `grep -i jquery dist/js/adminlte.js` da 0 coincidencias y no existe ningún shim de `data-widget` / `data-card-widget`. El FAQ (`src/html/pages/docs/faq.astro:37`) lo confirma: "there are no `$.fn.xxx()` plugin shims". jQuery "coexist fine" si la app lo carga.

### Dependencias que hay que cargar aparte (las del starter oficial, `dist/starter.html`)

| Dependencia       | Versión en el starter / docs                                                                                                                    | Última en npm (hoy) | ¿Obligatoria?                                                     |
| ----------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- | ------------------- | ----------------------------------------------------------------- |
| Bootstrap JS      | `bootstrap@5.3.8/dist/js/bootstrap.min.js`                                                                                                      | 5.3.8               | Sí (peer)                                                         |
| Popper            | `@popperjs/core@2.11.8/dist/umd/popper.min.js`                                                                                                  | 2.11.8              | Sí, para dropdowns, tooltips y popovers                           |
| OverlayScrollbars | `overlayscrollbars@2.16.0` (CSS + `browser/overlayscrollbars.browser.es6.min.js`) + un script inline de inicialización sobre `.sidebar-wrapper` | 2.17.0              | Opcional ("optional, for the sidebar scroller", `deployment.mdx`) |
| Bootstrap Icons   | `bootstrap-icons@1.13.1/font/bootstrap-icons.min.css`                                                                                           | 1.13.2              | Opcional (solo las demos usan `bi bi-*`)                          |
| Fuente            | `@fontsource/source-sans-3@5.3.0`                                                                                                               | —                   | Opcional                                                          |

- En lugar de `popper.min.js` + `bootstrap.min.js` se puede cargar **`bootstrap.bundle.min.js` 5.3.8**, que trae Popper embebido (verificado: `bs538/bootstrap.bundle.js` contiene `createPopper`). Esto lo verifiqué yo; el starter usa los dos archivos por separado.
- Orden oficial (`deployment.mdx:97-120`): CSS `bootstrap-icons` → `overlayscrollbars` → `adminlte.min.css`. Al final de `<body>`: `popper` → `bootstrap` → `overlayscrollbars` → `adminlte.min.js` ("must load AFTER Bootstrap").
- **Contradicción oficial:** la tabla de reemplazos de `migration.mdx` dice "jQuery Overlay Scrollbars → Native `scrollbar-*` CSS", pero el starter y `deployment.mdx` siguen cargando OverlayScrollbars 2.16.0 como opcional. Conclusión: no hace falta para que el layout funcione.

### Corrección a lo que dicen el spec y el plan (`specs/001-adminlte-migration/spec.md`)

- "Dependencias que vienen empaquetadas con AdminLTE 4 (no se instalan por separado): Bootstrap 5.3.8 (CSS y JS), Popper, OverlayScrollbars, Bootstrap Icons" es **FALSO** salvo para el CSS de Bootstrap. El JS de Bootstrap, Popper, OverlayScrollbars y Bootstrap Icons se cargan aparte.
- La tabla de versiones del spec está desactualizada. Valores de `npm view` hoy:
    - jQuery **4.0.0** (2026-01-18). Los docs de AdminLTE usan `jquery@4.0.0` en su ejemplo de Select2 (`integrations.mdx:137`).
    - Select2 **4.1.0 final** (2026-05-26).
    - DataTables **3.1.5** (no 2.x).
    - Chart.js 4.6.1.
    - jquery-validation **1.22.1** (peer `jquery ^1.7 || ^2.0 || ^3.1 || ^4.0`).
    - Font Awesome Free **7.3.1** (no 6.5.2).
    - SweetAlert2 11.26.25.
- **DataTables 3.x ya NO requiere jQuery.** `dt3/dataTables.js` usa un factory `(window, document)` y expone `window.DataTable`. Si encuentra `jQuery` (`DataTable.use(jQuery)` o `window.jQuery`), registra además `$.fn.DataTable` y `$.fn.dataTable` (función `jQuerySetup()`, líneas ~623-647). `npm view datatables.net@3.1.5 dependencies` está vacío; la 2.3.x tenía `jquery >=1.7`. Esto invalida la premisa C2 del spec ("DataTables también requiere jQuery") para la 3.x. Select2 y jQuery Validate sí siguen requiriendo jQuery.

---

## 2. Estructura del layout v4 y JS

### Markup del layout (de `dist/starter.html` y `src/html/components/docs/components/main-sidebar.mdx`)

```html
<html lang="es">
    <!-- data-bs-theme lo escribe ColorMode / el snippet anti-FOUC -->
    <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
        <div class="app-wrapper">
            <nav class="app-header navbar navbar-expand bg-body">
                <div class="container-fluid">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a
                                class="nav-link"
                                data-lte-toggle="sidebar"
                                href="#"
                                role="button"
                                aria-label="Toggle sidebar"
                                ><i class="bi bi-list"></i
                            ></a>
                        </li>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a
                                class="nav-link"
                                href="#"
                                data-lte-toggle="fullscreen"
                                >…</a
                            >
                        </li>
                        <li class="nav-item dropdown user-menu">
                            <a
                                href="#"
                                class="nav-link dropdown-toggle"
                                data-bs-toggle="dropdown"
                                ><img
                                    class="user-image rounded-circle shadow"
                                    …
                            /></a>
                            <ul
                                class="dropdown-menu dropdown-menu-lg dropdown-menu-end"
                            >
                                <li class="user-header text-bg-primary">…</li>
                                <li class="user-footer">
                                    …
                                    <a class="btn btn-outline-danger float-end"
                                        >Sign out</a
                                    >
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
            <aside
                class="app-sidebar bg-body-secondary shadow"
                data-bs-theme="dark"
            >
                <div class="sidebar-brand">
                    <a class="brand-link"
                        ><img class="brand-image opacity-75 shadow" /><span
                            class="brand-text fw-light"
                            >…</span
                        ></a
                    >
                </div>
                <div class="sidebar-wrapper">
                    <nav class="mt-2">
                        <ul
                            class="nav sidebar-menu flex-column"
                            data-lte-toggle="treeview"
                            role="menu"
                            data-accordion="false"
                        >
                            <li class="nav-header">…</li>
                            <li class="nav-item">
                                <a class="nav-link active"
                                    ><i class="nav-icon bi bi-…"></i>
                                    <p>Item</p></a
                                >
                            </li>
                            <li class="nav-item menu-open">
                                <a class="nav-link"
                                    ><i class="nav-icon …"></i>
                                    <p>
                                        Grupo
                                        <i
                                            class="nav-arrow bi bi-chevron-right"
                                        ></i></p
                                ></a>
                                <ul class="nav nav-treeview">
                                    …
                                </ul>
                            </li>
                        </ul>
                    </nav>
                </div>
            </aside>
            <main class="app-main">
                <div class="app-content-header">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-6">
                                <h1 class="mb-0 fs-3">Título</h1>
                            </div>
                            <div class="col-sm-6">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb float-sm-end">
                                        …
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="app-content">
                    <div class="container-fluid">…</div>
                </div>
            </main>
            <footer class="app-footer">
                <div class="float-end d-none d-sm-inline">…</div>
                <strong>…</strong>
            </footer>
        </div>
    </body>
</html>
```

- Clases de `<body>` que entiende el JS y el CSS: `layout-fixed`, `sidebar-expand-{bp}`, `sidebar-mini`, `sidebar-collapse`, `sidebar-open`, `fixed-header`, `fixed-footer`, `hold-transition`, `app-loaded`. Todas existen en `dist/css/adminlte.css` y en `src/ts/push-menu.ts:32-45`.
- Para imprimir: `data-lte-print="plain"` o `"app"` (`migration.mdx`, paso 8).

### JS de AdminLTE 4 (`src/ts/adminlte.ts`)

- Exporta `Layout`, `CardWidget`, `Treeview`, `DirectChat`, `FullScreen`, `PushMenu`, `ColorMode`, `SidebarSearch`, `initAccessibility`, `initialize` y `teardown`. En el build UMD quedan bajo el global `adminlte`. El nombre del global sale del factory UMD y no lo comprobé ejecutándolo (**NO VERIFICADO en runtime**).
- **Inicialización automática** con data-API: cada módulo registra un `document.addEventListener('click', …)` delegado más un `onDOMContentLoaded` (`util/index.ts`). No hay que llamar nada. `initialize()` solo hace falta si el DOM se renderiza después de `DOMContentLoaded`.

| Módulo        | Selector / atributo                                                                                                                                                                                | Clases y eventos                                                                                                                                                                                                                  |
| ------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------ | -------- | --------------------------------------------------------- |
| PushMenu      | `[data-lte-toggle="sidebar"]`; config en `.app-sidebar`: `data-sidebar-breakpoint` (por defecto 991.98) y `data-enable-persistence` (por defecto **false**, BREAKING en 4.0.0, `CHANGELOG.md:407`) | `sidebar-collapse` / `sidebar-open` en `body`; persiste en `localStorage['lte.sidebar.state']` solo si está activada; inyecta `.sidebar-overlay` dentro de `.app-wrapper`; eventos `open/collapse/opened/collapsed.lte.push-menu` |
| Treeview      | `[data-lte-toggle="treeview"]` en el `<ul>` padre; opciones `accordion` (true) y `animationSpeed` (300)                                                                                            | `menu-open` en `.nav-item`, submenú `.nav-treeview`, flecha `.nav-arrow`                                                                                                                                                          |
| CardWidget    | `data-lte-toggle="card-collapse"` / `"card-remove"` / `"card-maximize"` en `.btn-tool`                                                                                                             | `collapsed-card`, `maximized-card`, `was-collapsed`; iconos `data-lte-icon="expand                                                                                                                                                | collapse                             | maximize | minimize"`(el CSS decide cuál se ve);`animationSpeed` 500 |
| FullScreen    | `[data-lte-toggle="fullscreen"]` + iconos `data-lte-icon="maximize                                                                                                                                 | minimize"`                                                                                                                                                                                                                        | `maximized/minimized.lte.fullscreen` |
| DirectChat    | `[data-lte-toggle="chat-pane"]` dentro de `.direct-chat`                                                                                                                                           | `direct-chat-contacts-open`                                                                                                                                                                                                       |
| SidebarSearch | `[data-lte-toggle="sidebar-search"]`, `data-lte-target`, `[data-lte-search-empty]`                                                                                                                 | —                                                                                                                                                                                                                                 |
| ColorMode     | `[data-bs-theme-value]`, `[data-lte-theme-icon]`                                                                                                                                                   | ver §4                                                                                                                                                                                                                            |
| Layout        | automático sobre `body`                                                                                                                                                                            | `hold-transition` al redimensionar, `app-loaded` a los 400 ms                                                                                                                                                                     |

- **Efectos colaterales de `initAccessibility`**, que se ejecuta siempre (`src/ts/adminlte.ts`, `src/ts/accessibility.ts`):
    - inyecta skip links (`.skip-links`, `href="#main"`) y un live region `#live-region`;
    - pone `role="main"` e `id="main"` en `.app-main`;
    - pone `role="table"` y `scope` en los `<th>` de toda tabla;
    - escucha el evento nativo `invalid` de los inputs y crea un `div.invalid-feedback` con id `${id||name}-error`, además de añadir `is-invalid` y `aria-describedby`.
    - **Riesgo para este proyecto:** jQuery Validate usa ese mismo patrón de id (`<name>-error`). Se puede desactivar campo por campo con la clase `disable-adminlte-validations`. jQuery Validate pone `novalidate` en el form, así que el `invalid` nativo no debería dispararse al enviar. **NO VERIFICADO en navegador.**

---

## 3. Equivalencias v3→v4 para lo que usa este proyecto

### AdminLTE (oficial: `src/html/components/docs/migration.mdx` "Class renames"; README "Breaking changes from v3")

| AdminLTE 3                                                                           | AdminLTE 4                                                                                                | Nota                                                                                                                                                                                                                                     |
| ------------------------------------------------------------------------------------ | --------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------- | ----------- | --------------- | --- |
| `.wrapper`                                                                           | `.app-wrapper`                                                                                            |                                                                                                                                                                                                                                          |
| `.main-header` (`navbar-white navbar-light`)                                         | `.app-header navbar navbar-expand bg-body`                                                                | `navbar-white` no existe en v4. **`navbar-light` no existe en BS 5.3.8** (0 coincidencias en `bs538/bootstrap.css` y en `adminlte.css`): el Grupo B del inventario estaba medido contra BS 5.0.2 y deja de cubrirse                      |
| `.main-sidebar sidebar-light-primary elevation-*`                                    | `.app-sidebar bg-body-secondary shadow` (+ `data-bs-theme="dark"` opcional)                               | los skins `sidebar-{light,dark}-*` no existen; hay skins opt-in en `adminlte-colors.css` (`text-bg-navy`…)                                                                                                                               |
| `.brand-link` dentro de `.main-sidebar`                                              | `.sidebar-brand > .brand-link`                                                                            | `brand-image` y `brand-text` siguen                                                                                                                                                                                                      |
| `.sidebar`                                                                           | `.sidebar-wrapper`                                                                                        | sale del starter y del CSS (44 coincidencias)                                                                                                                                                                                            |
| `.nav-pills.nav-sidebar`                                                             | `.nav.sidebar-menu.flex-column`                                                                           |                                                                                                                                                                                                                                          |
| `i.right fas fa-angle-left`                                                          | `i.nav-arrow bi bi-chevron-right`                                                                         |                                                                                                                                                                                                                                          |
| `.content-wrapper`                                                                   | **`.app-main`** (`<main>`)                                                                                | **el inventario dice `app-content`; según la guía oficial es `.app-main`**                                                                                                                                                               |
| `.content-header`                                                                    | `.app-content-header`                                                                                     |                                                                                                                                                                                                                                          |
| `.content`                                                                           | `.app-content`                                                                                            |                                                                                                                                                                                                                                          |
| `.main-footer`                                                                       | `.app-footer`                                                                                             |                                                                                                                                                                                                                                          |
| `data-widget="pushmenu"`                                                             | `data-lte-toggle="sidebar"`                                                                               |                                                                                                                                                                                                                                          |
| `data-widget="treeview"`                                                             | `data-lte-toggle="treeview"` (en el `<ul>` padre)                                                         |                                                                                                                                                                                                                                          |
| `data-card-widget="collapse                                                          | remove                                                                                                    | maximize"`                                                                                                                                                                                                                               | `data-lte-toggle="card-collapse | card-remove | card-maximize"` |     |
| `data-widget="fullscreen"`                                                           | `data-lte-toggle="fullscreen"`                                                                            |                                                                                                                                                                                                                                          |
| `data-widget="control-sidebar"`                                                      | eliminado (usar offcanvas de BS)                                                                          |                                                                                                                                                                                                                                          |
| `.dark-mode` en `<body>`                                                             | `data-bs-theme="dark"`                                                                                    |                                                                                                                                                                                                                                          |
| `.small-box > .inner + .icon > i + .small-box-footer`                                | `.small-box.text-bg-* > .inner + .small-box-icon + a.small-box-footer`                                    | `.small-box .icon` no existe en v4 (0 coincidencias). `.small-box-icon` es una `<svg>` en las demos; sobre un `<i>` de fuente de iconos aplica `font-size:70px`, pero **NO VERIFICADO** visualmente. Color: `text-bg-*` en vez de `bg-*` |
| `.info-box`                                                                          | igual: `.info-box > span.info-box-icon.text-bg-* + .info-box-content > .info-box-text + .info-box-number` |                                                                                                                                                                                                                                          |
| `.callout.callout-{info,success,warning,…}`                                          | igual                                                                                                     | existe en `adminlte.css`                                                                                                                                                                                                                 |
| `.card.card-{color}.card-outline`                                                    | igual                                                                                                     | color en borde superior de 3px; `card-tools` y `btn-tool` siguen; `card-outline-tabs` y `card-tabs` existen                                                                                                                              |
| `.direct-chat`                                                                       | existe                                                                                                    |                                                                                                                                                                                                                                          |
| `.login-page` / `.login-box` / `.login-logo` / `.login-card-body` / `.login-box-msg` | siguen existiendo; `body.login-page.bg-body-secondary > main.login-box` (`dist/examples/login.html`)      | iCheck se reemplaza por `.form-check` nativo; `d-grid` en vez de `btn-block`                                                                                                                                                             |
| `.register-page` / `.register-box` / `.lockscreen`                                   | existen                                                                                                   |                                                                                                                                                                                                                                          |
| `.error-page` / `.error-content`                                                     | **no existen** (0 coincidencias)                                                                          | `dist/pages/404.html` es una página standalone con utilidades de BS: `body.bg-body-tertiary > main.d-flex.align-items-center.min-vh-100 …` y `.display-1.fw-bold.text-primary`                                                           |
| `.user-panel`                                                                        | no existe                                                                                                 | la v4 pone usuario y avatar en la navbar (`.user-menu`, `.user-header`, `.user-footer`, `.user-image`)                                                                                                                                   |
| `elevation-{0..5}`                                                                   | no existen → `shadow`, `shadow-sm`, `shadow-lg` de BS                                                     |                                                                                                                                                                                                                                          |
| `img-circle`                                                                         | no existe → `rounded-circle`                                                                              |                                                                                                                                                                                                                                          |
| `btn-default`, `btn-flat`                                                            | no existen (0 coincidencias en AL4 y en BS5)                                                              | candidato natural: `btn-secondary` / `btn-outline-secondary`; esto **no lo dicta ninguna guía oficial**                                                                                                                                  |
| `profile-user-img`, `profile-username`, `list-group-unbordered`                      | no existen                                                                                                |                                                                                                                                                                                                                                          |
| `bg-navy` y otros colores extra                                                      | solo con el CSS opt-in `adminlte-colors.css` o `adminlte-colors-v3.css`                                   |                                                                                                                                                                                                                                          |

### Bootstrap 4 → 5 (oficial: `bs-mig/migration-5.0.md` y `migration-5.3.mdx`; también `migration.mdx` de AL4)

| BS4                                                                              | BS5.3                                                                                                                                                                                                                                                              | Fuente (línea en migration-5.0.md salvo indicación)                                                                                                           |
| -------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `data-toggle/target/dismiss/parent/spy/ride`                                     | `data-bs-*`                                                                                                                                                                                                                                                        | 370                                                                                                                                                           |
| `ml-* mr-* pl-* pr-*`                                                            | `ms-* me-* ps-* pe-*`                                                                                                                                                                                                                                              | 5.3: 676                                                                                                                                                      |
| `float-left/right`, `text-left/right`, `border-left/right`, `rounded-left/right` | `*-start/*-end`                                                                                                                                                                                                                                                    | 314-319                                                                                                                                                       |
| `dropdown-menu-right`                                                            | `dropdown-menu-end`                                                                                                                                                                                                                                                | 140 (lógicas start/end); clase presente en BS 5.3.8                                                                                                           |
| `font-weight-*`, `font-italic`, `text-monospace`                                 | `fw-*`, `fst-italic`, `font-monospace`                                                                                                                                                                                                                             | 331, 337                                                                                                                                                      |
| `form-group`, `form-row`, `form-inline`                                          | eliminadas → `mb-3` y grid (`row g-*`)                                                                                                                                                                                                                             | 158                                                                                                                                                           |
| labels                                                                           | requieren `.form-label`                                                                                                                                                                                                                                            | 160                                                                                                                                                           |
| `custom-control custom-checkbox` + `custom-control-input/label`                  | `form-check` + `form-check-input/label`                                                                                                                                                                                                                            | 147                                                                                                                                                           |
| `custom-switch`                                                                  | `form-check form-switch`                                                                                                                                                                                                                                           | 148                                                                                                                                                           |
| `custom-select`                                                                  | `form-select`                                                                                                                                                                                                                                                      | 149                                                                                                                                                           |
| `custom-file` / `custom-file-input` / `custom-file-label`                        | `<input type="file" class="form-control">`                                                                                                                                                                                                                         | 150                                                                                                                                                           |
| `input-group-append/prepend`                                                     | eliminadas: `.input-group-text` o botón como hijo directo                                                                                                                                                                                                          | 154                                                                                                                                                           |
| `badge-pill`                                                                     | `rounded-pill`                                                                                                                                                                                                                                                     | 188                                                                                                                                                           |
| `badge-{color}`                                                                  | `bg-{color}` o, en 5.3, `text-bg-{color}`                                                                                                                                                                                                                          | 186; 5.3: 324                                                                                                                                                 |
| `btn-block`                                                                      | envolver en `.d-grid` (+ `gap-*`)                                                                                                                                                                                                                                  | 204                                                                                                                                                           |
| `.close`                                                                         | `.btn-close` (sin `&times;` dentro)                                                                                                                                                                                                                                | 228                                                                                                                                                           |
| `sr-only`, `sr-only-focusable`                                                   | `visually-hidden`, `visually-hidden-focusable`                                                                                                                                                                                                                     | 359-361. **Ojo:** AL4 4.10.1 sigue definiendo `.sr-only` en `src/scss/_accessibility.scss:90`, así que no rompe, pero lo canónico en BS5 es `visually-hidden` |
| `thead-light`                                                                    | `table-light`                                                                                                                                                                                                                                                      | 122                                                                                                                                                           |
| `no-gutters`                                                                     | `g-0`                                                                                                                                                                                                                                                              | 94                                                                                                                                                            |
| tooltip/popover `.arrow`                                                         | `.tooltip-arrow` / `.popover-arrow`                                                                                                                                                                                                                                | 284, 304                                                                                                                                                      |
| `text-muted`                                                                     | sigue en 5.3 pero deprecada → `text-body-secondary`                                                                                                                                                                                                                | 5.3: 84, 233                                                                                                                                                  |
| `navbar-dark`, `dropdown-menu-dark`, `btn-close-white`                           | deprecadas → `data-bs-theme="dark"`                                                                                                                                                                                                                                | 5.3: 169-177                                                                                                                                                  |
| jQuery en Bootstrap                                                              | eliminado; **pero** si `window.jQuery` existe y `<body>` no tiene `data-bs-no-jquery`, BS5 registra `$.fn.modal/.tooltip/.tab/…` y emite eventos por jQuery (`bs538/bootstrap.bundle.js:213-250`; docs `getting-started/javascript.mdx` "Optionally using jQuery") |                                                                                                                                                               |

Clases de AL3 que ya no existen en `adminlte.css` 4.10.1 (grep de `.clase` exacto, 0 coincidencias): `wrapper`, `main-header`, `main-sidebar`, `content-wrapper`, `nav-sidebar`, `sidebar-light-primary`, `sidebar-dark-primary`, `navbar-white`, `elevation-*`, `img-circle`, `btn-default`, `btn-flat`, `profile-user-img`, `profile-username`, `list-group-unbordered`, `user-panel`, `error-page`, `dark-mode`, `bg-navy` (solo opt-in), `badge-pill`, `btn-block`, `form-group`, `custom-control`, `thead-light`, `text-sm`. `input-group-append` solo aparece en selectores heredados de `.login-card-body` (`src/scss/pages/_login_and_register.scss:56-77`), no como clase de layout.

---

## 4. Modo oscuro (oficial: `src/html/components/docs/color-mode.mdx`, `src/ts/color-mode.ts`)

- Mecanismo: el atributo `data-bs-theme="light|dark"` en `<html>` (color modes nativos de BS 5.3) más `style.colorScheme`. Se puede aplicar por región, por ejemplo `<aside class="app-sidebar" data-bs-theme="dark">`.
- El módulo **ColorMode** viene dentro de `adminlte.js` desde la 4.1:
    - persiste en **`localStorage['lte-theme']`** con los valores `light`, `dark` o `auto`;
    - orden de resolución: 1) el valor guardado; 2) el `data-bs-theme` que el servidor puso en `<html>`; 3) `prefers-color-scheme`, seguido en vivo;
    - enlaza solo los botones `[data-bs-theme-value]` y sincroniza `active`, `aria-pressed`, `.bi-check-lg` y `[data-lte-theme-icon]`;
    - emite el evento `changed.lte.color-mode` en `document` con `detail.theme` y `detail.resolved`;
    - API: `new ColorMode().setTheme('dark')`.
- **Anti-FOUC:** un snippet inline oficial en `<head>`, que es la única pieza que debe ir inline (`dist/starter.html:9-46`). Lee `lte-theme`, escribe `data-bs-theme` y marca `data-lte-theme-resolved`.
- **Opt-out:** `<html data-lte-color-mode="off">`. Con eso ColorMode nunca escribe `data-bs-theme` (ni al cargar, ni con clicks en toggles, ni cuando cambia la preferencia del SO), y el snippet oficial también lo respeta.
- **Integración con el toggle propio** (clave `'theme'`, clase `.dark-mode` en `<html>`). Hay dos caminos oficiales:
    - **(a)** Mantener el toggle propio: poner `data-lte-color-mode="off"` en `<html>` en los tres layouts y hacer que el IIFE anti-FOUC y `theme-toggle.js` escriban `document.documentElement.setAttribute('data-bs-theme', …)` (más `style.colorScheme`) en lugar de, o además de, la clase `.dark-mode`, conservando la clave `'theme'`.
    - **(b)** Adoptar ColorMode: botones `[data-bs-theme-value]`, clave `lte-theme` y modo `auto`. Esto implica migrar o leer el valor viejo de `'theme'` una sola vez. Esta migración del valor **no la documenta AdminLTE**; sería código propio.
    - En los dos casos los selectores `html.dark-mode …` de `dark-mode.css`, `login-dark.css` y `errors-dark.css` deberían pasar a `[data-bs-theme="dark"] …`. AL4 no define `.dark-mode`.
    - Las páginas standalone (auth/error) no cargan necesariamente `adminlte.js`, pero el atributo funciona igual porque el CSS reacciona a `data-bs-theme`.

---

## 5. Temas oficiales para plugins

- **Solo existe uno: Select2.** `dist/css/adminlte-select2(.rtl)(.min).css`, con fuente en `src/scss/compat/_select2.scss` (es el único archivo de `src/scss/compat/`). Está en el CHANGELOG de 4.3.0, `CHANGELOG.md:209`.
    - Es solo CSS: no trae JS ni jQuery. Se carga **después** de `select2.min.css`.
    - Sigue el markup de Select2 4.1.x (y el de quitar opción de 4.0.x).
    - Cubre métricas de `.form-select` (incluidos `-sm` y `-lg`), anillo de foco, `.is-invalid` / `.is-valid`, `input-group`, píldoras de selección múltiple, dark mode vía `data-bs-theme` (también el dropdown que se cuelga del body) y RTL.
    - **Sube el `z-index` por encima de `.modal`: "dropdownParent is no longer needed"** (`integrations.mdx:118-173`). Esto afecta la convención de AGENTS.md sobre `dropdownParent`; **no lo verifiqué en navegador** con el focus trap de BS5.
    - Con este tema no hace falta el `select2-bootstrap-5-theme` de terceros que menciona el plan.
- **No hay tema oficial** para DataTables, jQuery Validate, SweetAlert2 ni Chart.js. AL4 recomienda Tabulator en vez de DataTables y Tom Select en vez de Select2 (`migration.mdx`, tabla "Third-party plugin replacements"). Para DataTables lo propio del fabricante es `datatables.net-bs5` 3.1.5 (npm), que no es de AdminLTE.
- Hojas de color opt-in, aparte de las de plugins:
    - `adminlte-colors.css`: 14 colores rediseñados más skins.
    - `adminlte-colors-v3.css`: los 18 colores de AL3 "exactly as AdminLTE 3 shipped them" (cabecera de `src/scss/adminlte-colors-v3.scss`).
    - Se carga una u otra, no ambas. Además está `<html data-lte-primary="…">`.

---

## 6. Guía oficial de migración y breaking changes

- **Sí existe:** `src/html/components/docs/migration.mdx`, publicada en `https://adminlte.io/themes/v4/docs/migration.html`. También hay un resumen en `README.md:161-170` y una entrada en el FAQ (`faq.astro:118`).
- Breaking changes relevantes para este proyecto:
    1. Bootstrap 4.6 → 5.3 (todas las renombradas de §3).
    2. Se elimina jQuery de AdminLTE y de BS, sin shims `$.fn`. Las llamadas tipo `$('.x').PushMenu()` dejan de funcionar.
    3. Desaparece la carpeta `plugins/` (iCheck, Select2, DataTables…).
    4. `.dark-mode` pasa a `data-bs-theme`.
    5. Los colores extra y los skins de sidebar/navbar pasan a CSS opt-in.
    6. Iconos: las demos usan Bootstrap Icons, pero "nothing in AdminLTE 4 forces Bootstrap Icons". Font Awesome se puede mantener.
    7. La persistencia de la sidebar está desactivada por defecto (desde 4.0.0).
    8. Solo navegadores evergreen.
    9. Las páginas de error ya no tienen clases propias (`error-page` desapareció).
- Orden de migración que sugiere la guía: rama aparte → renombrar clases → `data-bs-*` → dark mode → auditar jQuery → cambiar plugins uno por uno → probar breakpoints (BS5 añade `xxl`) → probar impresión.
- Herramienta citada por la guía: `github.com/dbtek/bootstrap-5-migration`. No la evalué.

## NO VERIFICADO

- Nombre exacto del global UMD (`adminlte`) en tiempo de ejecución.
- Render de `.small-box-icon` sobre `<i class="fas …">`.
- Que el tema `adminlte-select2.css` elimine de verdad la necesidad de `dropdownParent` con el focus trap de los modales BS5 en este proyecto.
- Ausencia de colisión entre los `*-error` que crea `initAccessibility` y los de jQuery Validate.
- Compatibilidad de Select2 4.1.0 y jQuery Validate 1.22.1 con jQuery 4.0.0 en este proyecto. jquery-validation declara `^4.0` en su peer; Select2 no declara dependencias en npm.
