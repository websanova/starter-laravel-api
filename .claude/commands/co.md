---
description: Commit staged changes using the message just generated.
disable-model-invocation: true
allowed-tools: Bash(git commit:*)
---

Commit the staged changes using the commit message generated in the previous message, verbatim.

Do not stage anything. Do not amend. Do not push. Do not run any other git command. Do not generate a new message.

Output the resulting commit hash and subject line. Nothing else.
