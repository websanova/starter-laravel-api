---
description: Summarize required changes. No code, no file changes.
argument-hint: [scope, optional]
disable-model-invocation: true
allowed-tools: Read, Grep, Glob
---

SUMMARIZE ONLY. List the changes required based on the current discussion. No coding, no file changes, no terminal commands.

## Output format

Group by file. One heading per file, path as a relative markdown link. Under each, bullets describing what changes. No code blocks, no diffs, no snippets. Describe the change, do not write it.

End with one line listing files to create and files to delete. Omit if none.

Only include changes actually agreed in this discussion. Do not add improvements, do not expand scope, do not include anything not discussed.

$ARGUMENTS
