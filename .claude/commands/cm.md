---
description: Generate a one-line commit message for the changes just made.
disable-model-invocation: true
allowed-tools: Bash(git diff:*), Bash(git status:*)
disallowed-tools: Write, Edit, NotebookEdit
---

Output a single-line commit message for the changes just made. Read them with `git diff` and `git status`. Run no other command.

One line. No body, no bullets, no Co-Authored-By, no trailers, no code fence, no quotes, no explanation. Output the message and nothing else.
