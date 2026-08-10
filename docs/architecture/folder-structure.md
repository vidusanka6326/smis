# Folder Structure

```
smis/
├── app/
│   ├── Actions/           # Prefer for multi-step writes
│   ├── Services/          # Calculators, report builders
│   ├── Policies/          # Model authorization
│   ├── Enums/             # Backed enums for domain values
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Requests/      # Form Requests (all validation)
│   ├── Livewire/
│   ├── Models/
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
├── resources/views/
├── routes/
│   ├── web.php
│   └── (api.php — Phase 8)
├── tests/
│   ├── Feature/
│   └── Unit/
└── .github/workflows/tests.yml
```
