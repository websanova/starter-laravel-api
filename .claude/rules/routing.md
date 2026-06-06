---
paths:
  - "routes/**/*.php"
---

# Routing

- Three route prefixes:
  - **`/auth`** - authentication flows (`POST /auth/login`, `POST /auth/register`, `POST /auth/logout`)
  - **`/account`** - authenticated user managing themselves (`GET /account/profile`, `PATCH /account/profile`, `GET /account/bookmarks`)
  - **`/admin`** - admin managing any resource (`GET /admin/users`, `PATCH /admin/users/{id}`)
- `/auth` has both guest routes (login, register, password reset) and authenticated routes (logout, token refresh).
- `/account` and `/admin` groups each declare their own middleware explicitly.
- REST convention: nested resources for direct ownership (`/admin/users/{user}/bookmarks`) rather than flat with query filters (`/admin/bookmarks?user_id=`). Both styles can coexist if a flat filter endpoint is needed, but nested is the default for direct parent-child access.
- `scopeBindings()` on nested resource routes to ensure child belongs to parent.
- `withTrashed()` on routes that need to resolve soft-deleted models.
- Stack order on `/account`: `auth:sanctum`, `track-active`, then `verified` and `password-updated` on inner routes. Gated resources (bookmarks, categories, tags) add `subscribed` middleware.
- Stack order on `/admin`: `auth:sanctum`, `track-active`, `verified`, `password-updated`, `admin`.
- Subscription routes: `/account/subscription` (CRUD + resume) for self-management, `/admin/users/{user}/subscription` (CRUD + resume) for admin management. `/admin/plans` for plan CRUD.
