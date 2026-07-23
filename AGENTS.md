# Agent Instructions

`php-cuid2` (`visus/cuid2`) is a PHP 8.2+ library implementing the [CUID2 spec](https://github.com/paralleldrive/cuid2) — collision-resistant, URL-safe, horizontally scalable IDs.

## Toolchain

- PHP 8.2+ minimum; `declare(strict_types=1);` on every file. CI runs PHP 8.3/8.4/8.5 on ubuntu and windows.
- Composer 2 for dependencies. PHPStan level `max` + bleeding edge for static analysis. PHPCS with PSR-12 (`phpcs.xml`) for style. PHPUnit 12 for tests. PhpBench for benchmarks.
- `ext-gmp` is an optional suggested dependency — CI enables it; both the GMP and pure-PHP code paths must keep working and stay in sync.

## Commands

All commands are Composer scripts (`composer.json`) — always use these, not the underlying tools directly.

```bash
composer test                    # full gate: lint -> benchmark -> analyze -> test:unit
composer dev:lint:fix            # auto-fix style violations (run before committing)
composer dev:lint:style          # phpcs check only
composer dev:analyze:phpstan     # PHPStan
composer dev:test:unit           # PHPUnit, no coverage
composer dev:test:coverage:html  # HTML coverage report -> build/coverage/
composer dev:benchmark           # PhpBench suite
```

## Code Style

- Short array syntax only (`[]`, never `array()`); `elseif` not `else if`; constants `UPPER_CASE`.
- Forbidden: `empty()`, `dd()`, `dump()`, `var_dump()`, deprecated type aliases.
- 4-space indent (PHP), LF endings, 120-char line limit (comments excluded).
- PHPDoc types must be accurate — PHPStan validates them (`treatPhpDocTypesAsCertain: false`).
- Comments explain non-obvious invariants/workarounds only — never restate what the code does.

## Testing

- 100% line and branch coverage is enforced; untested code fails CI.
- Tests live in `tests/`, mirroring `src/`, named `{ClassName}Test`.
- Coverage is attribute-based (`phpunit.xml`) — do not use `@covers`/`@coversNothing`.
- PHPUnit strict mode: tests that emit output/warnings or are "risky" fail.
- `Counter`/`Fingerprint` singletons use reflection in tests to reset private static state between cases.
- Coverage runs need Xdebug with `XDEBUG_MODE=coverage`.

## Architecture Notes

- `Cuid2::generate()`: random lowercase prefix -> `hrtime()` timestamp -> increment `Counter` singleton -> read `Fingerprint` singleton (hostname+PID+env hash) -> `random_bytes()` entropy -> SHA3-512(timestamp+counter+fingerprint+entropy) -> `Utils::hexToBase36()` -> prepend prefix, truncate to length.
- `Counter`/`Fingerprint` are process-scoped singletons: private constructor, `getInstance(): static`, `__clone`/`__wakeup` throw. Do not introduce static state elsewhere.
- `Utils::hexToBase36()`: fast path via `base_convert()` for hex <= 14 chars; chunked base-100,000,000 conversion for longer strings; GMP path used when `ext-gmp` is loaded.
- `src/compat.php` (loaded via Composer `files` autoload) polyfills `getmypid()`/`gethostname()` only when missing natively — don't add polyfills here without strong justification.

## Commit Convention

Conventional Commits, scope-less, lowercase subject, no trailing period — enforced by CaptainHook and PR title lint:

```
fix: prevent counter overflow on 32-bit systems
```

Allowed types: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`, `revert`.

## What NOT to Do

- Do not add dependencies to `composer.json` without explicit user approval.
- Do not suppress PHPStan errors with `@phpstan-ignore` without a comment justifying why.
- Do not change the ID generation algorithm without updating the CUID2 spec cross-references in `README.md`.
- Do not push directly to `main` or skip git hooks with `--no-verify`.
- Do not widen type signatures to silence PHPStan — fix the underlying type issue.
