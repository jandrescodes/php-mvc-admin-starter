# Plan de migración AdminLTE 3.2.0 → AdminLTE 4.x

> Generado con la Plantilla 4 — Consulta de arquitectura de `PROMPTS.md`.
> Repositorio: `php-mvc-admin-starter` (PHP MVC custom, sin framework, Composer, sin build process).

---

## Prompt usado (Plantilla 4 rellena)

```
[Rol]
Actúa como arquitecto de software PHP con experiencia en sistemas MVC
custom, diseño de base de datos y patrones de diseño (Repository, Service Layer).

[Contexto]
Proyecto: php-mvc-admin-starter — PHP MVC custom (sin framework; Composer para dependencias de terceros).
Stack actual: App\Core\Router, middleware por ruta, PSR-4 custom autoloader,
              PDO singleton (Connection::getInstance()), App\Core\Auth (hub de sesión/permisos).
Módulos existentes: auth, users (con flujo de invitación por email — pending → accept → active), permissions, roles, dashboard (con métricas Chart.js, DashboardCache, animaciones CSS de entrada y toggle de métricas de acceso), audit-log (read-only, AuditLogger service), dark-mode (localStorage + prefers-color-scheme, sin BD).
Patrón de modelos: fat model / thin controller — validación, formateo e invalidación de caché viven en el modelo. App\Core\Model provee CRUD genérico heredable (find, all, insert, update, delete, getLastInsertId, trimInput). User usa tres traits (UserAuthTrait, UserPasswordTrait, UserStatsTrait) bajo app/Models/Traits/.
Frontend actual: AdminLTE v3.2.0, Bootstrap 4, jQuery, DataTables (extensión bootstrap4), Select2 (tema bootstrap4), SweetAlert2, Font Awesome 5. Views con clases AdminLTE (wrapper, main-header, main-sidebar, content-wrapper, main-footer, hold-transition, sidebar-mini, nav-pills nav-sidebar, etc.). Sin npm, sin build process, assets estáticos en public/.
Restricciones clave de AGENTS.md:
- No build process (no npm, no Makefile — frontend assets son archivos estáticos en public/).
- No introducir librerías nuevas vía Composer sin aprobación del líder técnico.
- Mantener compatibilidad con Router, Connection singleton y el autoloader actual.
- Las vistas deben poder ser implementadas por un dev junior sin romper lo que existe.
- CSRF obligatorio, sanitización en modelo, escape en vista, ImageService para uploads.
- AJAX devuelve JSON con $this->jsonResponse(); feedback de sesión si hay location.reload().

[Tarea]
Necesito decidir: migrar el frontend de AdminLTE 3.2.0 a AdminLTE 4.x (última estable v4.10.1).

Opciones que estoy considerando:
- Opción A: Migrar a AdminLTE 4.x (Bootstrap 5, vanilla JS/TS, sin jQuery, requiere Node.js + build).
- Opción B: Quedarse en AdminLTE 3.2.0 y solo aplicar parches de seguridad/bugfix dentro de la rama v3.

[Restricciones]
- No introducir frameworks (ni Laravel, ni Symfony).
- No introducir librerías nuevas vía Composer sin aprobación del líder técnico.
- Mantener compatibilidad con Router, Connection singleton y el autoloader actual.
- La solución debe poder implementarla un dev junior sin romper lo que existe.
- Nuevos servicios van en app/Services/; la lógica de auth/sesión va en App\Core\Auth.

[Formato de salida]
1. Recomendación directa (cuál opción y por qué en 3 líneas)
2. Trade-offs de cada opción (tabla si aplica)
3. Impacto en el resto del sistema
4. Primeros pasos concretos para implementar la opción recomendada
```

---

## 1. Recomendación directa

**Opción B (quedarse en AdminLTE 3.2.0)** es la recomendada por ahora. AdminLTE 4 es una reescritura completa, no una actualización incremental: cambia Bootstrap 4 → 5, elimina jQuery por completo, adopta vanilla TypeScript y requiere un proceso de build con Node.js que el proyecto actualmente no tiene (y AGENTS.md prohíbe sin aprobación del líder técnico). Migrar implicaría reescribir prácticamente todo el frontend (layouts, views, JS de módulos, CSS custom, plugins de DataTables/Select2/tooltips), lo cual rompe el contrato "un dev junior puede implementarlo sin romper lo existente". Se recomienda revisar la migración solo cuando el equipo apruebe introducir Node.js/npm y reescribir el frontend de forma planificada.

