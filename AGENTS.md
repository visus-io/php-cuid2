<!-- architecture -->
@ARCHITECTURE.md
<!-- architecture -->

# Agent Instructions

`php-cuid2` (`visus/cuid2`) is a PHP library. It implements the [CUID2 spec](https://github.com/paralleldrive/cuid2). CUID2 identifiers are collision-resistant, URL-safe, and horizontally scalable.

## Language and Runtime

- Use PHP 8.3 or a later version. Add `declare(strict_types=1);` to every file.
- CI runs PHP 8.3, 8.4, and 8.5 on Ubuntu and on Windows.
- Use Composer 2 to manage dependencies.
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

- Use PHPStan at level `max`, with bleeding edge enabled, for static analysis.
- Use PHPCS with PSR-12 (`phpcs.xml`) for style checks.
- Use PHPUnit 12 for tests.
- Use PhpBench for benchmarks.

## Code Style

- Use short array syntax only (`[]`). Do not use `array()`.
- Use `elseif`. Do not use `else if`.
- Write constants in `UPPER_CASE`.
- Do not use `empty()`, `dd()`, `dump()`, `var_dump()`, or deprecated type aliases.
- Indent PHP code with 4 spaces. Use LF line endings. Keep lines at or under 120 characters (comments excluded).
- Write accurate PHPDoc types. PHPStan validates these types (`treatPhpDocTypesAsCertain: false`).
- Write a comment only to explain a non-obvious invariant or workaround. Do not write a comment that restates what the code does.

## Member Order

Order class members by kind, then by visibility, then alphabetically. This is a
mechanical rule — apply it exactly, do not reorder by "logical flow" or
call-before-use.

**Default pattern**, for any class not covered by the test pattern below:

1. Constants
2. Properties
3. Constructor (`__construct`)
4. Other magic methods (`__destruct`, `__clone`, `__wakeup`, `__toString`, etc.)
5. Methods

Within each kind, order `public` before `protected` before `private`. Within each
visibility group, static members come before instance members. Within that, sort
alphabetically by name (case-sensitive).

**Test/benchmark pattern**, for PHPUnit test classes and PhpBench benchmark classes:

1. Setup/teardown methods (`setUp`, `tearDown`, or a method referenced by PhpBench's
   `#[BeforeMethods]`/`#[AfterMethods]`)
2. Everything else (properties, data/param providers, helper methods) — same
   kind/visibility/alphabetical rule as the default pattern
3. Test or benchmark methods (`test*` / `bench*`) last, sorted alphabetically

## YAML and Workflow Files

- Before finishing any change that touches a `.yml`/`.yaml` file, check whether `yamllint` is available (`command -v yamllint`) and, if so, run it against the changed file(s). Skip silently only if the tool is not installed.
- If the changed `.yml` file is a GitHub Actions workflow under `.github/workflows/`, additionally check whether `actionlint` is available (`command -v actionlint`) and, if so, run it against the changed file(s). Skip silently only if the tool is not installed.

## Project Structure Rules

- New source files go under `src/`.
- New tests go under `tests/` and must mirror the structure of `src/`. Name each test class `{ClassName}Test`.
- Do not add dependencies to `composer.json` without explicit user approval.

## Patterns to Follow

- **Singleton for process-scoped state** — `Counter` and `Fingerprint` are the only classes allowed to hold static state. Each has a private constructor, a static `getInstance()` method returning the concrete class, and throws on `__clone` and `__wakeup`. See [ARCHITECTURE.md](ARCHITECTURE.md).
- **Dual code path (GMP / pure-PHP)** — `Utils::hexToBase36()` and `Utils::bytesToBase36()` must produce identical output whether `ext-gmp` is loaded or not. A change to one path requires the equivalent change to the other.
- **Polyfill isolation** — `src/compat.php` defines `getmypid()`/`gethostname()` only when the function is missing natively. Do not add a new polyfill there without strong justification.

## Testing Requirements

- This project enforces 100% line and branch coverage. Untested code fails CI.
- Coverage is attribute-based (`phpunit.xml`). Do not use `@covers` or `@coversNothing`.
- PHPUnit runs in strict mode. A test fails if it emits output or warnings, or if PHPUnit marks it "risky".
- The `Counter` and `Fingerprint` singletons hold private static state. Use reflection in tests to reset this state between test cases.
- Coverage runs need Xdebug. Set `XDEBUG_MODE=coverage` when you run coverage.

## Documentation Style

All documentation and PHPDoc comments **must** follow the ASD-STE100 (Simplified Technical
English) standard. This applies to Markdown docs (`README.md`, `ARCHITECTURE.md`, `AGENTS.md`)
and to PHPDoc comments (`@param`, `@return`, `@throws`, etc.) in PHP source. Key rules:

- Write short sentences. Target 20 words or fewer per sentence.
- Write one instruction or one fact per sentence.
- Use active voice. Use simple tenses: simple present, simple past, or imperative.
- Address the reader directly with imperative mood for instructions (e.g. "Call
  `generate()` first," not "The `generate()` method should be called first").
- Avoid noun clusters (chains of 3+ nouns strung together). Avoid gerunds as nouns when a
  verb form reads clearer.
- Use each term consistently. Do not use a synonym for a term already used elsewhere in the
  same document or comment.
- Avoid vague qualifiers ("relatively", "fairly", "quite"). State the exact behavior or value.

## Documentation

- Do not change the ID generation algorithm without updating the CUID2 spec cross-references in `README.md` in the same change.
- Any change to a pattern, singleton, or code-path invariant described in `ARCHITECTURE.md` must be accompanied by a matching update to `ARCHITECTURE.md` in the same change. Treat a missing doc update as an incomplete change, not a follow-up.

## Commit Convention

This project uses Conventional Commits. A scope is optional. Add a scope when it clarifies the affected area — most commits do. Write the subject in lowercase. Do not add a trailing period. CaptainHook and the PR title lint enforce this convention.

```
fix(counter): prevent counter overflow on 32-bit systems
```

Allowed types: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`, `revert`.

## What NOT to Do

- Do not add dependencies to `composer.json` without explicit user approval.
- Do not suppress a PHPStan error with `@phpstan-ignore` without a comment that justifies the suppression.
- Do not push directly to `main`. Do not skip git hooks with `--no-verify`.
- Do not widen a type signature to silence PHPStan. Fix the underlying type issue instead.
- Do not introduce static or singleton state outside `Counter` and `Fingerprint`. See [ARCHITECTURE.md](ARCHITECTURE.md).
- Do not add a polyfill to `src/compat.php` without strong justification. See [ARCHITECTURE.md](ARCHITECTURE.md).
