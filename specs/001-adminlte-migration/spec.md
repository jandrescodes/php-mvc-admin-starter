# Spec — Migración AdminLTE 3.2.0 → 4.x

> Fase 2 de SDD — Especificación. Contrato de requisitos para la actualización del frontend.
> **Contiene WHAT y WHY solamente.** Versiones, rutas de archivos, herramientas y mecanismos de entrega viven en `plan.md`.

## Propósito

Pasar la capa de presentación de AdminLTE 3.2.0 (Bootstrap 4, jQuery) a AdminLTE 4.x (Bootstrap 5.3), adoptando los componentes y las librerías que AdminLTE 4 documenta como suyos (tablas, selectores, iconos, validación, gráficos), todos sin jQuery. El comportamiento de negocio no cambia.

El proyecto sirve también de práctica del stack nuevo; otros proyectos siguen en AdminLTE 3.2.0, que queda como respaldo si la migración se descarta.

## Por qué

- AdminLTE 3 y Bootstrap 4 están fuera de mantenimiento; hoy el proyecto mezcla CSS de Bootstrap 5.0.2 con JS de Bootstrap 4.6.1, y varias librerías están muy atrasadas.
- AdminLTE 4 ya no incluye plugins jQuery y documenta alternativas sin jQuery para cada uno. Seguirlas evita arrastrar jQuery y sus incompatibilidades.
- El plan previo (`docs/legacy/PLAN_ADMINLTE_MIGRATION.md`) recomendaba no migrar (opción B). Se revierte esa recomendación porque el desfase de versiones ya genera defectos visuales y de seguridad (dependencias con vulnerabilidades corregidas en versiones posteriores) y el costo de seguir pospuesto crece. El mismo plan queda como referencia histórica.

## Alcance

### Dentro

- La shell de la aplicación (layouts, barra superior, menú lateral, contenido, pie, páginas independientes de autenticación y de error) reconstruida con los componentes de AdminLTE 4.
- Todas las vistas de negocio (autenticación, usuarios, roles, permisos, registro de auditoría, dashboard, perfil), con el markup y las clases de AdminLTE 4 / Bootstrap 5.
- La interactividad del cliente (selectores, tooltips, tablas, modales, pestañas, toggles de contraseña, cambio de tema, formularios AJAX, validación, notificaciones, gráficos, iconos) reescrita sobre las APIs de Bootstrap 5 y las librerías que AdminLTE 4 recomienda, sin jQuery.
- Las hojas de estilo propias y el modo oscuro, apoyados en el mecanismo de temas de AdminLTE 4 / Bootstrap 5.3.
- El registro de assets del frontend (qué archivos carga cada pantalla), por ser parte de la entrega de la capa de presentación.
- El reemplazo de las librerías jQuery actuales (tablas, selectores, validación) por sus alternativas sin jQuery, y el retiro de jQuery del proyecto.
- El paso de los datos de las tablas desde la vista (sin cambiar lo que el backend decide ni devuelve).

### Fuera

- El comportamiento PHP: controladores, modelos, servicios, rutas, middlewares, autenticación, permisos, CSRF, base de datos. Se conserva tal cual; solo cambia el markup que las vistas imprimen.
- Funcionalidades de negocio nuevas.
- Cambios de CI/CD.
- Navegadores no evergreen.
- Endpoints nuevos o cambios de contrato en los existentes para alimentar las tablas.

## Requisitos funcionales (EARS)

### Shell y navegación

FR-1. THE SYSTEM shall renderizar la aplicación con el layout de AdminLTE 4: contenedor, barra superior, menú lateral colapsable, área de contenido y pie.
FR-2. WHILE una sesión está activa, THE SYSTEM shall mostrar en el menú lateral solo los módulos para los que la sesión tiene permiso (dashboard, usuarios, permisos, roles, auditoría).
FR-3. WHEN la persona usuaria pulsa el botón de menú, THE SYSTEM shall colapsar o expandir el menú lateral.
FR-4. THE SYSTEM shall mostrar el logotipo y el nombre del sistema en la barra superior y en el menú lateral.
FR-5. THE SYSTEM shall mostrar en la barra superior el menú de usuario con avatar, nombre, rol, acceso al perfil y cierre de sesión.
FR-6. WHEN se carga una pantalla, THE SYSTEM shall resaltar en el menú el ítem de la ruta actual.
FR-7. THE SYSTEM shall mostrar la versión de la aplicación en el pie.

