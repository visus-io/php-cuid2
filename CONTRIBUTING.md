# Contributing to php-cuid2

Thank you for your interest in this project. This document gives you guidelines and instructions for how to contribute to php-cuid2.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Development Workflow](#development-workflow)
- [Testing](#testing)
- [Code Style](#code-style)
- [Commit Messages](#commit-messages)
- [Submitting Changes](#submitting-changes)
- [Project Architecture](#project-architecture)

## Code of Conduct

This project follows standard open-source community guidelines. Be respectful and constructive in every interaction. See [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) for the full guidelines.

## Getting Started

### Prerequisites

- PHP 8.3 or a later version
- Composer
- Git
- Recommended: the GMP extension, for better performance
- Optional: Xdebug, for code coverage reports

### Fork and Clone

1. Fork the repository on GitHub.
2. Clone your fork to your local machine:
   ```bash
   git clone https://github.com/YOUR_USERNAME/php-cuid2.git
   cd php-cuid2
   ```
3. Add the upstream repository:
   ```bash
   git remote add upstream https://github.com/visus/php-cuid2.git
   ```

## Development Setup

### Install Dependencies

Run this command to install dependencies:

```bash
composer install
```

This command installs:
- The development dependencies: PHPUnit, PHPStan, PHP_CodeSniffer, and other tools
- The Git hooks, through CaptainHook
- The project dependencies

### Git Hooks

This project uses CaptainHook to enforce quality standards automatically.

**Pre-commit hooks:**
- Validate `composer.json` when you modify the file
- Run `composer normalize` in dry-run mode when you change `composer.json`
- Check the syntax of staged PHP files, with `composer dev:lint:syntax`
- Check the code style of staged PHP files, with `composer dev:lint:style`

**Commit-msg hook:**
- Validate the Conventional Commits format of the commit message

**Post-merge/Post-checkout hooks:**
- Run `composer install` automatically when Composer files change

The `composer install` command installs these hooks automatically.

## Development Workflow

1. Create a new branch for your changes:
   ```bash
   git checkout -b feature/my-new-feature
   ```

2. Make your changes. Follow the [Code Style](#code-style) guidelines.

3. Add tests for your changes. See [Testing](#testing).

4. Run the unit tests:
   ```bash
   composer dev:test:unit
   ```

5. Run static analysis:
   ```bash
   composer dev:analyze:phpstan
   ```

6. Check code style:
   ```bash
   composer dev:lint:style
   ```

7. Run every check at once:
   ```bash
   composer dev:test
   ```
   This command runs linting, benchmarks, static analysis, and unit tests.

8. Commit your changes. See [Commit Messages](#commit-messages).

9. Push your branch to your fork. Submit a pull request.

## Testing

### Running Tests

```bash
# Run the unit tests, without coverage
composer dev:test:unit

# Run the tests and generate an HTML coverage report (needs Xdebug)
composer dev:test:coverage:html
# View the report at build/coverage/html/index.html

# Run the tests with coverage for CI (needs Xdebug)
composer dev:test:coverage:ci

# Run the full test suite: linting, benchmarks, analysis, and unit tests
composer dev:test
```

You can also run PHPUnit directly:

```bash
# Run all tests
vendor/bin/phpunit

# Run the tests with coverage
XDEBUG_MODE=coverage vendor/bin/phpunit

# Run a specific test class
vendor/bin/phpunit tests/Cuid2Test.php

# Run a specific test method
vendor/bin/phpunit --filter testMethodName
```

### Testing Requirements

- Add a test for every code change.
- Maintain 100% code coverage. This requirement excludes `src/compat.php`.
- Put tests in the `tests/` directory.
- Use data providers for parameterized tests.
- Test both success and failure scenarios.
- Test edge cases and boundary conditions.

### Writing Tests

Example test structure:

```php
<?php

declare(strict_types=1);

namespace Visus\Cuid2\Test;

use PHPUnit\Framework\TestCase;

class MyFeatureTest extends TestCase
{
    public function testMyFeature(): void
    {
        // Arrange
        $input = 'test';

        // Act
        $result = myFunction($input);

        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

## Code Style

### Standards

- **PSR-12**: Follow the PSR-12 coding standard for all code.
- **Strict Types**: Add `declare(strict_types=1)` to every PHP file.
- **Type Safety**: Declare every parameter type and return type explicitly.
- **PHPDoc**: Use PHPDoc blocks for complex array types. Use PHPDoc blocks to document classes and methods.

### Checking and Fixing Code Style

```bash
# Check code style
composer dev:lint:style

# Fix code style issues automatically
composer dev:lint:fix

# Check for syntax errors
composer dev:lint:syntax

# Run every linting check: syntax and style
composer dev:lint
```

### Static Analysis

This project uses PHPStan at the maximum level:

```bash
composer dev:analyze:phpstan

# Or run every analysis check
composer dev:analyze
```

All code must pass PHPStan analysis with no errors.

### Composer Validation

When you modify `composer.json`, run these commands:

```bash
# Validate composer.json
composer validate

# Normalize composer.json
composer normalize
```

### Benchmarking

Run this command to check performance:

```bash
composer dev:benchmark
```

This command runs the performance benchmarks in `tests/benchmark/`.

## Commit Messages

This project follows the [Conventional Commits](https://www.conventionalcommits.org/) specification.

### Format

```
<type>(<scope>): <description>

[optional body]

[optional footer(s)]
```

### Types

- `feat`: A new feature
- `fix`: A bug fix
- `docs`: A change to documentation only
- `style`: A code style change, such as formatting or a missing semicolon
- `refactor`: A code change that does not fix a bug and does not add a feature
- `perf`: A performance improvement
- `test`: A new test, or a correction to an existing test
- `chore`: A change to the build process or to an auxiliary tool
- `ci`: A change to a CI configuration file or script

### Examples

```bash
# Feature
git commit -m "feat(cuid2): add support for custom random generator"

# Bug fix
git commit -m "fix(counter): prevent integer overflow in counter increment"

# Documentation
git commit -m "docs: update installation instructions in README"

# Refactoring
git commit -m "refactor(utils): optimize base conversion algorithm"

# Tests
git commit -m "test(fingerprint): add test for environment variable handling"

# Performance
git commit -m "perf(utils): improve hexToBase36 conversion speed"
```

## Submitting Changes

### Pull Request Process

1. Make sure your code passes all tests, static analysis, and code style checks:
   ```bash
   composer dev:test
   ```

2. Update the documentation if needed. Update README.md, CLAUDE.md, or other files.

3. Create a pull request. Write a clear title and description.

4. Reference related issues in the PR description. For example, write "Fixes #123".

5. Wait for a review. Address the feedback.

### Pull Request Checklist

Before you submit your PR, verify these items:

- [ ] Tests are added or updated, and all tests pass (`composer dev:test:unit`)
- [ ] Code coverage remains at 100% (`composer dev:test:coverage:html`)
- [ ] PHPStan analysis passes (`composer dev:analyze:phpstan`)
- [ ] Code style follows PSR-12 (`composer dev:lint:style`)
- [ ] Syntax is valid (`composer dev:lint:syntax`)
- [ ] Benchmarks run successfully (`composer dev:benchmark`)
- [ ] Every check passes (`composer dev:test`)
- [ ] Commit messages follow the Conventional Commits format
- [ ] Documentation is updated if needed
- [ ] No breaking changes exist. If a breaking change exists, document it clearly.

## Project Architecture

For the internal design of this project, see [ARCHITECTURE.md](ARCHITECTURE.md). ARCHITECTURE.md describes the generation flow, the singleton classes, the base-conversion strategy, and the compatibility polyfills.

## Development Commands Reference

This is a quick reference for common development tasks:

```bash
# Testing
composer dev:test                  # Run all tests and checks
composer dev:test:unit             # Run unit tests only
composer dev:test:coverage:html    # Generate HTML coverage report

# Code Quality
composer dev:lint                  # Run syntax + style checks
composer dev:lint:syntax           # Check PHP syntax
composer dev:lint:style            # Check code style (PSR-12)
composer dev:lint:fix              # Auto-fix code style issues
composer dev:analyze               # Run static analysis
composer dev:analyze:phpstan       # Run PHPStan analysis

# Performance
composer dev:benchmark             # Run performance benchmarks

# Composer
composer validate                  # Validate composer.json
composer normalize                 # Normalize composer.json

# Cleanup
composer dev:build:clean          # Clean build artifacts
```

## Questions?

If you have questions about how to contribute, do one of these:
- Open an issue for discussion.
- Ask a question in your pull request.
- Review existing issues and pull requests for similar questions.

Thank you for your contribution to php-cuid2.
