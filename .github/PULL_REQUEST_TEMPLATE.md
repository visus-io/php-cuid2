## Description

<!-- Provide a clear and concise description of your changes -->

## Type of Change

<!-- Mark the relevant option(s) with an "x" -->

- [ ] Bug fix (non-breaking change that fixes an issue)
- [ ] New feature (non-breaking change that adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update
- [ ] Performance improvement
- [ ] Code refactoring (no functional changes)
- [ ] Dependency update
- [ ] Other (please describe):

## Related Issues

<!-- Link to related issues using #issue_number -->

Closes #
Related to #

## Changes Made

<!-- List the specific changes you made -->

-
-
-

## Testing

<!-- Describe the tests you ran and/or added -->

### Test Coverage

- [ ] Added/updated unit tests
- [ ] All existing tests pass (`vendor/bin/phpunit`)
- [ ] Code coverage maintained at 100% (excluding src/compat.php)
- [ ] Tested with Xdebug coverage (`XDEBUG_MODE=coverage vendor/bin/phpunit`)

### Manual Testing

<!-- Describe any manual testing performed -->

**Test environment:**

- PHP version:
- php-cuid2 version:
- GMP extension: [ ] Enabled [ ] Disabled

**Steps taken:**

1.
2.
3.

## Code Quality

<!-- Verify code quality checks -->

- [ ] Code follows PSR-12 coding standards
- [ ] PHPStan passes at max level (`composer dev:analyze:phpstan`)
- [ ] Code style check passes (`vendor/bin/phpcs`)
- [ ] Syntax validation passes (`composer dev:lint:syntax`)
- [ ] All type declarations are explicit (strict_types enabled)
- [ ] PHPDoc comments added for complex array types

## Documentation

<!-- Verify documentation updates -->

- [ ] Updated README.md (if needed)
- [ ] Updated PHPDoc/code comments (if needed)
- [ ] Updated type declarations (if needed)
- [ ] Updated CLAUDE.md (if needed)
- [ ] Added/updated code examples (if needed)

## Composer & Dependencies

<!-- If dependencies were changed -->

- [ ] composer.json is valid (`composer validate`)
- [ ] composer.json is normalized (`composer normalize --dry-run`)
- [ ] Composer lock file updated (`composer.lock`)

## Breaking Changes

<!-- If this is a breaking change, describe the impact and migration path -->

### Impact

<!-- What will break for users? -->

### Migration Guide

<!-- How should users update their code? -->

```php
// Before

// After
```

## Compatibility

<!-- Verify PHP version compatibility -->

- [ ] Tested on PHP 8.2
- [ ] Tested on PHP 8.3 (if available)
- [ ] Works with GMP extension enabled
- [ ] Works with pure PHP fallback (GMP disabled)

## Additional Notes

<!-- Any additional information, context, or screenshots -->

## Checklist

<!-- Final checks before submitting -->

- [ ] My code follows the PSR-12 coding standard
- [ ] I have performed a self-review of my code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] My changes generate no new warnings or errors
- [ ] I have tested my changes thoroughly (both with and without GMP if applicable)
- [ ] My commit messages follow the [Conventional Commits](https://www.conventionalcommits.org/) specification
- [ ] I have ensured that git hooks (CaptainHook) pass locally
- [ ] I have read the CLAUDE.md file for project conventions