---

## 2. Trade-offs de cada opción

| Dimensión                         | Opción A — Migrar a AdminLTE 4.x                        | Opción B — Quedarse en AdminLTE 3.2.0 |
| --------------------------------- | ------------------------------------------------------- | ------------------------------------- |
| **Versión actual del proyecto**   | v3.2.0 (confirmado en cabeceras de adminlte.min.css/js) | v3.2.0                                |
| **Última AdminLTE disponible**    | v4.10.1 (oct 2026)                                      | v3.2.0 (última de la rama 3)          |
| **Bootstrap**                     | 5.3.8                                                   | 4.6                                   |
| **JavaScript**                    | Vanilla TypeScript, sin jQuery                          | jQuery 3.5.1+                         |
| **Build process**                 | Requerido (Node.js, npm run production)                 | Ninguno (archivos estáticos)          |
| **Views/layouts**                 | Reescritura casi total (clases CSS cambian)             | Sin cambios                           |
| **JS de módulos**                 | Reescritura (todos usan jQuery/$)                       | Sin cambios                           |
| **CSS custom**                    | Revisión completa (clases AdminLTE cambian)             | Sin cambios                           |
| **Plugins (DataTables, Select2)** | Versiones compatibles con Bootstrap 5 / sin jQuery      | Versiones actuales (bootstrap4)       |
| **Riesgo**                        | Alto — reescritura frontend completa                    | Bajo — status quo                     |
| **Tiempo estimado**               | Semanas (varias fases)                                  | N/A                                   |
| **Cumple AGENTS.md**              | No (introduce npm/build, nuevas dependencias)           | Sí                                    |

---

## 3. Impacto en el resto del sistema

Si se elige la Opción A en el futuro, el impacto es transversalo:

- **`views/layouts/`** (header.php, footer.php, auth.php, error.php, sidebar.php): clases AdminLTE (`wrapper`, `main-header`, `main-sidebar`, `content-wrapper`, `hold-transition`, `sidebar-mini`, `nav-pills nav-sidebar`, `sidebar-light-primary`, etc.) cambian en v4. Hay que rehacer el markup y el dark-mode detection.
- **`public/js/core/`** (`ui-components.js`, `common-utils.js`, `common-validate.js`, `sweetalert-utils.js`): dependen de `$` (jQuery), `$.fn.select2`, `$.fn.tooltip`, DataTables jQuery plugin. Todo se rompe sin jQuery.
- **`public/js/modules/*`** (users, roles, permissions, audit-log, dashboard, profile, auth): delegación de eventos con `$(document).on(...)`, Select2, DataTables init, SweetAlert2. Reescritura a vanilla JS.
- **`app/Core/AssetRegistry.php`**: mapas de plugin CSS/JS referencian archivos `bootstrap4` y plugins jQuery. Hay que redefinir para Bootstrap 5 / versiones sin jQuery.
- **`views/*`**: clases de botones, tarjetas, alertas, modales de AdminLTE cambian. Revisar cada view.
- **CSS custom** (`public/css/core/`, `public/css/modules/*`): overrides de AdminLTE v3 (`.btn-sm`, `.small-box-footer`, dark-mode tokens `--dm-*`) pueden no aplicar en v4.
- **Backend**: sin impacto (PHP, rutas, middlewares, Auth, modelos no dependen de AdminLTE). Solo las views envían HTML al cliente.

---

## 4. Primeros pasos concretos (Opción B — mantener v3.2.0)

1. Confirmar que adminlte.min.css/js son v3.2.0 (ya verificado en cabeceras).
2. Revisar `.github/dependabot.yml` para vigilar si la rama AdminLTE 3 recibe parches críticos.
3. Documentar en `AGENTS.md` que el stack frontend está en AdminLTE 3.2.0 + Bootstrap 4 + jQuery por decisión de arquitectura.
4. Crear un issue/tarea para reevaluar la migración a AdminLTE 4 solo cuando el equipo apruebe introducir Node.js/npm y un proceso de build.

