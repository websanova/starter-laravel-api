# TODO

## Auth
- [ ] Register, login, logout, refresh token (Sanctum)
- [ ] Email verification, password reset
- [ ] Rate-limited login attempts

## Subscriptions
- [ ] Stripe via Laravel Cashier
- [ ] Plans, trial periods, webhook handling
- [ ] Middleware to gate features by plan tier

## Roles & Permissions
- [ ] Role-based (admin, user) with granular permissions
- [ ] Spatie laravel-permission or lightweight custom implementation
- [ ] Middleware for route-level gating

## Account Management
- [ ] Profile update (name, email, password)
- [ ] Avatar upload with image processing (S3/local storage)
- [ ] Account deletion

## Admin vs Account Scope
- [ ] Admin: full user list, user CRUD, impersonation, system stats
- [ ] Account: own profile only, own data only
- [ ] Separate route groups with middleware enforcement

## Bookmarks/Links Manager
- [ ] CRUD (url, title, description)
- [ ] Folders/categories for organization
- [ ] Tags (polymorphic, reusable across future models)
- [ ] Favoriting, archive/restore
- [ ] Sort, filter, search
- [ ] Demonstrates: CRUD, relationships, soft deletes, filtering, pagination, polymorphic relations
