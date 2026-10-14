# MCP Server Guide

The API exposes its own MCP servers so users can work with their data from Claude and other MCP clients. Auth runs on Passport OAuth, so a client only needs the server URL.

This is separate from Boost's MCP server, which is local dev tooling for agents working on the codebase, see [Agent Guide](agent-guide.md).

## Servers

The API serves no HTML, so a frontend has to serve `/oauth/authorize` on `FRONTEND_APP_URL` and `FRONTEND_ADMIN_URL` before the login can finish.

Add the servers as a connector through a client app like Claude Desktop, or directly with the CLI.

```bash
claude mcp add --transport http starter http://localhost:8000/mcp
claude mcp add --transport http starter-admin http://localhost:8000/admin/mcp
```

## Inspector

The MCP Inspector is a web UI that connects to a server as a client, lists its tools, calls them and shows the raw JSON-RPC.

```bash
./dev inspector
```

It runs in the foreground, so give it its own terminal. Open the URL it prints, then connect to `http://php:8000/mcp` or `http://php:8000/admin/mcp`, using `php` rather than `localhost` since the connection is made from inside the container. It sends a pasted `Authorization: Bearer <token>` header, so it only works with the Sanctum setup. Ctrl+C stops it and removes the container.