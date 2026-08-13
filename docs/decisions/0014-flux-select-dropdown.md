# ADR 0014 — Custom Flux select dropdown (free edition)

## Context

Flux free `flux:select` uses a native HTML `<select>` (the Pro `listbox` / `combobox` variants are unavailable). Native selects still open the operating-system picker, so the UI looked like “normal HTML selectors” even after swapping tags to `flux:select`.

## Decision

Publish and override `flux/select/variants/default.blade.php` so every `flux:select` renders a Flux `dropdown` + `menu` trigger. A visually hidden native `<select>` remains for form names, `required`, Alpine `x-model`, and existing tests.

## Consequences

- All current `flux:select` call sites pick up the custom picker without view-by-view rewrites.
- Option lists are built in Alpine from the hidden native options, including assignment rows cloned with `x-for`.
- Do not put Blade `@if` / `@endif` inside `<flux:button>` tags — Flux treats `@if` as a component attribute and prints it in the UI.
- Flux calendar datepicker stays Pro-only; `flux:input type="date"` is unchanged.
