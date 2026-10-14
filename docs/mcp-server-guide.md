# MCP Server Guide

The API exposes its own MCP servers so users can work with their data from Claude and other MCP clients. Auth runs on Passport OAuth, so a client only needs the server URL.

This is separate from Boost's MCP server, which is local dev tooling for agents working on the codebase, see [Agent Guide](agent-guide.md).

## Servers

The API serves no HTML, so a frontend has to serve `/oauth/authorize` on `FRONTEND_APP_URL` and `FRONTEND_ADMIN_URL` before the login can finish.

Adding the servers as a connector through a client app like Claude Desktop won't work for local dev, since it doesn't work with `localhost` and requires https.

Add them manually with the CLI instead. The `--scope user` flag makes them available from any project or worktree. Without it the CLI defaults to local scope, which ties them to the directory you ran the command in. Either way the API on `localhost:8000` has to be running.

```bash
claude mcp add --scope user --transport http starter http://localhost:8000/mcp
claude mcp add --scope user --transport http starter-admin http://localhost:8000/admin/mcp
```

These land in Claude Code's own config, so they show up in Claude Code sessions, including the Code tab in Claude Desktop, but not in the general Desktop chat. That chat keeps a separate connector list and doesn't read servers added through the CLI.

List them to see their state. They show as needing authentication until you log in.

```bash
claude mcp list
```

Trigger the login manually. It opens the browser at the frontend's `/oauth/authorize` page, or add `--no-browser` to print the URL instead.

```bash
claude mcp login starter
claude mcp login starter-admin
```

Clients find the OAuth endpoints through two public discovery routes. They need no authentication, so they are a quick way to check the API is up and publishing the right URLs.

```bash
curl http://localhost:8000/.well-known/oauth-protected-resource/mcp
curl http://localhost:8000/.well-known/oauth-protected-resource/admin/mcp
curl http://localhost:8000/.well-known/oauth-authorization-server/mcp
curl http://localhost:8000/.well-known/oauth-authorization-server/admin/mcp
```

The first pair says which authorization server protects each MCP server. The second pair lists the authorize, token and registration endpoints.

## Inspector

The MCP Inspector is a web UI that connects to a server as a client, lists its tools, calls them and shows the raw JSON-RPC.

```bash
./dev inspector
```

It runs in the foreground, so give it its own terminal. Open the URL it prints, then connect to `http://php:8000/mcp` or `http://php:8000/admin/mcp`, using `php` rather than `localhost` since the connection is made from inside the container. It sends a pasted `Authorization: Bearer <token>` header, so it only works with the Sanctum setup. Ctrl+C stops it and removes the container.