---

# Plan de implementación por fases atomicas (si se elige la Opción A en el futuro)

El plan se divide en fases atómicas, cada una reversible y probabile de forma independiente. Ninguna fase se inicia sin que la anterior esté verificada en staging.

## Fase 0 — Auditoría y baseline (semana 0)

**Objetivo:** Mapear todo el acoplamiento actual y establecer una línea de base probada.

Tareas atómicas:

1. Listar todas las clases CSS de AdminLTE usadas en `views/` y `public/css/`.
2. Listar todos los usos de `$`/`jQuery` en `public/js/`.
3. Listar plugins activos en `AssetRegistry.php` y sus archivos.
4. Ejecutar `composer check` (lint + stan + tests) y registrar el estado actual como baseline.
5. Documentar vistas que usan clases AdminLTE específicas (sidebar, navbar, cards, small-boxes, modales).

**Criterio de aceptación:** Inventario escrito con rutas y números de línea; suite de tests verde.

---

## Fase 1 — Infraestructura de build (semana 1)

**Objetivo:** Introducir Node.js/npm y un proceso de compilación sin romper el frontend actual.

Tareas atómicas:

1. Crear `package.json` con `admin-lte@^4`, `bootstrap@^5`, `jquery` (transicional), `datatables.net`, `select2`, `sweetalert2`.
2. Crear `webpack.mix.js` / `vite.config.js` o script de copia que emita archivos estáticos en `public/css/lib/adminlte/` y `public/js/lib/adminlte/`.
3. Configurar `.gitignore` para excluir `node_modules/` y assets generados (solo commitear fuente, no build).
4. Añadir script `npm run build` y `npm run dev` (watch).
5. Verificar que `composer check` sigue pasando (sin cambios en PHP).

**Criterio de aceptación:** `npm run build` genera archivos en `public/` idénticos funcionalmente a los actuales; `git status` limpio salvo `package.json`, `package-lock.json`, config de build.

---

## Fase 2 — Layouts y shell (semana 2)

**Objetivo:** Migrar `views/layouts/*` a AdminLTE 4 + Bootstrap 5 sin tocar módulos de negocio.

Tareas atómicas:

1. Reescribir `header.php`: reemplazar clases AdminLTE 3 por v4, Bootstrap 4 → 5, eliminar jQuery (cargar solo scripts vanilla de AdminLTE 4).
2. Reescribir `footer.php`: mismo cambio de clases y scripts.
3. Reescribir `sidebar.php`: clases de navegación v4 (`nav-sidebar`, treeview, etc.).
4. Reescribir `auth.php` y `error.php`: layouts standalone v4.
5. Migrar dark-mode detection (clase `dark-mode` en `html` vs `body`, tokens `--dm-*` de v4).
6. Probar en browser: login, dashboard, sidebar colapsable, navbar, theme toggle.

**Criterio de aceptación:** Las 5 vistas de layout renderizan sin errores de consola JS; dark-mode funciona; sidebar se abre/cierra; no hay referencias a `$` en layouts.

---

## Fase 3 — Utilidades JS core (semana 3)

**Objetivo:** Reescribir `public/js/core/*` de jQuery a vanilla JS.

Tareas atómicas:

1. `ui-components.js`: `initSelect2` → Select2 vanilla (o reemplazar por `<select>` nativo + biblioteca ligera), `initTooltips` → Bootstrap 5 Tooltip vanilla.
2. `common-utils.js`: delegación `$(document).on(...)` → `document.addEventListener(...)`; password toggle, formatDate, etc.
3. `common-validate.js`: jQuery Validate → validación nativa o librería sin jQuery.
4. `sweetalert-utils.js`: ya usa SweetAlert2 (no jQuery), revisar solo llamadas a `.html()` → `escapeHtml()`.
5. `theme-toggle.js`: revisar compatibilidad con dark-mode v4.

