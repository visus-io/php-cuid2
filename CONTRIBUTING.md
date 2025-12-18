# Contributing to php-cuid2

Thank you for your interest in contributing to php-cuid2! This document provides guidelines and instructions for contributing to this project.

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

This project follows standard open source community guidelines. Please be respectful and constructive in all interactions.

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Git
- Recommended: GMP extension for better performance
- Optional: Xdebug for code coverage reports

### Fork and Clone

1. Fork the repository on GitHub
2. Clone your fork locally:
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

```bash
composer install
```

This will install:
- Development dependencies (PHPUnit, PHPStan, PHP_CodeSniffer, etc.)
- Git hooks via CaptainHook
- Project dependencies

### Git Hooks

The project uses CaptainHook to automatically enforce quality standards:

**Pre-commit hooks:**
- Validates `composer.json` when modified
- Runs `composer normalize` (dry-run) for composer.json changes
- Runs syntax checking on staged PHP files (`composer dev:lint:syntax`)
- Runs code style checking on staged PHP files (`composer dev:lint:style`)

**Commit-msg hook:**
- Validates conventional commit message format

**Post-merge/Post-checkout hooks:**
- Auto-runs `composer install` when composer files change

Hooks are installed automatically during `composer install`.

## Development Workflow

1. Create a new branch for your changes:
   ```bash
   git checkout -b feature/my-new-feature
   ```