### Autenticación y páginas independientes

FR-8. THE SYSTEM shall renderizar login, olvido de contraseña, restablecimiento y aceptación de invitación como páginas independientes, sin el layout de la aplicación.
FR-9. WHEN una acción deja un mensaje en sesión y la página se recarga, THE SYSTEM shall mostrarlo (éxito, error, advertencia, información), con JavaScript disponible y sin él.
FR-10. WHEN una persona inicia sesión por primera vez, THE SYSTEM shall mostrar una ventana de bienvenida.
FR-31. WHEN ocurre un error 403, 404 o 500, THE SYSTEM shall mostrarlo en una página independiente, con la tonalidad (peligro/advertencia) que corresponde al código.
FR-34. THE SYSTEM shall presentar casillas y opciones de selección (p. ej. «Recordarme») con los estilos nativos de AdminLTE 4 / Bootstrap 5, sin librerías de estilo adicionales.

### Usuarios, roles y permisos

FR-11. THE SYSTEM shall listar usuarios, roles, permisos y eventos de auditoría en tablas con paginación, ordenamiento, búsqueda, exportación (copiar, CSV, Excel, PDF, imprimir) y elección de columnas visibles, sin recargar la página.
FR-12. THE SYSTEM shall permitir crear, editar, activar/desactivar y eliminar usuarios, roles y permisos mediante formularios, modales y acciones AJAX.
FR-13. THE SYSTEM shall permitir asignar y revocar permisos directos a usuarios y sincronizar los permisos de un rol.
FR-14. WHEN la persona usuaria introduce un correo, documento o nombre de rol/permiso, THE SYSTEM shall comprobar su unicidad antes de guardar y mostrar el error junto al campo.
FR-15. IF la persona autenticada no posee un privilegio, THEN THE SYSTEM shall impedir que lo conceda y mostrar el motivo.
FR-16. WHEN la persona usuaria inicia un borrado o una desactivación, THE SYSTEM shall pedir confirmación antes de ejecutarla.

### Dashboard y auditoría

FR-17. THE SYSTEM shall mostrar en el dashboard métricas de usuarios, permisos, roles y eventos recientes, con gráficos legibles en ambos temas.
FR-18. THE SYSTEM shall mostrar el registro de auditoría en solo lectura, con paginación y detalle de cada evento.
FR-19. WHEN la persona usuaria solicita la exportación del registro de auditoría, THE SYSTEM shall entregar el PDF generado por el servidor.

### Tema y accesibilidad

FR-20. THE SYSTEM shall ofrecer modo claro y oscuro, persistir la elección en el navegador y, sin elección previa, seguir la preferencia del sistema.
FR-21. WHEN se carga una página, THE SYSTEM shall aplicar el tema elegido antes del primer pintado, sin parpadeo visible.
FR-22. WHEN la persona usuaria pulsa el botón de tema, THE SYSTEM shall alternar entre claro y oscuro.
FR-23. THE SYSTEM shall mantener contraste mínimo 4.5:1 en texto y elementos interactivos en ambos temas, incluidos los componentes de terceros (tablas, selectores, notificaciones, gráficos).
FR-24. THE SYSTEM shall poder operarse con teclado y lector de pantalla en todas las vistas migradas, incluidos modales y menús.

### Interactividad y formularios

FR-25. THE SYSTEM shall presentar los campos de selección con búsqueda y selección múltiple donde corresponda, también dentro de modales, y cada uno con su etiqueta accesible.
FR-26. WHEN la persona usuaria enfoca o pasa el cursor sobre un elemento con ayuda contextual, THE SYSTEM shall mostrar su tooltip, incluso tras reordenar o paginar una tabla.
FR-27. WHEN la persona usuaria envía un formulario, THE SYSTEM shall validarlo en el cliente con la validación nativa del navegador y mostrar el error asociado a cada campo con el estilo de validación de Bootstrap 5.
FR-28. WHEN se completa una acción mutante, THE SYSTEM shall notificar el resultado (éxito/error) sin recargar la página, salvo que el flujo requiera recarga.
FR-29. WHILE una acción mutante AJAX está en curso, THE SYSTEM shall mostrar un indicador de carga.
FR-30. WHEN un envío POST termina con éxito, o falla en una ruta sensible, THE SYSTEM shall seguir rotando el token CSRF y el cliente shall usar el token vigente en el siguiente envío. (Comportamiento existente; se conserva.)
FR-32. THE SYSTEM shall usar la API de Bootstrap 5 en modales, pestañas, desplegables, colapsables y tooltips, sin atributos ni clases de Bootstrap 4 / AdminLTE 3 que ya no existan.
FR-33. THE SYSTEM shall usar para cada función de interfaz la librería que AdminLTE 4 documenta, en una versión estable, y no conservar archivos de librerías que ya no se usen.
FR-35. THE SYSTEM shall presentar todos los iconos con el conjunto de iconos predeterminado de AdminLTE 4.
FR-36. WHEN la persona usuaria elige fechas de un filtro, THE SYSTEM shall aceptar y mostrar fechas en un formato legible y operable por teclado.

