# AGENTS.md

Guidelines for AI agents working on the `visus/cuid2` PHP library.

## Project Overview

`php-cuid2` is a PHP 8.2+ library for generating CUID2 identifiers — collision-resistant, URL-safe, horizontally scalable unique IDs. It is a PHP implementation of the [CUID2 specification](https://github.com/paralleldrive/cuid2).

Key characteristics:
- Base36-encoded, always starts with a lowercase letter
- Configurable length: 4–32 characters (default: 24)
- Cryptographically secure randomness via `random_bytes()`
- SHA3-512 hashing of timestamp + counter + fingerprint + entropy
- Optional `ext-gmp` for 60–300× faster base conversion

## Repository Layout

```
src/
  Cuid2.php                  # Main value object; static factory and validation
  Counter.php                # Singleton monotonic counter (collision prevention)
  Fingerprint.php            # Singleton machine/process fingerprint
  Utils.php                  # hex → base36 conversion (GMP-aware)
  InvalidOperationException.php
  compat.php                 # Polyfills for getmypid() / gethostname()
tests/
  Cuid2Test.php
  CounterTest.php
  FingerprintTest.php
  UtilsTest.php
  benchmark/
    Cuid2Bench.php
    SingletonBench.php
    ValidationBench.php
.github/workflows/
  ci.yml                     # Full CI pipeline
  lint_pullrequest.yml       # PR title validation
composer.json                # Authoritative list of scripts and deps
phpunit.xml                  # PHPUnit config (100% coverage required)
phpstan.neon                 # PHPStan max level + bleeding edge
phpcs.xml                    # PSR-12 + project-specific rules
phpbench.json                # Benchmark runner config
captainhook.json             # Git hooks (pre-commit, commit-msg)
```

## PHP Version & Toolchain

- **Minimum PHP:** 8.2 (strict types required on every file)
- **CI matrix:** PHP 8.3, 8.4, 8.5 on ubuntu-latest and windows-latest
- **Package manager:** Composer 2
- **Static analysis:** PHPStan level max with bleeding edge enabled
- **Style:** PHP_CodeSniffer with PSR-12 base (see `phpcs.xml`)
- **Tests:** PHPUnit 12
- **Benchmarks:** PhpBench 1.x

## Development Commands

All commands are defined in `composer.json` under `scripts`. Always use these rather than invoking tools directly.

```bash
# Install / update dependencies
composer install

# Run the full quality gate (lint → analyze → test)
composer test

# Individual steps
composer dev:lint              # syntax check + style check
composer dev:lint:syntax       # parallel-lint only
composer dev:lint:style        # phpcs only
composer dev:lint:fix          # phpcbf auto-fix

composer dev:analyze:phpstan   # PHPStan static analysis

composer dev:test:unit         # PHPUnit, no coverage
composer dev:test:coverage:html  # HTML coverage report in build/coverage/
composer dev:test:coverage:ci    # Clover + JUnit output for CI

composer dev:benchmark         # PhpBench performance suite
```

## Code Style Rules

Source of truth: `phpcs.xml` and `.editorconfig`.

- **Indentation:** 4 spaces (PHP), 2 spaces (JSON / YAML / Markdown)
- **Line endings:** LF
- **Encoding:** UTF-8
- **Max line length:** 120 characters (comments excluded)
- **Array syntax:** Short syntax only (`[]`, never `array()`)
- **Conditional keywords:** `elseif` (never `else if`)
- **Forbidden functions:** `empty()`, `dd()`, `dump()`, `var_dump()`; deprecated type aliases
- **Constant names:** UPPER_CASE
- **Strict types:** `declare(strict_types=1);` on every PHP file

Always run `composer dev:lint:fix` before committing to auto-correct fixable violations, then `composer dev:lint:style` to confirm zero remaining errors.

## Static Analysis

PHPStan is configured at level **max** with bleeding edge. All code in `src/` and `tests/` must pass without errors.

- Never suppress with `@phpstan-ignore` unless truly necessary and with a comment explaining why.
- Do not widen type signatures to work around analysis failures; fix the underlying type issue instead.
- PHPDoc type annotations must be accurate — `treatPhpDocTypesAsCertain` is `false`, so PHPStan will still validate them.

## Testing Requirements

- **100% line and branch coverage** is enforced. Adding code without tests will fail CI.
- Tests live in `tests/` and mirror the `src/` structure.
- Test class naming: `{ClassName}Test` in the root `tests/` namespace.
- Singletons (`Counter`, `Fingerprint`) use reflection in tests to reset private static instances between test cases.
- PHPUnit strict mode is enabled: tests that produce output, emit warnings, or are "risky" will fail.
- Do **not** use `@covers` or `@coversNothing` — the project uses attribute-based coverage configuration in `phpunit.xml`.

### Running Tests Locally

```bash
# Ensure Xdebug is installed and XDEBUG_MODE=coverage for coverage runs
composer dev:test:unit
```

On Windows the CI uses the same `composer dev:test:unit` command; no platform-specific branching needed in tests.

## Architecture Notes

### ID Generation Algorithm (in `Cuid2::generate()`)

1. Pick a random lowercase letter prefix (`a`–`z`)
2. Record current time in milliseconds via `hrtime()`
3. Read and increment the `Counter` singleton
4. Read the `Fingerprint` singleton (hostname + PID + env hash)
5. Generate cryptographically secure random bytes via `random_bytes()`
6. SHA3-512 hash of: `timestamp + counter + fingerprint + entropy`
7. Convert hex digest to base36 via `Utils::hexToBase36()`
8. Prepend prefix; truncate to requested length

### Singleton Pattern

`Counter` and `Fingerprint` follow the same pattern:
- Private constructor
- Private static `$instance` property
- `getInstance(): static` factory method
- Clone and unserialize are forbidden (`__clone` throws, `__wakeup` throws)
- Singletons are **process-scoped** — do not share across forked processes without resetting

### Utils::hexToBase36()

- For hex strings ≤ 14 chars: fast path using `base_convert()`
- For longer strings: chunked arbitrary-precision conversion using a base-100,000,000 intermediate
- With `ext-gmp` loaded: the GMP path is significantly faster; keep both paths working

### compat.php

Loaded via Composer's `files` autoload. Defines `getmypid()` and `gethostname()` only when the native functions do not exist. Do not add new polyfills here without strong justification.

## Commit Convention

Commits must follow **Conventional Commits** (enforced by CaptainHook and validated on PRs):

```
<type>: <short lowercase subject>
```

Allowed types: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`, `revert`

- **No scope** — the project uses scope-less types only
- Subject must start lowercase and must not end with a period
- PR titles follow the same format (validated by `lint_pullrequest.yml`)

Examples:
```
feat: add support for custom alphabet
fix: prevent counter overflow on 32-bit systems
test: add coverage for fingerprint fallback path
chore: update phpstan to 2.x
```

## CI Pipeline Summary

Defined in `.github/workflows/ci.yml`. Jobs run on every push and PR.

| Job | Runs on | What it does |
|-----|---------|--------------|
| Coding Standards | ubuntu-latest | `dev:lint:syntax` then `dev:lint:style` |
| Static Analysis | ubuntu-latest | `dev:analyze:phpstan` |
| Code Coverage | ubuntu-latest | PHPUnit + Xdebug → uploads to SonarCloud |
| Unit Tests | ubuntu + windows, PHP 8.3/8.4/8.5 | `dev:test:unit` |

Coverage and unit-test jobs depend on Coding Standards and Static Analysis passing first.

**SonarCloud:** Coverage results are uploaded with `sonar-project.properties`. The project key is `visus:php-cuid2`. Do not remove or rename the clover or JUnit output files expected by the CI step.

## What Agents Should and Should Not Do

### Do

- Run `composer dev:lint:fix` before any commit to fix auto-correctable style issues.
- Run `composer test` (the full suite) before declaring a task complete.
- Keep 100% code coverage — add tests for every new code path.
- Use `declare(strict_types=1)` on every new PHP file.
- Use short array syntax `[]` exclusively.
- Name test methods descriptively: `testGenerateReturnsStringOfExpectedLength()`.
- Check both the GMP and non-GMP code paths in `Utils` when modifying base conversion.
- Respect the singleton invariants in `Counter` and `Fingerprint` — do not introduce static state elsewhere.

### Do Not

- Do not introduce dependencies to `composer.json` without explicit user approval.
- Do not use `empty()`, `var_dump()`, `dd()`, `dump()`, or other forbidden functions.
- Do not suppress PHPStan errors with `@phpstan-ignore` without a comment justification.
- Do not write `array()` long-form syntax.
- Do not add `else if` — use `elseif`.
- Do not push directly to `main`; all changes go through a PR.
- Do not skip git hooks with `--no-verify`.
- Do not use `@covers` annotations — the project does not use them.
- Do not change the ID generation algorithm without updating the CUID2 specification cross-references in `README.md`.
- Do not add comments that just restate what the code does; only comment non-obvious invariants or workarounds.

## Optional Extension

`ext-gmp` is listed as a suggested dependency. CI enables it. When writing or modifying `Utils::hexToBase36()`:

- Both the GMP branch and the pure-PHP branch must produce identical output.
- Benchmark regressions in the GMP path are unacceptable; verify with `composer dev:benchmark`.

## File Encoding and Formatting Checklist

Before opening a PR, verify:

- [ ] `composer dev:lint` passes with zero errors
- [ ] `composer dev:analyze:phpstan` passes with zero errors
- [ ] `composer dev:test:coverage:html` passes with 100% coverage
- [ ] All new files have `declare(strict_types=1);`
- [ ] No trailing whitespace, no Windows line endings (`\r\n`)
- [ ] Commit message follows Conventional Commits format
