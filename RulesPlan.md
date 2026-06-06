## Context

We restructured the project's CLAUDE.md to have two main project sections:
- `## Project` - high-level overview (stack, packages, what it is)
- `## Conventions` - all project conventions under `###` subsections (Routing, Namespacing, Enums, Controllers, Models, Requests, Resources, Rules, Services, Middleware, Policies, Observers, Notifications, Config, Commands, Migrations, Sorting, Tests)

The behavioral sections (Output, Token Efficiency, Typography, Sycophancy, Commands, etc.) remain as separate `##` sections.

## Decision: Split conventions into `.claude/rules/`

Based on Claude Code documentation:
- CLAUDE.md over ~200 lines reduces adherence. Ours is ~250.
- Path-scoped rules in `.claude/rules/` lazy-load only when Claude reads/writes matching files, keeping context cleaner.
- Rules and CLAUDE.md have identical weight - both are context. The benefit is context efficiency, not enforcement strength.
- Rules without `paths:` frontmatter load unconditionally, same as CLAUDE.md.

## Plan

1. Move the detailed `## Conventions` subsections into `.claude/rules/` as individual markdown files with `paths:` frontmatter scoped by directory glob (e.g., controllers rules scoped to `app/Http/Controllers/**/*.php`).

2. Keep a condensed summary of conventions in CLAUDE.md under `## Conventions` so Claude has general project awareness in `:pr` mode (thinking only) without needing to read specific files. This covers architecture questions like "where should I put X?" without glob-based rules firing.

3. Behavioral sections stay in CLAUDE.md unchanged (Output, Sycophancy, Typography, Commands, Code Output, etc.) - these are always-on and not file-specific.

4. Some convention topics may work better as unconditional rules (no `paths:` frontmatter) if they span too many directories or are needed for general decision-making (e.g., Routing, Namespacing, Enums).

## File structure

```
.claude/rules/
  controllers.md      -> paths: ["app/Http/Controllers/**/*.php"]
  models.md            -> paths: ["app/Models/**/*.php"]
  requests.md          -> paths: ["app/Http/Requests/**/*.php"]
  resources.md         -> paths: ["app/Http/Resources/**/*.php"]
  rules.md             -> paths: ["app/Rules/**/*.php"]
  services.md          -> paths: ["app/Services/**/*.php"]
  middleware.md        -> paths: ["app/Http/Middleware/**/*.php"]
  policies.md          -> paths: ["app/Policies/**/*.php"]
  observers.md         -> paths: ["app/Observers/**/*.php"]
  notifications.md     -> paths: ["app/Notifications/**/*.php"]
  config.md            -> paths: ["config/**/*.php"]
  commands.md          -> paths: ["app/Console/Commands/**/*.php"]
  migrations.md        -> paths: ["database/migrations/**/*.php"]
  tests.md             -> paths: ["tests/**/*.php"]
  routing.md           -> paths: ["routes/**/*.php"]
  enums.md             -> paths: ["app/Enums/**/*.php"]
```

The current full CLAUDE.md with all conventions is in the repo at `CLAUDE.md` - use it as the source for the detailed content that goes into each rule file.
