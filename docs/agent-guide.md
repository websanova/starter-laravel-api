# Agent Setup

Agent files are generated and managed by Boost, so be mindful about editing them. Anything inside the `<laravel-boost-guidelines>` markers gets overwritten on the next run.

## Install and update

Comes with Claude out of the box (mainly for the commands) but just install via boost for whatever suits your needs.

```
php artisan boost:install
php artisan boost:update
```

## MCP

Local dev only. Boost runs an MCP server that hands tools to the client (Claude Code, Cursor, etc) for querying the database, inspecting schema and searching package docs. None of it relates to exposing an MCP server to your own users, which is a separate thing entirely.

Running `boost:install` writes the config to `.mcp.json`, but it assumes PHP on your host. With `sail: false` in `boost.json` and a hand-rolled compose setup there's nothing for it to detect, so you have to point it at the container yourself.

```json
{
    "mcpServers": {
        "laravel-boost": {
            "command": "docker",
            "args": ["compose", "exec", "-T", "php", "php", "artisan", "boost:mcp"]
        }
    }
}
```

The `-T` matters. MCP talks JSON-RPC over stdin and stdout, so allocating a TTY corrupts the stream. It's also the one place `./dev artisan` won't substitute, since the script omits `-T` for interactive use.

What happens when you open a conversation.

1. The client looks for `.mcp.json` in the project directory and parses it.
2. First run only, it asks you to approve `laravel-boost`. The answer is remembered per project.
3. It spawns `docker compose exec -T php php artisan boost:mcp` as a child process on your machine.
4. That `exec` reaches into the running container and starts `boost:mcp` inside it.
5. The client sends `initialize` then `tools/list`, and `boost:mcp` answers with its tool definitions.
6. The tools show up prefixed, `mcp__laravel-boost__database-query` and the rest.

Each conversation spawns its own, so three conversations open on this project means three `boost:mcp` processes running in the container.

Containers need to be up first, see [Docker Guide](docker-guide.md). Start a conversation before running `./dev up` and there's no container to connect to, so boost won't load for it. Run `./dev up`, then open a new conversation.

Same for edits to `.mcp.json`. The file is read when a conversation starts, so open a new conversation to pick up changes. Ones already open keep the config they started with.

Same applies to the CLI, where each `claude` run is its own conversation.

## Commands

Not all agents support commands, currently only setup with Claude Code via `.claude/commands/` folder and a definition list in `CLAUDE.md`.

Most exist to gate what an agent can do in a turn, so brainstorming or summarising happens without touching files, and writes stay behind an explicit `/ex`. Kill or modify them as needed for your workflow.

One to be aware of is `/flow` which reads specs from the separate flows repo and compares them against this one rather than working from the code alone. It will need to know about where the flows repo lives, which with Claude can be added to a git ignored `CLAUDE.local.md` like so:

```
- Flows live at `/path/to/starter-flows/public/specs/flows`. This is an external directory, outside this project.
```

## Rules

Rules aren't universal. Cursor picks up `.ai/rules` natively by glob, but Claude has no such concept. The Boost guidelines import `@.ai/rules/index.md`, so the index does get loaded for real, but the rule files it points at don't. Getting to those is just an instruction the agent is asked to follow, so be wary of assuming they were picked up.
