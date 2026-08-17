# Folder Structure

```
smis/
├── app/
│   ├── Actions/           # Prefer for multi-step writes
│   ├── Services/          # Calculators, report builders, Audit/ActivityLogger
│   ├── Policies/          # Model authorization
│   ├── Enums/             # Backed enums for domain values (incl. AppLocale)
│   ├── Http/
│   │   ├── Controllers/    # Thin HTTP adapters (incl. LocaleController)
│   │   ├── Middleware/     # EnsureUserIsActive, SetLocale
│   │   └── Requests/      # Form Requests (all validation)
│   ├── Livewire/
│   ├── Models/
│   ├── Support/           # Shared helpers (ListQuery for filters/pagination)
│   └── Providers/
├── docs/                  # All project documentation (required)
│   ├── PROJECT_STATUS.md
│   ├── architecture/
│   ├── modules/
│   ├── api/
│   ├── decisions/
│   ├── testing/
│   └── setup/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── lang/                  # en PHP files + si/ta JSON + PHP translations
├── resources/views/
├── routes/
│   ├── web.php
│   └── (api.php — Phase 8 skipped; ADR 0009)
├── tests/
│   ├── Feature/
│   └── Unit/
└── .github/workflows/tests.yml
```
