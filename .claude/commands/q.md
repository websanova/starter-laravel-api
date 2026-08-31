---
description: Side bar question. General knowledge, no code scan.
argument-hint: [question]
disable-model-invocation: true
allowed-tools: WebSearch, WebFetch
disallowed-tools: Write, Edit, NotebookEdit, Bash, Read, Grep, Glob
---

ANSWER ONLY. This is a general question, unrelated to the codebase.

Answer from model knowledge first. Fall back to a web lookup only when the answer is not known, or when it is version dependent and may have changed.

## Rules

- Do not reference, infer about, or answer in terms of the current project. The repo is not the subject.
- Say "I don't know" rather than guessing. Never guess confidently.
- Cite sources as markdown links when the web was used. Omit sources entirely when answering from model.
- No preamble, no closing summary, no recap.
- Compressed. Every sentence must earn its place.

$ARGUMENTS
