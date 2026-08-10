# ADR 0003 — SQLite locally, MySQL in production

## Context

The brief mandates MySQL. The starter defaults to SQLite for zero-config local and CI runs.

## Decision

Keep SQLite as the default for local development and automated tests. Document MySQL as the production/target database and provide `.env.example` MySQL settings ready to uncomment.

## Consequences

- Faster CI without a MySQL service unless explicitly added.
- Migrations must stay MySQL-compatible (avoid SQLite-only features).
- Production `.env` uses `DB_CONNECTION=mysql`.
