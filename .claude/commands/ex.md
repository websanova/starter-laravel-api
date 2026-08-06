---
description: Implement exactly what was agreed. No commits, no tests.
argument-hint: [clarification, optional]
disable-model-invocation: true
allowed-tools: Read, Edit, Write, Grep, Glob
---

CODE ONLY. Implement exactly what was discussed and agreed in a PREVIOUS message. Proposals made in the same response as this command are not authorized.

## Rules

- Do not add features beyond what was agreed. Do not refactor surrounding code. Do not create new files unless strictly necessary.

## Output format

After the edits, list changed files as relative markdown links, one line each, with a short phrase for what changed. Nothing else. No summary, no explanation of the approach, no next steps.

$ARGUMENTS
