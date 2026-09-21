---
description: Generate a one-line commit message for the changes just made.
disable-model-invocation: true
allowed-tools: Bash(git diff:*), Bash(git status:*)
disallowed-tools: Write, Edit, NotebookEdit
---

MESSAGE ONLY. Output a single-line commit message for the changes just made.

- Read the changes with `git diff` and `git status`. Run no other command.
- One line. No body, no bullets.
- No Co-Authored-By, no trailers, no code fence, no quotes.
- Output the message and nothing else. No explanation.
