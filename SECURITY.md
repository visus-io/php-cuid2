# Security Policy

## Supported Versions

| Version | Status | Notes |
|---------|--------|-------|
| 6.x | ✅ Supported | Current version |
| 5.x | ✅ Supported | Security patches only |
| < 5.0 | ❌ Unsupported | No longer supported |

## Vulnerability Reporting Process

**Please do not report security vulnerabilities through public GitHub issues.**

Instead, please report them responsibly using one of the following methods:

### Preferred Method
Use GitHub Security Advisories by navigating to the Security tab and selecting "Report a vulnerability."

### Alternative Method
Email: security@projects.visus.io

### Required Information
When reporting a vulnerability, please include:
- **Vulnerability type** (e.g., cryptographic weakness, predictability, collision vulnerability)
- **Affected versions** and components
- **Reproduction steps** with detailed instructions
- **Potential impact** and severity assessment
- **Suggested fix** (if available)
- **Your contact information** for follow-up

### Response Timeline
- **Initial acknowledgment:** Within 48 hours
- **Detailed assessment:** Within 7 days with severity classification
- **Resolution timeline:**
  - Critical severity: 7-14 days
  - High priority: 14-30 days
  - Medium priority: 30-60 days
  - Low priority: 60-90 days or next release

### Disclosure Policy
We request 90 days before public disclosure to allow time for patches to be developed, tested, and deployed. We will credit security researchers in release notes unless anonymity is requested.

## Security Scope

### In-Scope Security Concerns

We consider the following issues to be security vulnerabilities:

- **Cryptographic weaknesses** in SHA3-512 implementation or usage
- **ID predictability** that could allow attackers to guess or enumerate identifiers
- **Collision vulnerabilities** beyond theoretical probability
- **Memory safety issues** (memory leaks, buffer overflows in dependencies)
- **Timing attacks** that could leak information about ID generation
- **Dependency vulnerabilities** (CVEs in required packages)
- **Platform-specific vulnerabilities** affecting ID uniqueness or security
- **Random number generation weaknesses** compromising entropy

### Out-of-Scope

The following are not considered security vulnerabilities:

- **Theoretical collision probability** for default 24-character IDs (astronomically low by design)
- **Application-level misuse** (e.g., using CUIDs as passwords or cryptographic secrets)
- **Resource exhaustion DoS** from generating large numbers of IDs
- **Non-security build or configuration issues**
- **Performance characteristics** unless they enable timing attacks
- **Compatibility issues** with unsupported PHP versions (< 8.2)

## Security Best Practices

### Appropriate Use Cases

This library implements CUID2 (collision-resistant unique identifiers) with SHA3-512 hashing (NIST FIPS-202 compliant).

**Recommended uses:**
- Public-facing URL identifiers
- Database primary keys
- File or resource naming
- Distributed system identifiers
- Log correlation IDs

**Not recommended:**
- **Never use CUIDs as cryptographic secrets or authentication tokens**
- **Never use CUIDs as passwords or password reset tokens**
- **Never use CUIDs as session identifiers** (use dedicated session management)
- **Never use CUIDs as API keys** (use cryptographic key generation)

### Threat Model Considerations

Consider your threat model when implementing CUIDs:
- CUID2 provides collision resistance and unpredictability for identifier use cases
- For security-critical operations, use dedicated cryptographic libraries
- Evaluate whether identifier enumeration is a concern for your application
- Consider rate limiting if ID generation endpoints are publicly accessible

### Dependency Management

- Keep dependencies updated through `composer update`
- Monitor security advisories through GitHub repository watches
- Subscribe to Packagist notifications for this package
- Review `composer.lock` regularly for known CVEs
- Use `composer audit` to check for vulnerable dependencies

## Cryptographic Dependencies

### SHA3-512 Hashing

The library relies on PHP's native SHA3-512 implementation:
- **Requirement:** PHP must be compiled with SHA3 support (standard in most distributions)
- **Compliance:** NIST FIPS-202 compliant when using native PHP implementation
- **Validation:** The library checks for SHA3-512 availability at runtime
- **Error handling:** Throws `InvalidOperationException` if SHA3-512 is not available

### Random Number Generation

- **Primary:** Uses `random_bytes()` for cryptographically secure random number generation (CSPRNG)
- **Source:** PHP's native CSPRNG implementation
- **Fallback:** `random_int()` for counter initialization
- **Entropy:** Relies on operating system entropy sources

### Fingerprinting Components

The library incorporates multiple sources for machine/process fingerprinting:
- Hostname (via `gethostname()` or fallback)
- Process ID (via `getmypid()` or fallback)
- Environment variables
- Cryptographically secure random data
- All hashed with SHA3-512

## PHP Version Considerations

### Supported Versions

- **PHP 8.2+:** Full support with strict type checking enabled
- **PHP 8.3:** Native support for PHP 8.3 features
- **PHP 8.2:** Uses `symfony/polyfill-php83` for PHP 8.3 features

### Performance Considerations

- **GMP extension:** Recommended (but optional) for optimal performance
  - Provides native arbitrary-precision arithmetic
  - Significantly faster base conversion operations
  - Install via system package manager or `pecl install gmp`
- **Pure PHP fallback:** Available when GMP is not installed
  - No performance impact on security
  - Slower base conversion only

### Security Features

- **Strict types:** Enabled throughout the codebase
- **Type safety:** PHPStan validation at maximum level
- **Immutability:** CUID instances are immutable once created
- **Thread safety:** Singleton pattern for Counter and Fingerprint components

## Security Testing

### Current Test Coverage

- **Code coverage:** 100% (excluding compatibility polyfills)
- **Collision testing:** Validates uniqueness up to 50,000 IDs
- **Format validation:** Ensures proper CUID2 format compliance
- **Length validation:** Tests all valid lengths (4-32 characters)
- **Error handling:** Validates exception handling for invalid states

### Continuous Integration

All security-relevant tests run automatically on:
- Every commit via GitHub Actions
- Pull request validation
- Pre-release verification

## Known Security Issues

No security advisories have been published to date.

**Last Updated:** January 10, 2025

## Security Acknowledgments

We appreciate the security research community's efforts in responsibly disclosing vulnerabilities. Contributors will be credited in release notes and security advisories (unless anonymity is requested).
