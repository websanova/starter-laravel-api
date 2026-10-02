# Agent Setup

Agent files are generated and managed by Boost, so be mindful about editing them. Anything inside the `<laravel-boost-guidelines>` markers gets overwritten on the next run.

## Install and update

Comes with Claude out of the box (mainly for the commands) but just install via boost for whatever suits your needs.

```
php artisan boost:install
php artisan boost:update
```

## Commands

Not all agents support commands, currently only setup with Claude Code via `.claude/comands/` folder and a definition list in `CLAUDE.md`.

Most exist to gate what an agent can do in a turn, so brainstorming or summarising happens without touching files, and writes stay behind an explicit `/ex`. Kill or modify them as needed for your workflow.

One to be aware of is `/flow` which reads specs from the separate flows repo and compares them against this one rather than working from the code alone. It will need to know about where the flows repo live, which with Claude can be aded to a git ignored `CLAUDE.local.md` like so:

```
- Flows live at `/path/to/starter-flows/public/specs/flows`. This is an external directory, outside this project.
```

## Rules

Rules aren't universal. Cursor picks up `.ai/rules` natively by glob, but Claude has no such concept and only reaches them through the index import in the Boost guidelines. That's prose rather than a real load, so be wary of assuming they were picked up.
