# ADR 0022 — English, Sinhala, and Tamil UI locale

## Context

SMIS is used in Sri Lankan schools where staff and students work in English, Sinhala, and Tamil. UI copy was already wrapped in `__()` with English source strings, but there was no locale switcher, no `lang/{si,ta}.json` files, and no persisted preference.

## Decision

1. Support three locales: `en` (default), `si` (Sinhala), `ta` (Tamil), via `App\Enums\AppLocale`.
2. Store the choice in the session for guests. Persist it on `users.locale` when signed in.
3. `SetLocale` web middleware applies session locale, then the user column, then `config('app.locale')`.
4. `POST /locale` (`locale.update`) is available to guests and authenticated users (throttled).
5. JSON translations (`lang/si.json`, `lang/ta.json`) map the existing English `__()` keys. PHP files cover `auth`, `pagination`, `passwords`, and `validation`.
6. Language switcher lives on the homepage, login/auth layout, sidebar, and Settings → Appearance. Noto Sans Sinhala/Tamil are loaded as font fallbacks.

## Consequences

- English remains the fallback (`APP_FALLBACK_LOCALE=en`) and the source language for keys.
- Person names and seeded demo data stay in English (ADR 0015); only UI chrome and messages are translated.
- SMIS Agent model replies stay in the model’s language unless the user asks in Sinhala or Tamil; chrome around chat is translated.
- Changing language does not rewrite stored school records.
