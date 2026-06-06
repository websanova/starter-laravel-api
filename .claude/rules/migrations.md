---
paths:
  - "database/migrations/**/*.php"
---

# Migrations

- Foreign keys use `cascadeOnDelete()` for owned resources (user's bookmarks, tags, categories cascade on user delete).
- `nullOnDelete()` for optional relationships (bookmark's `category_id` nulls when category deleted).
- Pivot tables cascade on both sides (`bookmark_tag`).
- Fulltext indexes are guarded with a `DB::getDriverName() !== 'sqlite'` check since SQLite doesn't support them. Tests run on SQLite, so the `Searchable` trait falls back from `MATCH ... AGAINST` to `LIKE` based on driver.
- Migrations numbered with a group prefix scheme (`0001_` for core/users, `0002_` for seeding, `0003_` for domain resources, etc.).
- Role/permission seeding runs as a migration calling a seeder class.