## Requisitos no funcionales

NFR-1. THE SYSTEM shall funcionar sin proceso de compilación frontend: Node/npm solo descargan y copian assets en desarrollo, y ni la ejecución ni el despliegue requieren Node.
NFR-2. THE SYSTEM shall conservar el router, el autoloader PSR-4, la conexión PDO y el hub de `Auth` sin cambios de comportamiento.
NFR-3. THE SYSTEM shall limitar el cambio en Composer a `almasaeed2010/adminlte` como `require-dev`, sin dependencias de producción nuevas.
NFR-4. THE SYSTEM shall pasar Pint (PSR-12), PHPStan nivel 5 y las suites unitaria e integral existentes, sin modificar su código.
NFR-5. THE SYSTEM shall poder verificarse con un checklist manual por módulo cuyos resultados queden con evidencia (captura o registro de consola).
NFR-6. THE SYSTEM shall servir cada librería de frontend en una versión fija y reproducible (`composer.lock` y `package-lock.json`) desde el propio repositorio, sin depender de CDN en tiempo de ejecución.
NFR-7. THE SYSTEM shall funcionar sin jQuery.
NFR-8. THE SYSTEM shall mostrar los datos de usuario que lleguen a tablas, selectores o diálogos escapados, nunca como HTML.

## Suposiciones

- Las clases de AdminLTE 3 (`wrapper`, `main-header`, `main-sidebar`, `content-wrapper`, `nav-sidebar`, etc.) no son compatibles con AdminLTE 4; las vistas requieren reescritura de markup.
- El backend PHP no necesita cambios de comportamiento.

## Restricciones

- Sin frameworks frontend (React, Vue, Angular, Livewire).
- Composer solo para `almasaeed2010/adminlte` en `require-dev`; npm solo en `devDependencies` (NFR-1, NFR-3).
- Sin jQuery (NFR-7).
- Prohibido el CDN en tiempo de ejecución (NFR-6).

## Criterios de aceptación

- Todas las rutas existentes renderizan bajo AdminLTE 4 sin errores en la consola del navegador.
- Claro y oscuro funcionan en todas las vistas, con persistencia y sin parpadeo.
- Las tablas de usuarios, roles, permisos y auditoría paginan, ordenan, buscan y exportan sin recargar.
- Los formularios de usuarios, roles y permisos validan (incluida la unicidad) y envían por AJAX.
- `grep -ri jquery` sobre `views/` y `public/js/` (código propio) no encuentra uso, y `public/` no contiene la librería.
- Ningún icono de Font Awesome (`fa-`) queda en `views/` ni en `public/`.
- Las acciones de estado, asignación y revocación piden confirmación y notifican el resultado.
- La exportación de auditoría a PDF funciona igual que antes.
- Ningún atributo `data-toggle`, `data-dismiss`, `data-target`, `data-parent`, `data-card-widget` ni `data-widget` de Bootstrap 4 / AdminLTE 3 queda en `views/` ni en `public/js/`.
- Pint, PHPStan y las suites existentes pasan sin tocar el backend.

## Clarificaciones (Fase 3 de SDD) — resueltas

Verificado en la documentación oficial y en el código fuente de cada versión (informes del 2026-11-04).