2. Make your changes following the [Code Style](#code-style) guidelines

3. Add tests for your changes (see [Testing](#testing))

4. Run the full test suite:
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

7. Run all checks at once:
   ```bash
   composer dev:test
   ```
   This runs linting, benchmarks, static analysis, and unit tests.

8. Commit your changes (see [Commit Messages](#commit-messages))

9. Push to your fork and submit a pull request

## Testing

### Running Tests

```bash
# Run unit tests (no coverage)
composer dev:test:unit

# Run tests with HTML coverage report (requires Xdebug)
composer dev:test:coverage:html
# View report at: build/coverage/html/index.html

# Run tests with coverage for CI (requires Xdebug)
composer dev:test:coverage:ci

# Run full test suite (linting, benchmarks, analysis, unit tests)
composer dev:test
```

You can also run PHPUnit directly:

```bash
# Run all tests
vendor/bin/phpunit

# Run tests with coverage
XDEBUG_MODE=coverage vendor/bin/phpunit

# Run specific test class
vendor/bin/phpunit tests/Cuid2Test.php

# Run specific test method
vendor/bin/phpunit --filter testMethodName
```

### Testing Requirements

- **All code changes require corresponding tests**
- **Maintain 100% code coverage** (excluding `src/compat.php`)
- Tests are located in the `tests/` directory
- Use data providers for parameterized tests
- Test both success and failure scenarios
- Test edge cases and boundary conditions

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

- **PSR-12**: All code must follow PSR-12 coding standard
- **Strict Types**: Enable strict types in all PHP files: `declare(strict_types=1)`
- **Type Safety**: All parameters and return types must be explicitly declared
- **PHPDoc**: Use PHPDoc blocks for complex array types and class/method documentation

### Checking and Fixing Code Style

```bash
# Check code style compliance
composer dev:lint:style

# Auto-fix code style issues
composer dev:lint:fix

# Check syntax errors
composer dev:lint:syntax

# Run all linting checks (syntax + style)
composer dev:lint
```

### Static Analysis

The project uses PHPStan at maximum level:

```bash
composer dev:analyze:phpstan

# Or run all analysis checks
composer dev:analyze
```

All code must pass PHPStan analysis with no errors.

### Composer Validation

When modifying `composer.json`:

```bash
# Validate composer.json
composer validate

# Normalize composer.json
composer normalize
```

### Benchmarking

To ensure performance remains optimal:

```bash
composer dev:benchmark
```

This runs performance benchmarks located in `tests/benchmark/`.

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
- `docs`: Documentation only changes
- `style`: Code style changes (formatting, missing semi colons, etc.)
- `refactor`: Code change that neither fixes a bug nor adds a feature
- `perf`: Performance improvement
- `test`: Adding missing tests or correcting existing tests
- `chore`: Changes to build process or auxiliary tools
- `ci`: Changes to CI configuration files and scripts

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

1. Ensure your code passes all tests, static analysis, and code style checks:
   ```bash
   composer dev:test
   ```

2. Update documentation if needed (README.md, CLAUDE.md, etc.)

3. Create a pull request with a clear title and description

4. Reference any related issues in the PR description (e.g., "Fixes #123")

5. Wait for review and address any feedback

### Pull Request Checklist

Before submitting your PR, verify:

- [ ] Tests added/updated and passing (`composer dev:test:unit`)
- [ ] Code coverage maintained at 100% (`composer dev:test:coverage:html`)
- [ ] PHPStan analysis passes (`composer dev:analyze:phpstan`)
- [ ] Code style follows PSR-12 (`composer dev:lint:style`)
- [ ] Syntax is valid (`composer dev:lint:syntax`)
- [ ] Benchmarks run successfully (`composer dev:benchmark`)
- [ ] All checks pass (`composer dev:test`)
- [ ] Commit messages follow Conventional Commits
- [ ] Documentation updated if needed
- [ ] No breaking changes (or clearly documented)

## Project Architecture

### Core Components

Understanding the project structure will help you contribute effectively:

**Cuid2** (`src/Cuid2.php`)
- Main class that generates CUID2 identifiers
- Immutable once constructed
- Implements JsonSerializable for JSON encoding

**Counter** (`src/Counter.php`)
- Singleton maintaining monotonically increasing counter
- Prevents collisions for rapid CUID generation
- Thread-safe through singleton pattern
- Uses bias-free random initialization within range of 476782367

**Fingerprint** (`src/Fingerprint.php`)
- Singleton creating unique machine/process fingerprint
- Combines hostname, process ID, environment variables, and random data
- Ensures uniqueness across different machines/processes
- Computed once per process lifecycle
- Uses SHA3-512 for fingerprint generation

**InvalidOperationException** (`src/InvalidOperationException.php`)
- Custom exception class
- Thrown when SHA3-512 hashing algorithm is unavailable
- Used to signal invalid operations based on system state

**Utils** (`src/Utils.php`)
- Utility class for base conversion
- Provides `hexToBase36()` method for arbitrary precision base conversion
- Pure PHP implementation without external dependencies
- Uses intermediate large base (100 million) for efficient arithmetic operations
- Fallback when GMP extension unavailable
- Final utility class with private constructor (cannot be instantiated)

**compat.php** (`src/compat.php`)
- Compatibility polyfills for missing system functions
- Polyfills `getmypid()` - returns random value if function unavailable
- Polyfills `gethostname()` - returns random string if function unavailable
- Excluded from code coverage requirements
- Auto-loaded via composer.json files configuration

### CUID2 Generation Process

1. **Initialization**: Random lowercase letter prefix (a-z)
2. **Timestamp**: Current time in milliseconds
3. **Counter**: Monotonically increasing value from singleton
4. **Fingerprint**: Machine/process identification from singleton
5. **Random**: Cryptographically secure random bytes
6. **Hash**: All components hashed with SHA3-512
7. **Convert**: Base16 hash converted to base36
8. **Truncate**: Result trimmed to requested length (minus prefix)

### Base Conversion

The library uses two strategies for base16 to base36 conversion:
- **Preferred**: GMP extension (`gmp_init` and `gmp_strval`) - significantly faster
- **Fallback**: `Utils::hexToBase36()` - pure PHP implementation used when GMP not available
  - Uses arbitrary precision arithmetic with intermediate large base (100 million)
  - No external dependencies required
  - Converts hex to large base representation, then to base36

### Important Design Decisions

- **Singleton Pattern**: Counter and Fingerprint persist across generations within the same process
- **Immutability**: Cuid2 instances cannot be modified after creation
- **SHA3-512 Required**: Library checks for algorithm availability at runtime
- **GMP Recommended**: Significantly faster base conversion when available
- **Length Constraints**: Valid lengths are 4-32 characters (enforced by OutOfRangeException)
- **Format**: CUIDs always start with lowercase letter followed by base36 characters (0-9, a-z)

### Validation

The `Cuid2::isValid()` method performs format validation only:
- Checks length (4-32 characters)
- Verifies pattern (starts with lowercase letter, followed by base36 chars)
- Does NOT guarantee the string was generated by this library
- Optionally accepts expected length parameter for strict validation

## Development Commands Reference

Quick reference for common development tasks:

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

If you have questions about contributing, feel free to:
- Open an issue for discussion
- Ask in your pull request
- Review existing issues and PRs for similar questions

Thank you for contributing to php-cuid2!
