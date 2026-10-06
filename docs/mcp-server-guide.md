# MCP Server Guide

Work in progress. The API exposes its own MCP servers so users can work with their data from Claude and other MCP clients. Auth currently runs on Sanctum bearer tokens, which is enough to test locally with Claude Code. The plan is to move to Passport OAuth so users can connect from any client without handling tokens themselves.

This is separate from Boost's MCP server, which is local dev tooling for agents working on the codebase, see [Agent Guide](agent-guide.md).

## Servers

There are two servers, mirroring the App and Admin split in the regular API.

- `/mcp` runs `AppServer`, for the authenticated user working with their own data.
- `/admin/mcp` runs `AdminServer`, for admins managing any user.

Both are registered in `routes/ai.php` with the same middleware stacks as their API counterparts, so a user who can't reach `/profile` or `/admin/users` can't reach the matching MCP server either. The package loads `routes/ai.php` outside the `api` middleware group, so `throttle:api` doesn't come along for free and is added to each server explicitly.

Route middleware applies to the whole server. Anything that should only gate some tools, like `subscribed` for bookmarks, goes in the tool's `shouldRegister()` instead, which hides the tool from users who don't qualify.

Servers live in `app/Mcp/Servers` and tools in `app/Mcp/Tools/App` and `app/Mcp/Tools/Admin`. Adding a capability is mostly writing the class and listing it on the server. Each server has a `HelloTool` that greets the authenticated user, which is handy for checking a connection end to end.

## Tools, resources and prompts

The difference is who decides to use them.

- Tools are picked by the model. They're functions with an input schema that Claude calls on its own while working, and they can read or write.
- Resources are picked by the user or client. They're read-only data behind a URI that gets attached as context, and the model doesn't fetch them on its own.
- Prompts are picked by the user. They're reusable prompt templates with arguments, which Claude Code shows as slash commands.

Most of what this API offers is tools. Client support for resources varies, so anything the model should be able to look up by itself needs to be a tool.

## Descriptions

The client doesn't route tool calls, the model does. When a conversation starts, the client loads every connected server's tool names, descriptions and input schemas into context, and the model matches the request against those descriptions. Tool names are prefixed by server (`mcp__starter__hello-tool`, `mcp__starter-admin__hello-tool`), so two servers can share a tool name without clashing. With lots of connectors, Claude Code defers the full definitions and has the model search for a tool before loading it.

That makes the `#[Description]` on each tool and the `#[Instructions]` on each server what decides whether the right tool gets picked, especially when a user has several MCP connectors installed. Write them like API docs for the model. Say what the tool does, when to use it and when not to. A `list-bookmarks` tool should say it only returns the current user's bookmarks, so the model doesn't reach for it on an admin request.

## Sanctum vs Passport

OAuth 2.1 is the auth the MCP spec documents and what most clients expect. Connectors in claude.ai and Claude Desktop authenticate through OAuth and don't take a custom header. With OAuth a user pastes the server URL, logs in and approves in the browser, and they're connected. That's the experience the starter is aiming for, so Passport is the target.

Sanctum works today because the servers are protected with plain route middleware, so any client that can send an `Authorization` header can connect. Claude Code can, which makes it the quickest way to check the servers and guards before OAuth adds more moving parts. The catch is tokens come from `/login` and expire after `SANCTUM_TOKEN_EXPIRATION` (7 days by default), so a token pasted into a client config stops working every week.

## Example in Claude

Manual setup with Sanctum, using the seeded users. Log in as a regular user and as an admin.

```bash
curl -s -X POST http://localhost:8000/login -H "Content-Type: application/json" -d '{"email":"small@starter.com","password":"testtest"}'
curl -s -X POST http://localhost:8000/admin/login -H "Content-Type: application/json" -d '{"email":"admin@starter.com","password":"testtest"}'
```

Copy the `token` field from each response, the one shaped like `{id}|{token}`. In local dev the response also carries a `queries` block, and the `insert into personal_access_tokens` binding in there is a 64 character hex string. That's the hash Sanctum stores, not the token, and using it gets a 401 on every request.

Add both servers to Claude Code from the project directory.

```bash
claude mcp add --transport http starter http://localhost:8000/mcp --header "Authorization: Bearer <user token>"
claude mcp add --transport http starter-admin http://localhost:8000/admin/mcp --header "Authorization: Bearer <admin token>"
```

This stores them for this project only, in `~/.claude.json`. Don't put them in `.mcp.json`, it's committed and the token would go with it. Replacing a token means removing the server with `claude mcp remove starter` and adding it again.

If `claude` isn't on your path, the desktop app bundles a copy that can be run by full path. On Linux it's at `~/.config/Claude/claude-code/{version}/claude`.

Servers load when a conversation starts, so open a new one, then ask Claude to call the hello tool on `starter` and on `starter-admin`. Each should greet the account its token belongs to. To check the admin guard, swap the admin server's token for the user one and the call should fail with a 403.

## Testing

The package's `Server::tool()` test helper calls the tool directly and skips route middleware, so it can't prove the guards work. The tests in `tests/Feature/App/Mcp` and `tests/Feature/Admin/Mcp` POST a JSON-RPC `tools/call` to the actual route instead, which runs the full middleware stack the same way a client would.
