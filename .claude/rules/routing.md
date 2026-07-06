---
paths:
  - "routes/**/*.php"
---

# Routing

- Two namespaces:
  - **App** (default, no prefix) - authentication flows plus the authenticated user managing themselves (`POST /login`, `POST /register`, `GET /profile`, `GET /bookmarks`)
  - **`/admin`** - admin managing any resource (`GET /admin/users`, `PATCH /admin/users/{id}`)
- Unauthenticated routes sit at root alongside the guest App routes: `GET /up` (health), `POST /stripe/webhook` (Cashier), `GET /plans` (public plan listing).
- The App root has both guest routes (login, register, password reset) and authenticated routes (logout, token refresh, profile, resources).
- The App root and `/admin` groups each declare their own middleware explicitly.
- REST convention: nested resources for direct ownership (`/admin/users/{user}/bookmarks`) rather than flat with query filters (`/admin/bookmarks?user_id=`). Both styles can coexist if a flat filter endpoint is needed, but nested is the default for direct parent-child access.
- `scopeBindings()` on nested resource routes to ensure child belongs to parent.
- `withTrashed()` on routes that need to resolve soft-deleted models.
- Stack order on the authenticated App routes: `auth:sanctum`, `track-active`, then `verified` and `password-updated` on inner routes. Gated resources (bookmarks, categories, tags) add `subscribed` middleware.
- Stack order on `/admin`: `auth:sanctum`, `track-active`, `verified`, `password-updated`, `admin`.
- Subscription routes: `/subscription` (CRUD + resume) for self-management, `/admin/users/{user}/subscription` (CRUD + resume) for admin management. `/admin/plans` for plan CRUD, with `PATCH /admin/plans/{plan}/prices/{price}` (scoped binding) for price updates.
- Notification routes: `GET /notifications` (list, filterable by `read`), `PATCH /notifications/{notification}` (mark read/unread), `POST /notifications/read` (mark all read).
- Non-CRUD actions use `POST` with a descriptive sub-path (e.g., `/notifications/read`) routed to a single-action controller.
