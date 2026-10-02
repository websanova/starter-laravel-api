---
paths:
  - "app/Observers/**/*.php"
---

# Observers

- Used for cross-cutting concerns that apply to multiple models (e.g., `SearchableObserver` updates keywords for any model using the `Searchable` trait).
- Simple model-specific lifecycle hooks use `boot()` in the model instead (e.g., Tag slug generation).
- Registered via `#[ObservedBy]` attribute on the model.
