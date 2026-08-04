# Agent Instructions

`php-cuid2` (`visus/cuid2`) is a PHP library. It implements the [CUID2 spec](https://github.com/paralleldrive/cuid2). CUID2 identifiers are collision-resistant, URL-safe, and horizontally scalable.

@ARCHITECTURE.md

## Toolchain

- Use PHP 8.3 or a later version. Add `declare(strict_types=1);` to every file.
- CI runs PHP 8.3, 8.4, and 8.5 on Ubuntu and on Windows.
- Use Composer 2 to manage dependencies.
- Use PHPStan at level `max`, with bleeding edge enabled, for static analysis.
- Use PHPCS with PSR-12 (`phpcs.xml`) for style checks.
- Use PHPUnit 12 for tests.
- Use PhpBench for benchmarks.
- `ext-gmp` is an optional suggested dependency. CI enables it. Keep the GMP code path and the pure-PHP code path both working, and keep them in sync.

## Commands

All commands are Composer scripts (`composer.json`). Always use these scripts. Do not call the underlying tools directly.

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

- Use short array syntax only (`[]`). Do not use `array()`.
- Use `elseif`. Do not use `else if`.
- Write constants in `UPPER_CASE`.
- Do not use `empty()`, `dd()`, `dump()`, `var_dump()`, or deprecated type aliases.
- Indent PHP code with 4 spaces. Use LF line endings. Keep lines at or under 120 characters (comments excluded).
- Write accurate PHPDoc types. PHPStan validates these types (`treatPhpDocTypesAsCertain: false`).
- Write a comment only to explain a non-obvious invariant or workaround. Do not write a comment that restates what the code does.

## Testing

- This project enforces 100% line and branch coverage. Untested code fails CI.
- Put tests in `tests/`. Mirror the structure of `src/`. Name each test class `{ClassName}Test`.
- Coverage is attribute-based (`phpunit.xml`). Do not use `@covers` or `@coversNothing`.
- PHPUnit runs in strict mode. A test fails if it emits output or warnings, or if PHPUnit marks it "risky".
- The `Counter` and `Fingerprint` singletons hold private static state. Use reflection in tests to reset this state between test cases.
- Coverage runs need Xdebug. Set `XDEBUG_MODE=coverage` when you run coverage.

## Commit Convention

This project uses Conventional Commits. A scope is optional. Add a scope when it clarifies the affected area — most commits do. Write the subject in lowercase. Do not add a trailing period. CaptainHook and the PR title lint enforce this convention.

```
fix(counter): prevent counter overflow on 32-bit systems
```

Allowed types: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`, `revert`.

## What NOT to Do

- Do not add dependencies to `composer.json` without explicit user approval.
- Do not suppress a PHPStan error with `@phpstan-ignore` without a comment that justifies the suppression.
- Do not change the ID generation algorithm without updating the CUID2 spec cross-references in `README.md`.
- Do not push directly to `main`. Do not skip git hooks with `--no-verify`.
- Do not widen a type signature to silence PHPStan. Fix the underlying type issue instead.
- Do not introduce static or singleton state outside `Counter` and `Fingerprint`. See [ARCHITECTURE.md](ARCHITECTURE.md).
- Do not add a polyfill to `src/compat.php` without strong justification. See [ARCHITECTURE.md](ARCHITECTURE.md).
