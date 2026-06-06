---
paths:
  - "app/Policies/**/*.php"
---

# Policies

- Used for admin-side authorization where permission checks are granular (specific permissions like `users.manage`, `users.assign-role`).
- `before()` method grants super users unconditional access.
- Protect against privilege escalation (admins cannot modify super users, cannot delete other admins).
- Account-side routes don't use policies since ownership checks are simpler and handled in requests.