| #   | Pregunta                                           | Respuesta verificada                                                                                                                                                                                                                                                                                                                                                                                                                           | Impacto                                                                                                                      |
| --- | -------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------- |
| C1  | ¿Cómo se obtiene AdminLTE 4?                       | Composer (`almasaeed2010/adminlte`, `require-dev`, tag 4.10.1; el paquete npm se quedó en 4.10.0) para `dist/` de AdminLTE, y npm (`devDependencies`) para Bootstrap, OverlayScrollbars, Bootstrap Icons, Tabulator, Tom Select, Chart.js, SweetAlert2, jsPDF y SheetJS, como indica la documentación oficial de AdminLTE 4 (rutas Composer y npm). Un script copia solo los archivos necesarios a `public/`, que se versiona; no hay bundler. | Entrega y versiones en `plan.md`. Cambia `composer.json`, aparece `package.json`, y la constitución (principio 2) se ajusta. |
| C2  | ¿Qué pasa con jQuery?                              | **Se elimina.** AdminLTE 4 y Bootstrap 5 no lo requieren y la documentación de AdminLTE 4 sustituye cada plugin jQuery por una alternativa sin jQuery. Sin DataTables, Select2 ni Validate, nada del proyecto lo exige (modales, pestañas, tooltips, AJAX tienen equivalente nativo).                                                                                                                                                          | NFR-7. Sustituye a la decisión previa de conservarlo aislado.                                                                |
| C3  | ¿Alcance de las pruebas de regresión en navegador? | Un solo desarrollador: happy path + casos límite críticos por módulo (login, crear/editar/desactivar usuario, rol y permiso, asignar/revocar, exportar PDF, tema, menú).                                                                                                                                                                                                                                                                       | Checklist reducido, con evidencia (NFR-5).                                                                                   |
| C4  | ¿Cómo se maneja el modo oscuro?                    | AdminLTE 4 / Bootstrap 5.3 usan el atributo `data-bs-theme` en `<html>`. El interruptor propio se conserva y escribe ese atributo; el selector `html.dark-mode` desaparece.                                                                                                                                                                                                                                                                    | FR-20 a FR-23; reescritura de las reglas de modo oscuro.                                                                     |
| C5  | ¿Qué selector se usa?                              | El que AdminLTE 4 documenta para reemplazar a Select2 (búsqueda, selección múltiple, etiquetas). Sin jQuery y con tema para Bootstrap 5.                                                                                                                                                                                                                                                                                                       | FR-23, FR-25. Sustituye la decisión previa de usar Select2 con su hoja de compatibilidad.                                    |
| C6  | ¿Casillas `iCheck`?                                | Se usan en un solo lugar (login). Se reemplazan por el estilo nativo de Bootstrap 5.                                                                                                                                                                                                                                                                                                                                                           | FR-34, FR-33.                                                                                                                |

| C7 | ¿Qué librería de tablas se usa y de dónde salen los datos? | La que AdminLTE 4 documenta para reemplazar a DataTables. No funciona bien sobre un `<table>` ya renderizado (pierde los `data-*` de la fila y exporta el HTML crudo). Los datos viajan como JSON en un atributo `data-*` del contenedor que imprime la vista; los badges y botones se dibujan en cliente con escape. | FR-11, NFR-8. Sin endpoint nuevo (fuera de alcance). |
| C8 | ¿Qué iconos se usan? | Los que AdminLTE 4 trae por defecto (SVG de fuente). El proyecto usa 67 iconos distintos de Font Awesome, todos con equivalente verificado. | FR-35. |
| C9 | ¿Se usa un selector de fechas de terceros? | No. Solo el filtro de auditoría elige fechas y el campo nativo de fecha cumple FR-36; el selector de terceros añadiría problemas de accesibilidad (campo de solo lectura, etiqueta desconectada, tema oscuro propio). | FR-36. |
| C10 | ¿Se conserva SweetAlert2? | Sí. AdminLTE 4 no lo sustituye, es independiente de jQuery y reemplazarlo obligaría a reescribir confirmaciones, carga y bienvenida. Se le pasa el tema activo. | FR-10, FR-16, FR-23, FR-28. |
| C11 | ¿Cómo se valida la unicidad sin Validate? | La API de validación nativa con mensaje personalizado, consultando los endpoints existentes con `fetch` (con la cabecera de petición AJAX que el servidor espera). Los endpoints no cambian. | FR-14, FR-27, NFR-2. |
| C12 | ¿Cómo se exporta con la nueva tabla? | Con paridad: Copiar, CSV, Excel, PDF e Imprimir, usando las librerías de exportación que la tabla requiere (Excel y PDF). Si alguna falla en la prueba de exportación, se reduce a copiar, CSV e imprimir y se registra aquí. | FR-11. |

El `plan.md` recoge estas decisiones y las versiones concretas.
