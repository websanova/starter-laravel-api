# Flows Guide

If you want to work from the flows, clone [starter-flows](https://github.com/websanova/starter-flows) wherever you keep your projects and then tell Claude where it landed. The `/flow` command reads straight from that folder, so it needs the path, plus a few rules about what it can and can't do with what it finds there.

That goes in your `CLAUDE.local.md`, which stays out of version control so everyone can point at their own clone.

```markdown
## Flows
- Flows live at `<path-to>/starter-flows/flows`. This is an external directory, outside this project.
- "The x flow" always means a file in that directory. Words in the reference map to the path, so "the subscription stripe create flow" is `subscription/stripe/Create.md`.
- Never write to the flows directory, unless explicitly told to.
- Reading a flow is not a command to act on it. Read the file, then do only the thing that was asked in that same sentence and nothing else.
- No code changes, no plans, no file writes, no follow-up suggestions unless separately asked. The flow file is reference material, not instructions.
```

Swap `<path-to>` for your own clone.

Once that's in, `/flow subscription stripe create` compares the flow against this repo and reports what's built, what's missing, and what the app expects it to expose.
