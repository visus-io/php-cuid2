# Security Policy

## Supported Versions

| Version | Status | Notes |
|---------|--------|-------|
| 7.x | ✅ Supported | Current version |
| 6.x | ✅ Supported | Security patches only |
| < 6.0 | ❌ Unsupported | No longer supported |

## Vulnerability Reporting Process

**Please do not report security vulnerabilities through public GitHub issues.**

Report vulnerabilities through one of these methods instead.

### Preferred Method
Open the Security tab. Select "Report a vulnerability." This uses GitHub Security Advisories.

### Alternative Method
Email: security@projects.visus.io

### Required Information
Include this information in your report:
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
We request 90 days before public disclosure. This time lets us develop, test, and deploy a patch. We credit security researchers in release notes. We omit a name if the researcher requests anonymity.

## Security Scope

### In-Scope Security Concerns

We treat these issues as security vulnerabilities:

- **Cryptographic weaknesses** in the SHA3-512 implementation or its use
- **ID predictability** that could let an attacker guess or enumerate identifiers
- **Collision vulnerabilities** beyond the theoretical probability
- **Memory safety issues** (memory leaks, buffer overflows in dependencies)
- **Timing attacks** that could leak information about ID generation
- **Dependency vulnerabilities** (CVEs in required packages)
- **Platform-specific vulnerabilities** that affect ID uniqueness or security
- **Weaknesses in random number generation** that compromise entropy

### Out-of-Scope

We do not treat these issues as security vulnerabilities:

- **Theoretical collision probability** for the default 24-character ID (low by design, not a practical risk)
- **Application-level misuse** (for example, using CUIDs as passwords or cryptographic secrets)
- **Resource exhaustion DoS** from generating large numbers of IDs
- **Non-security build or configuration issues**
- **Performance characteristics**, unless they enable timing attacks
- **Compatibility issues** with unsupported PHP versions (< 8.3)

## Security Best Practices

### Appropriate Use Cases

This library implements CUID2, a collision-resistant unique identifier standard. It uses SHA3-512 hashing, which is NIST FIPS-202 compliant.

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

Consider your threat model when you use CUIDs:
- CUID2 provides collision resistance and unpredictability for identifiers
- For security-critical operations, use dedicated cryptographic libraries
- Evaluate whether identifier enumeration is a concern for your application
- Consider rate limiting for any public ID-generation endpoint

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
- **Compliance:** NIST FIPS-202 compliant, through PHP's native implementation
- **Validation:** The library checks for SHA3-512 availability at runtime
- **Error handling:** Throws `InvalidOperationException` if SHA3-512 is not available

### Random Number Generation

- **Primary:** Uses `random_bytes()` for cryptographically secure random data (CSPRNG)
- **Source:** PHP's native CSPRNG implementation
- **Fallback:** `random_int()` for counter initialization
- **Entropy:** Relies on entropy from the operating system

### Fingerprint Components

The library builds each fingerprint from these sources:
- Hostname (from `gethostname()`, or a fallback)
- Process ID (from `getmypid()`, or a fallback)
- Environment variables
- Cryptographically secure random data

The library hashes all sources together with SHA3-512.

## PHP Version Considerations

### Supported Versions

- **PHP 8.3+:** Required. The library enables strict types on PHP 8.3 and later. It needs no polyfills for language features.
- **PHP 8.4 and 8.5:** Also supported. The CI pipeline tests both.

### Performance Considerations

- **GMP extension:** Recommended, but optional, for the best performance
  - Provides native arbitrary-precision arithmetic
  - Converts to base36 faster than the pure PHP fallback
  - Install it with your system package manager, or run `pecl install gmp`
- **Pure PHP fallback:** Used when the GMP extension is not installed
  - Gives the same security guarantees as the GMP path
  - Only base36 conversion is slower

### Security Features

- **Strict types:** Enabled throughout the codebase
- **Type safety:** PHPStan checks the code at its strictest level (`max`)
- **Immutability:** A `Cuid2` instance cannot change after creation
- **Thread safety:** `Counter` and `Fingerprint` use a singleton pattern to manage shared state safely

## Security Testing

### Current Test Coverage

- **Code coverage:** 100% line and branch coverage, excluding compatibility polyfills
- **Collision testing:** Validates uniqueness up to 50,000 IDs
- **Format validation:** Confirms each ID matches the CUID2 format
- **Length validation:** Tests all valid lengths (4-32 characters)
- **Error handling:** Confirms the library throws the correct exception for each invalid state

### Continuous Integration

All security-relevant tests run automatically on:
- Every commit, through GitHub Actions
- Every pull request
- Every pre-release check

## Known Security Issues

This project has no published security advisories.

**Last Updated:** September 21, 2026

## Security Acknowledgments

We appreciate the security research community. We thank researchers for responsible disclosure of vulnerabilities. We credit contributors in release notes and security advisories. We omit a name if the researcher requests anonymity.
