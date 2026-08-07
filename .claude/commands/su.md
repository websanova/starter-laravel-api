---
description: Summarize required changes. No code changes.
argument-hint: [instruction, optional]
disable-model-invocation: true
allowed-tools: Read, Grep, Glob
---

SUMMARIZE ONLY. List the changes required based on the current discussion. No coding, no source file changes, no terminal commands.

## Modes

Bare `/su` regenerates the list from the current discussion.

`/su <instruction>` applies the instruction to the list from the previous `/su` output in this conversation and reprints the whole list. Referenced numbers refer to that last printed list.

## Output format

A plain markdown numbered list. One flat list, numbering continuous across the whole list, never restarting. Each item names its file path inline and describes the change in a sentence or two. No code blocks, no diffs, no snippets. Describe the change, do not write it.

Files to create and files to delete go in a single line after the list. Omit if none.

## Rules

- Only include changes actually agreed in this discussion. Do not add improvements, do not expand scope, do not include anything not discussed.
- The list is the chat output. Nothing else around it.

$ARGUMENTS
