# ADR 0012 — Remove Fortify passkeys

## Context

The starter kit enabled Fortify WebAuthn passkeys (login, confirm password, security settings). The product only needs email/password (+ optional 2FA).

## Decision

Disable `Features::passkeys()`, remove passkey UI/JS (`@laravel/passkeys`), drop the `passkeys` table, and stop implementing `PasskeyUser` on `User`.

## Consequences

- Sign-in is password-only (2FA still available).
- Existing passkey credentials are discarded on migrate.
- `@laravel/passkeys` is no longer an npm dependency.
