---
paths:
  - "app/Enums/**/*.php"
---

# Enums

- All defined option sets go through enums in `app/Enums/`.
- Sort columns per resource: `UserSort`, `BookmarkSort`, `CategorySort`, `TagSort`.
- Sort direction: `SortDirection` (`asc`, `desc`).
- Roles: `UserRole` (`super`, `admin`).
- Config-driven modes: `VerificationMode` (`disabled`, `auto`, `required`), `AccountPruneStrategy` (`delete`, `anonymize`).
- Admin filters: `TrashedFilter` (`only`, `with`).
- Storage paths: `StoragePath` centralizes file storage path prefixes (e.g., `UserAvatar = 'users/avatars'`).
- Subscription: `SubscriptionMode` (`freemium`, `trial`, `required`), `PlanTier` (`Free`, `Pro`), `PlanInterval` (`Monthly`, `Yearly`), `PlanFeature` (`Bookmarks`, `Categories`, `Tags` with `relation()` and `isCountable()` methods), `PlanSort` (`Name`, `SortOrder`, `CreatedAt`).
