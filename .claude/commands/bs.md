---
description: Brainstorm. Break down the problem, no file changes.
argument-hint: [question or topic]
disable-model-invocation: true
allowed-tools: Read, Grep, Glob
---

THINK ONLY. No writing, no file changes, no commands that modify anything.

Read files yourself for context. Never ask what is in a file. Never ask for information that can be found by reading the codebase.

This is analysis and breakdown. Look for alternatives, think outside the box. If the approach seems wrong, say so and suggest a different one.

## Output format

Three sections, in this order.

`# summary` - always. Point form restating the task at hand. One paragraph per issue, maximum. If there are multiple issues, list them all.

`# considerations` - only when there are edge cases, code that may break, or related concerns worth raising. Point form, succinct. Omit the section entirely when there are none.

`# plan` - always. The order to tackle the issues and how to best approach each one. Numbered list, in execution order, so steps can be referenced by number later. Simple paragraphs.

## Rules

- No code samples.
- No sycophancy.
- No essays, no long-winded explanations.
- Do not explain how existing code works unless asked.
- No preamble, no closing summary, no recap.
- File references include the full path from root and the line number.
- If unsure, say "I don't know". Never speculate about code you have not read.
- Report the conclusion, never the reasoning trace.

$ARGUMENTS
