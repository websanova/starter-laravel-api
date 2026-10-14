# MCP Server Guide

The API exposes its own MCP servers so users can work with their data from Claude and other MCP clients. Auth runs on Passport OAuth, so a client only needs the server URL.

This is separate from Boost's MCP server, which is local dev tooling for agents working on the codebase, see [Agent Guide](agent-guide.md).

## Servers

There are two servers, mirroring the App and Admin split in the regular API.

- `/mcp` runs `AppServer`, for the authenticated user working with their own data.
- `/admin/mcp` runs `AdminServer`, for admins managing any user.

The paths are set in `config/mcp.php`. Both are registered in `routes/ai.php` with the same middleware stacks as their API counterparts, so a user who can't reach `/profile` or `/admin/users` can't reach the matching MCP server either. The package loads `routes/ai.php` outside the `api` middleware group, so `throttle:api` doesn't come along for free and is added to each server explicitly.

Route middleware applies to the whole server. Anything that should only gate some tools, like `subscribed` for bookmarks, goes in the tool's `shouldRegister()` instead, which hides the tool from users who don't qualify.

Servers live in `app/Mcp/Servers` and tools in `app/Mcp/Tools/App` and `app/Mcp/Tools/Admin`. Adding a capability is mostly writing the class and listing it on the server. Each server has a `HelloTool` that greets the authenticated user, for checking a connection end to end.

## Tools, resources and prompts

The difference is who decides to use them.

- Tools are picked by the model. They're functions with an input schema that Claude calls on its own while working, and they can read or write.
- Resources are picked by the user or client. They're read-only data behind a URI that gets attached as context, and the model doesn't fetch them on its own.
- Prompts are picked by the user. They're reusable prompt templates with arguments, which Claude Code shows as slash commands.

Most of what this API offers is tools. Client support for resources varies, so anything the model should be able to look up by itself needs to be a tool.

## Descriptions

The client doesn't route tool calls, the model does. When a conversation starts, the client loads every connected server's tool names, descriptions and input schemas into context, and the model matches the request against those descriptions. Tool names are prefixed by server (`mcp__starter__hello-tool`, `mcp__starter-admin__hello-tool`), so two servers can share a tool name without clashing. With lots of connectors, Claude Code defers the full definitions and has the model search for a tool before loading it.

That makes the `#[Description]` on each tool and the `#[Instructions]` on each server what decides whether the right tool gets picked, especially when a user has several MCP connectors installed. Write them like API docs for the model. Say what the tool does, when to use it and when not to. A `list-bookmarks` tool should say it only returns the current user's bookmarks, so the model doesn't reach for it on an admin request.

## OAuth

OAuth 2.1 is the auth the MCP spec documents and what most clients expect. Connectors in claude.ai and Claude Desktop authenticate through OAuth and don't take a custom header.

What happens when a client connects.

1. The client calls the server without a token and gets a 401 pointing at the resource metadata.
2. It reads the discovery metadata under `/.well-known`, which names the authorization server and its endpoints. Each MCP server is its own authorization server, so the app and admin servers don't share a login.
3. It registers itself at `/oauth/register`, so there's no client to create ahead of time. The route is public, which is why it gets the `auth` throttle. The `redirect_domains` in `config/mcp.php` limit where a client can send the user back to, and ship with claude.ai, claude.com and localhost on any port for Claude Code.
4. It opens the browser at the frontend's `/oauth/authorize` page, on `FRONTEND_APP_URL` for the app server and `FRONTEND_ADMIN_URL` for the admin one. The API serves no HTML, so the frontend logs the user in, shows the consent screen from `GET /oauth/consent` and posts the decision to `POST /oauth/consent`. The response carries the `redirect_uri` to send the browser on to.
5. The client trades the code for tokens using PKCE. Access tokens last an hour and refresh tokens 30 days.

Passport authenticates its own `OAuthUser` model, so the `oauth-user` middleware swaps it for the real `User` before anything else runs. That keeps `verified`, `password-updated` and `admin` working the same as on the API. A soft deleted account isn't found there and gets a 401.

### Testing locally

Passport needs its signing keys, which are git ignored, so a fresh clone has to generate them once.

```bash
./dev artisan passport:keys
```

The authorize page belongs to the frontend, so the flow can't finish until a frontend is serving `/oauth/authorize` on the URLs in `.env`. Hosted connectors in claude.ai call the server from the internet and can't reach `localhost`, so Claude Code is the way to test locally. Add both servers from the project directory, no header needed.

```bash
claude mcp add --transport http starter http://localhost:8000/mcp
claude mcp add --transport http starter-admin http://localhost:8000/admin/mcp
```

This stores them for this project only, in `~/.claude.json`. If `claude` isn't on your path, the desktop app bundles a copy that can be run by full path. On Linux it's at `~/.config/Claude/claude-code/{version}/claude`.

Servers load when a conversation starts, so open a new one and run `/mcp` to authenticate each server. The browser opens on the frontend login, and once approved the tools are available. Ask Claude to call the hello tool on `starter` and on `starter-admin`. Each should greet the account that approved it. To start over, `claude mcp remove starter` and add it again.

## Sanctum

Don't use this. It's possible, but it's manual and the token expires. Tokens come from `/login`, last `SANCTUM_TOKEN_EXPIRATION` (7 days by default), and have to be pasted into the client config by hand. Hosted clients can't send a custom header at all. It only checks the servers and guards from Claude Code without a frontend.

The servers are protected by `auth:api` and `oauth-user`, which only accept Passport tokens. To use Sanctum, swap those two for `auth:sanctum` on the server in `routes/ai.php`, then log in as a regular user and as an admin using the seeded accounts.

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

Servers load when a conversation starts, so open a new one, then ask Claude to call the hello tool on `starter` and on `starter-admin`. Each should greet the account its token belongs to. To check the admin guard, swap the admin server's token for the user one and the call should fail with a 403.

## Inspector

The MCP Inspector is a web UI that connects to a server as a client, lists its tools, calls them and shows the raw JSON-RPC going back and forth. It separates a server problem from a client config problem.

`php artisan mcp:inspector` is only a launcher that runs `npx @modelcontextprotocol/inspector`, so it needs PHP and Node in the same place. Neither the host nor the php container has both, so the Inspector runs in its own Node container behind a compose profile instead.

```bash
./dev inspector
```

It stays out of `./dev up` since it's a debug tool, and it generates a new session token on every start. It runs in the foreground, so give it its own terminal. Ctrl+C stops it and removes the container. Nothing survives a restart, so servers and tokens have to be entered again each time.

Open the URL it prints, which carries the Inspector's session token, then connect to `http://php:8000/mcp` or `http://php:8000/admin/mcp` with an `Authorization: Bearer <token>` header. That's a pasted token, so it only works with the Sanctum setup above. It's `php` rather than `localhost` because the connection is made from inside the Inspector container.

The port is bound to `127.0.0.1` only, since the page embeds that session token. Inside the container the Inspector has to listen on all interfaces, which it refuses to do without `DANGEROUSLY_BIND_ALL_INTERFACES`, so that's set in the compose file.

## Testing

The package's `Server::tool()` test helper calls the tool directly and skips route middleware, so it can't prove the guards work. The tests in `tests/Feature/App/Mcp` and `tests/Feature/Admin/Mcp` POST a JSON-RPC `tools/call` to the actual route instead, which runs the full middleware stack the same way a client would.
