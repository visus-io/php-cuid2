# GitHub Copilot Instructions

This PHP library generates collision-resistant identifiers (CUIDs v2) following the CUID2 specification.

## Code Generation Guidelines

### Strict Type Safety (PHP 8.2+)
- Always include `declare(strict_types=1)` at the top of PHP files
- Explicitly type all parameters and return types
- Use PHPDoc for complex array types
- Code must pass PHPStan at maximum level

### PSR-12 Coding Standard
- Follow PSR-12 formatting strictly
- 4 spaces for indentation (no tabs)
- Opening braces on same line for methods/functions
- Use strict comparison operators (`===`, `!==`)
- No trailing whitespace

### Architecture Patterns
- **Singletons**: Counter and Fingerprint classes use singleton pattern (`getInstance()`)
- **Immutability**: Cuid2 instances are immutable once constructed
- **Final classes**: Utility classes should be final with private constructors
- **No instantiation**: Static utility classes cannot be instantiated

### Security & Cryptography
- Use `random_int()` and `random_bytes()` for CSPRNG
- Hash with SHA3-512 algorithm (`hash('sha3-512', ...)`)
- Validate algorithm availability before use
- Throw `InvalidOperationException` if SHA3-512 unavailable

### Base Conversion Strategy
When converting base16 to base36:
- Prefer GMP extension: `gmp_init()` and `gmp_strval()`
- Fallback to `Utils::hexToBase36()` for pure PHP implementation
- Check GMP availability with `function_exists('gmp_init')`

### Testing Requirements
- Every new method/class requires corresponding PHPUnit tests
- Use data providers for parameterized tests
- Maintain 100% code coverage (exclude `src/compat.php`)
- Test files located in `tests/` directory
- Extend `PHPUnit\Framework\TestCase`

### Naming Conventions
- Classes: PascalCase (e.g., `Cuid2`, `InvalidOperationException`)
- Methods/properties: camelCase (e.g., `getInstance()`, `hexToBase36()`)
- Constants: SCREAMING_SNAKE_CASE
- Test methods: `testDescriptiveName()` or `test_descriptive_name()`

### Exception Handling
- Use specific exception types (`OutOfRangeException`, `InvalidOperationException`)
- Validate input parameters early
- Throw exceptions for invalid states (missing algorithms, invalid lengths)
- Include descriptive error messages

### Key Constraints
- CUID length: 4-32 characters (validated at runtime)
- CUID format: starts with lowercase letter (a-z), followed by base36 chars (0-9, a-z)
- PHP minimum version: 8.2
- Required algorithm: SHA3-512

### Common Patterns in This Codebase

**Singleton pattern:**
```php
private static ?self $instance = null;

public static function getInstance(): self
{
    if (self::$instance === null) {
        self::$instance = new self();
    }
    return self::$instance;
}

private function __construct()
{
    // Initialization
}
```

**Strict validation:**
```php
if ($length < 4 || $length > 32) {
    throw new OutOfRangeException('Length must be between 4 and 32');
}
```

**Algorithm availability check:**
```php
if (!in_array('sha3-512', hash_algos(), true)) {
    throw new InvalidOperationException('SHA3-512 algorithm not available');
}
```

## Don't Do This
- Don't use `var_dump()`, `print_r()`, or `echo` for debugging
- Don't add unnecessary dependencies
- Don't create mutable state in Cuid2 class
- Don't use weak randomness (`rand()`, `mt_rand()`)
- Don't skip type declarations
- Don't use `@` error suppression operator
- Don't create new singletons without careful consideration

## File Structure
- `src/` - Source code with namespace `ParagonIE\Cuid2`
- `tests/` - PHPUnit tests
- `src/compat.php` - Polyfills for missing functions (excluded from coverage)

## Development Commands
```bash
vendor/bin/phpunit                    # Run tests
XDEBUG_MODE=coverage vendor/bin/phpunit  # Run with coverage
composer dev:analyze:phpstan          # Static analysis
vendor/bin/phpcs                      # Check code style
vendor/bin/phpcbf                     # Fix code style
```
