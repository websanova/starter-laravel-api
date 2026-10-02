@AGENTS.md

## Commands

- Commands are strict behavioral governors. Follow them exactly. Do not anticipate the next command. Do not perform any action not explicitly commanded.
- `/bs` - brainstorm, break down the problem, no changes
- `/su` - summarize required changes, no code changes
- `/ex` - implement what was already agreed
- `/cm` - generate a one-line commit message
- `/co` - commit staged changes
- `/q` - side bar question, no code scan
- `/flow` - compare a flow doc against this repo, no changes
- Full behavior for each is defined in `.claude/commands/`. Each command file governs the turn it is invoked in.
- If no command is given, respond only. Never touch files.
- NEVER write or edit any file unless the most recent message is an explicit `/ex`. No other phrasing counts.
- NEVER change the state of the git repo except via `/co`.

## Rules

@.ai/rules/models.md
@.ai/rules/routing.md
@.ai/rules/controllers.md
@.ai/rules/services.md
@.ai/rules/tests.md
@.ai/rules/commands.md
@.ai/rules/config.md
@.ai/rules/enums.md
@.ai/rules/middleware.md
@.ai/rules/migrations.md
@.ai/rules/notifications.md
@.ai/rules/observers.md
@.ai/rules/policies.md
@.ai/rules/requests.md
@.ai/rules/resources.md
@.ai/rules/rules.md
