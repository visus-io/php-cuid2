# Security Policy

## Supported Versions

| Version | Status | Notes |
|---------|--------|-------|
| 6.x | ✅ Supported | Current version |
| 5.x | ✅ Supported | Security patches only |
| < 5.0 | ❌ Unsupported | No longer supported |

## Vulnerability Reporting Process

**Do Not Use Public Issues:** Security vulnerabilities must not be disclosed through GitHub issues.

**Report Privately:** Users should either:
- Use GitHub Security Advisories (Security tab > "Report a vulnerability")
- Email security@projects.visus.io

**Required Information:** Include vulnerability type, affected versions, reproduction steps, potential impact, suggested fixes, and contact details.

**Response Timeline:**
- Initial response: 48 hours
- Status update: 7 days
- Fix timeline varies: Critical (7-14 days), High (30 days), Medium/Low (next release)

**Disclosure Policy:** The maintainers request 90 days before public disclosure and will credit reporters unless anonymity is requested.

## Security Best Practices

This library implements CUID2 (collision-resistant unique identifiers) with SHA3-512 hashing (NIST FIPS-202 compliant). Never use CUIDs as cryptographic secrets or authentication tokens. Consider your threat model—CUID2 suits public-facing URL identifiers and database keys, while dedicated cryptographic libraries serve session tokens and API keys better.

Keep dependencies updated and monitor security advisories through repository watches and Packagist notifications.

## Cryptographic Dependencies

The library relies on PHP's native SHA3-512 implementation (requires PHP compiled with sha3 support) and uses `random_bytes()` for cryptographically secure random number generation. The library will throw `InvalidOperationException` if SHA3-512 is not available.

## PHP Version Considerations

PHP 8.2+ receives full support with strict type checking enabled. The library uses `symfony/polyfill-php83` for PHP 8.3 features on older versions. The GMP extension is recommended (but optional) for optimal performance in base conversion operations.

**Status:** No security advisories have been published to date (last updated January 10, 2025).
