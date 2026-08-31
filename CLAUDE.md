# CLAUDE.md

@AGENTS.md

`AGENTS.md` (imported above) is the **single source of truth** for this repo — setup, architecture,
and every coding convention. Keep it, not this file, updated when a convention changes. This file
only adds Claude Code–specific notes.

## Claude Code specifics

- **Memory:** persistent notes in `~/.claude/projects/-opt-lampp-htdocs-php-mvc-admin-starter/memory/`
  (index in `MEMORY.md`). Review it at the start of a session; write project/feedback facts there,
  not in this file.
- **Skills** (`.claude/skills/`): `code-review` (PHP MVC review before merging a feature branch),
  `design-review`, and the SDD workflow set (`sdd-constitution`, `sdd-spec`, `sdd-clarify`,
  `sdd-plan`, `sdd-tasks`, `sdd-implement`, `sdd-validate`, `sdd-change`). Invoke with `/<name>`.
- **MCP servers** (`.mcp.json` — details in `docs/AI_SETUP.md`): `mysql`, `phpstorm`, `playwright`,
  `github`. Bash permission allowlist in `.claude/settings.example.json`.
- **After fixing a mistake**, document the convention in `AGENTS.md` (project-specific) or
  `~/.claude/CLAUDE.md` (universal habit) — never here.
