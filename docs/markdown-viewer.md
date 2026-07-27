# Markdown Viewer

Renders anything in `docs/` in the browser, with live mermaid diagrams. Local dev tool, not part of the API.

## Setup

`docs/` sits outside the web root, so it needs a symlink before anything will load. Run this once:

```bash
docker compose exec php ln -sfn ../../../docs storage/app/public/docs
```

## Viewing

Load up the viewer with a doc query param.

```
http://localhost:8000/markdown.html?doc=subscriptions.md
```
