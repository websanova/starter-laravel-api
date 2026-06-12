---
paths:
  - "tests/**/*.php"
---

# Tests

- Pest with `RefreshDatabase` on all feature tests.
- Mirror route group structure: `tests/Feature/Account/Bookmark/IndexTest.php`, `tests/Feature/Admin/User/ShowTest.php`, `tests/Feature/Auth/Login/StoreTest.php`.
- Console commands: `tests/Feature/Console/PruneDeletedUsersTest.php`.
- Services: `tests/Feature/Services/{Service}Test.php` (e.g., `tests/Feature/Services/PlanSyncServiceTest.php`), group tag `service.{kebab-name}` (e.g., `service.plan-sync`).
- Each test file sets `uses()->group('prefix.resource.action')` (e.g., `account.bookmark.index`, `admin.user-role.update`).
- Test descriptions read as behavioral assertions: `test('user can list their bookmarks')`, `test('unauthenticated user cannot list bookmarks')`.
