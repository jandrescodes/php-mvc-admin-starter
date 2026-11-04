# Roadmap — php-mvc-admin-starter

Cross-feature view: what's done, what's next, and what's in the backlog.
Project-level artifact (alongside `docs/constitution.md`). Maintained by
hand or by `/sdd:validate`, which moves a feature to `Done ✅` when it closes;
`/sdd:spec` reads it when picking a number.

Each Done / Next entry links the feature's **folder**
(`specs/NNN-<slug>/`), never just a number.

## Done ✅

1. **Authentication** — login, logout, remember-me, forgot-password, reset-password → legacy (pre-SDD)
2. **User management** — CRUD, status toggle, unlock login, resend invitation → legacy (pre-SDD)
3. **Role and permission management** — CRUD, assign/revoke, sync → legacy (pre-SDD)
4. **Dashboard and audit log** — metrics, read-only log, PDF export → legacy (pre-SDD)
5. **Dark mode and image uploads** — theme toggle, ImageService → legacy (pre-SDD)
6. **Testing and CI** — PHPUnit unit + integration, GitHub Actions, Pint, PHPStan → legacy (pre-SDD)

_Features predating SDD adoption: one line each, without retroactively
writing `spec/plan/tasks`; the detail lives in `CHANGELOG.md`._

## Next 🔜

- **AdminLTE 3.2.0 → 4.x migration** — frontend re-presentation to AdminLTE 4 + Bootstrap 5 → `specs/001-adminlte-migration/`

## Backlog · ideas 💡

_Not committed or fully ordered. Every idea must respect
`docs/constitution.md` (including product scope)._

- **Replace jQuery for Select2/DataTables** — evaluate native alternatives when a stable jQuery-free version exists.
