# Constitution — php-mvc-admin-starter

Non-negotiable principles. Every spec, plan, and task must comply with them.

1. **Product scope**: PHP MVC admin starter (auth, users, roles, permissions, audit log). It is NOT: a framework (Laravel/Symfony), a build-process frontend, or a SaaS product.
2. **Stack**: PHP 8.2+, PDO/MySQL, Composer PSR-4, AdminLTE 3 → 4, Bootstrap 5, jQuery (isolated for Select2/DataTables), no npm build process.
3. **The spec rules**: no behavior is implemented unless it's in the active spec. If a decision is missing, work stops and the question is asked.
4. **Layer separation**: backend (PHP) never prints HTML; views are thin templates without business logic. Controllers read input, call models, set flash, redirect or return JSON.
5. **Verification as a gate**: every task ends verified — `composer check` green or a documented manual checklist with evidence attached. Never proceed without it.
6. **Data / persistence**: MySQL via PDO singleton; all queries prepared; no raw SQL on password column; soft deletes only.
7. **Content language**: specs, plans, and generated artifacts in Spanish; code and identifiers in English.
