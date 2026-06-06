---
paths:
  - "app/Http/Controllers/**/*.php"
---

# Controllers

- Thin controllers. Filtering/sorting/search delegated to model scopes. Authorization delegated to requests. Response shaping delegated to resources.
- Always return resources, never raw models. Collections use `->response()->getData(true)` to include pagination meta.
- Single-item responses wrap in `['data' => new Resource($model)]`.
- 201 for creates, 204 (null body) for deletes, 200 for everything else.
- Controllers organized under `Auth/`, `Account/`, and `Admin/` namespaces. No controllers in the root `Controllers/` directory (except `Controller.php` base class).
- `/auth` routes use `Auth/` namespace: `Controllers/Auth/LoginController`.
- `/account` routes use `Account/` namespace: `Controllers/Account/ProfileController`.
- `/admin` routes use `Admin/` namespace: `Controllers/Admin/UserController`.
