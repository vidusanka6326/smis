# Shared list filters and pagination

## Context

Index screens mixed Laravel’s default Tailwind paginator with ad-hoc GET forms. Students had rich filters; most other lists (teachers, classes, exams, officers, marks) did not. With a 600-student demo school, unfiltered tables and default gray pagination were unusable.

## Decision

Use one GET-based list kit across admin, teacher, and student indexes:

- `x-list.filters` — one row: search + dropdowns, a narrow per-page select (10/20/50/100), then Apply / Clear
- `x-list.table` — shared bordered table chrome
- `x-list.pagination` + `pagination.flux` — “Showing X to Y of Z” with HTTP page links (not Livewire `wire:click`)
- `App\Support\ListQuery` — trim/drop empty filters, cap `per_page`, `paginate()` / `paginateCollection()`
- Eloquent `scopeFilter()` on list models

Mark-entry rosters stay unpaginated so a full class can still be saved in one request.

## Consequences

- Filter query strings survive pagination (`withQueryString()`).
- Report “all students” tables paginate after aggregation; CSV still exports the full set.
- Flux Pro table/pagination Livewire wiring is not used; lists remain controller + Blade.
