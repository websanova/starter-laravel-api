---
paths:
  - "app/Http/Resources/**/*.php"
---

# Resources

- API responses always use Laravel API Resources (`app/Http/Resources/`). Never return raw model arrays.
- Namespaced by group: `Resources/Account/BookmarkResource`, `Resources/Admin/BookmarkResource`. Same resource name can exist in both with different fields (admin includes `user_id`, account doesn't).
- A `Public` namespace exists for unauthenticated responses (`Resources/Public/PlanResource`, which maps `prices` to `interval => amount`).