**Criterio de aceptación:** Ningún archivo en `public/js/core/` contiene `$` o `jQuery`; `ComponentUtils.initAll()` funciona sin jQuery; Select2 y tooltips inicializan en una página de prueba.

---

## Fase 4 — Módulos de negocio JS (semanas 4-6)

**Objetivo:** Migrar cada módulo JS a vanilla JS, uno por uno.

Tareas atómicas (una por módulo):
4a. `users/` (index, create, update, show, profile): DataTables vanilla, Select2 vanilla, formularios AJAX con `fetch()`.
4b. `roles/` (index, detail, modal): mismo patrón.
4c. `permissions/` (index, detail, modal): mismo patrón.
4d. `audit-log/`: tabla read-only, export (sin cambios backend).
4e. `dashboard/`: Chart.js (ya es vanilla), animaciones CSS.
4f. `profile/`: theme toggle, cambio de contraseña.
4g. `auth/` (login, forgot, reset, accept-invitation): validación y envío de formularios.

Cada subtarea incluye:

- Reemplazar `$(...)` por `document.querySelector(...)` / `fetch()`.
- Reemplazar DataTables jQuery plugin por `datatables.net` vanilla.
- Reemplazar Select2 jQuery por Select2 vanilla o nativo.
- Probar el módulo en browser con datos reales.

**Criterio de aceptación:** Todos los módulos funcionan sin jQuery; AJAX devuelve/procesa JSON; DataTables ordena/filtra/exporta; Select2 abre en modales.

---

## Fase 5 — CSS y estilos (semana 7)

**Objetivo:** Migrar CSS custom a Bootstrap 5 + AdminLTE 4 tokens.

Tareas atómicas:

1. `public/css/core/dark-mode.css`: actualizar variables `--dm-*` a los de AdminLTE 4; revisar `.bg-light`, `.thead-light`.
2. `public/css/modules/*`: revisar overrides de `.btn-sm`, `.small-box-footer`, clases AdminLTE v3.
3. `public/css/lib/adminlte/adminlte.min.css`: sustituir por build de v4.
4. Verificar contraste con axe-core en ambos temas (light/dark).

**Criterio de aceptación:** Ningún archivo CSS referencia clases eliminadas en v4; contraste ≥4.5:1 en ambos temas; no hay hojas de estilo huérfanas.

---

## Fase 6 — AssetRegistry y rutas de assets (semana 8)

**Objetivo:** Centralizar los nuevos assets en `AssetRegistry.php`.

Tareas atómicas:

1. Reescribir `getPluginCssMap()` y `getPluginJsMap()` para Bootstrap 5 / plugins sin jQuery.
2. Verificar que `resolvePluginCss()` / `resolvePluginJs()` retornan los archivos correctos.
3. Actualizar referencias en views si es necesario.

**Criterio de aceptación:** Todas las páginas cargan sus plugins desde AssetRegistry; consola sin errores 404 de assets.

---

## Fase 7 — Pruebas de regresión y firma (semana 9)

**Objetivo:** Validar que el sistema funciona igual que antes con el nuevo frontend.

Tareas atómicas:

1. Ejecutar `composer check` (lint + stan + tests unit + integ).
2. Prueba manual de cada módulo: login, CRUD users, roles, permissions, audit-log, dashboard, profile, dark-mode.
3. Prueba en móviles y desktop.
4. Documentar decisiones y actualizar `AGENTS.md` si se introduce npm/build.

**Criterio de aceptación:** Suite verde; checklist de testing manual aprobado por el equipo; `AGENTS.md` actualizado si aplica.

---

## Feasibility verdict

| Criterio                           | Resultado                                                            |
| ---------------------------------- | -------------------------------------------------------------------- |
| ¿Es una actualización incremental? | **No** — reescritura frontend completa                               |
| ¿Requiere nuevo tooling?           | **Sí** — Node.js, npm, build process                                 |
| ¿Rompe AGENTS.md?                  | **Sí** (no build process, no nuevas librerías sin aprobación)        |
| ¿Impacta backend?                  | **No** — solo views y assets estáticos                               |
| ¿Es recomendable ahora?            | **No** — esperar a aprobación de líder técnico y plan de reescritura |
