# ADR 0002 — Fortify + Livewire + Flux instead of Breeze

## Context

The project brief suggested Laravel Breeze (Blade) or Fortify. The repository was initialized from the official Laravel Livewire starter kit (Fortify, Livewire 4, Flux UI, Tailwind 4).

## Decision

Retain Fortify + Livewire + Flux as the auth/UI stack. This satisfies the brief’s preference for a monolith with Livewire or Blade+Alpine, and avoids reinstalling Breeze over a working starter.

## Consequences

- Auth flows use Fortify actions/views already present.
- Interactive UIs use Livewire/Flux components.
- Documentation and Phase 1 build on Fortify, not Breeze.
