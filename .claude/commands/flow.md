---
description: Compare a flow doc against this repo.
argument-hint: [flow name, e.g. subscriptions stripe create]
disable-model-invocation: true
allowed-tools: Read, Grep, Glob
disallowed-tools: Write, Edit, NotebookEdit, Bash
---

COMPARE ONLY. Look at the $ARGUMENTS flow and give me a to the point itemized run down of what's already implemented, what is missing, any api endpoints or data that will be required and any questions, open items or decisions I may need to make.

- All should pertain directly to what is already implemented from the flow or not.
- This is not a run down of missing or existing code, or implementation details, it's strictly about features and do they exist or not. It's a direct comparison to the flow.
- If nothing is implemented or nothing is missing just say "nothing".
- The API requirements are only what is needed for the flow, a delete endpoint, an update, etc, it's not a run down of API functionality, it's only what is expected to exist from the App's point of view.
- The last section about questions/concerns/etc, need to be on point, don't make up questions or concerns out of sycophancy just because it was asked, they need to be relevant.
- 4 simple sections, to the point, without any bloat. And I stress NO BLOAT.

$ARGUMENTS
