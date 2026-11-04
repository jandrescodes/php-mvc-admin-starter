# Constitución — php-mvc-admin-starter

Principios no negociables. Toda spec, plan y tarea debe cumplirlos.

1. **Alcance del producto**: starter MVC en PHP para administración (autenticación, usuarios, roles, permisos, registro de auditoría). NO es: un framework (Laravel/Symfony), un frontend con proceso de build ni un SaaS.
2. **Stack**: PHP 8.2+, PDO/MySQL, Composer con PSR-4, AdminLTE 4 + Bootstrap 5 como capa de presentación, sin jQuery y sin proceso de build. Composer (`almasaeed2010/adminlte`, `require-dev`) y npm (`devDependencies`) son solo fuente de descarga de los assets de frontend: un script los copia a `public/`, que se versiona. Ni la ejecución ni el despliegue requieren Node ni `vendor/` de desarrollo. Las versiones concretas viven en `AGENTS.md` y en el plan de cada spec, no aquí.
3. **La spec manda**: no se implementa comportamiento que no esté en la spec activa. Si falta una decisión, el trabajo se detiene y se pregunta.
4. **Separación de capas**: el backend (PHP) nunca imprime HTML de negocio; las vistas son plantillas delgadas sin lógica de negocio. Los controladores leen la entrada, llaman a modelos, dejan mensajes flash, redirigen o devuelven JSON.
5. **Verificación como puerta**: toda tarea termina verificada, con `composer check` en verde o un checklist manual documentado con evidencia. Nunca se avanza sin ello.
6. **Datos y persistencia**: MySQL vía el singleton PDO; todas las consultas preparadas; nada de SQL crudo sobre la columna de contraseña; los borrados destructivos piden confirmación en la interfaz y quedan en el registro de auditoría.
7. **Idioma**: specs, planes y artefactos generados en español; código e identificadores en inglés.
