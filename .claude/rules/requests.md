---
paths:
  - "app/Http/Requests/**/*.php"
---

# Requests

- Namespaced by group and resource: `Requests/Account/Profile/UpdateRequest.php`, `Requests/Auth/Login/StoreRequest.php`, `Requests/Admin/User/UpdateRequest.php`.
- Account requests do ownership checks inline in `authorize()` (e.g., `$this->route('bookmark')->user_id === $this->user()->id`). No policies needed since it's always a simple "does the user own this resource" check.
- Admin requests delegate to policies via `$this->user()->can('view', $this->route('user'))` for fine-grained permission checks.
- Index requests override `validated()` to cast string inputs to their enum types (`BookmarkSort::from(...)`, `SortDirection::from(...)`) so controllers receive typed values.
