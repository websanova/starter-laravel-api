---
description: Summarize required changes to the side panel list. No code changes.
argument-hint: [instruction, optional]
disable-model-invocation: true
allowed-tools: Read, Grep, Glob, Write, Edit, SendUserFile
---

SUMMARIZE ONLY. List the changes required based on the current discussion. No coding, no source file changes, no terminal commands.

The list lives at `.claude/tmp/su.html`. That is the only path this command may ever write. Never write or edit any other file.

## Modes

Bare `/su` regenerates the list from the current discussion and overwrites the file completely.

`/su <instruction>` reads the existing file, applies the instruction to it, and rewrites it. Referenced numbers refer to the list currently in the file.

## Output format

Write a self-contained HTML file. One flat numbered list, numbering continuous across the whole list, never restarting. Each item names its file path inline and describes the change in a sentence or two. No code blocks, no diffs, no snippets. Describe the change, do not write it.

Files to create and files to delete go in a single line after the list. Omit if none.

Minimal styling. No scripts, no external assets.

After writing, send the file to the side panel with `display: render`. It replaces the panel each time, so the panel always shows the latest list and nothing else.

## Rules

- Only include changes actually agreed in this discussion. Do not add improvements, do not expand scope, do not include anything not discussed.
- Chat output is a single pointer line. The list goes in the panel, never in chat.

$ARGUMENTS
