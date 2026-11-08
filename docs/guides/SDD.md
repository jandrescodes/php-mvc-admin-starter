# Spec-Driven Development: artifacts and phases

Reference for the SDD flow this project follows (the operating rules and the docs profile live in `AGENTS.md` › Spec-Driven Development).

## Artifacts

```
docs/constitution.md          ← project principles (project level)
docs/roadmap.md               ← what's done / in progress / backlog (project level)
specs/NNN-<slug>/
├── spec.md                   ← WHAT and WHY (FRs in EARS) + § Clarifications
├── plan.md                   ← HOW (modules, data, decisions, risks)
├── tasks.md                  ← <30 min tasks + closeout block
└── validation.md             ← FR-by-FR walkthrough + verdict (left by phase 7)
```

A feature isn't closed until all four files exist.

## Phases

| #   | Phase          | Command (plugin / script)                   |
| --- | -------------- | ------------------------------------------- |
| 1   | Constitution   | `/sdd:constitution` · `/sdd-constitution`   |
| 2   | Specification  | `/sdd:spec` · `/sdd-spec`                   |
| 3   | Clarification  | `/sdd:clarify` · `/sdd-clarify`             |
| 4   | Planning       | `/sdd:plan` · `/sdd-plan`                   |
| 5   | Tasks          | `/sdd:tasks` · `/sdd-tasks`                 |
| 6   | Implementation | `/sdd:implement <n>` · `/sdd-implement <n>` |
| 7   | Validation     | `/sdd:validate` · `/sdd-validate`           |
| 8   | Change         | `/sdd:change <req>` · `/sdd-change <req>`   |

Cross-cutting, not a ninth phase: `/sdd:docs
<prd|trd|acceptance|changes|delivery|schema|flow|ux> [feature]` · `/sdd-docs <…>` builds a project document from the artifacts
above, after you approve a proposal.
