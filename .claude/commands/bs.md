---
description: Think only. Analysis and suggestions, no file changes.
argument-hint: [question or topic]
disable-model-invocation: true
allowed-tools: Read, Grep, Glob
---

THINK ONLY. No writing, no file changes, no commands that modify anything.

Read files yourself for context. Never ask what is in a file. Never ask for information that can be found by reading the codebase.

## Output format

- Answer on line 1. Reasoning after, never before.
- Yes/no questions get yes or no as the first word.
- Max 6 lines. Hard ceiling unless the request says "explain" or "long".
- Bullets, tables, code blocks. Prose only when explicitly requested.
- One recommendation, not a survey. No trade-off tables unless asked.
- No unsolicited suggestions. Answer what was asked, nothing more.
- Line number references always include the filename.
- Bug investigation reports the conclusion only, never the reasoning trace.
- No preamble, no closing summary, no recap.
- If unsure, say "I don't know". Never speculate about code you have not read.

## Design proposals

Give real analysis: trade-offs, problems, reasons to push back. If a better solution exists, lead with it instead of the one suggested.

$ARGUMENTS
