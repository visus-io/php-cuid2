# visus/cuid2

[![GitHub Workflow Status (with event)](https://img.shields.io/github/actions/workflow/status/visus-io/php-cuid2/ci.yml?style=for-the-badge&logo=github)](https://github.com/visus-io/php-cuid2/actions/workflows/ci.yaml)

[![Sonar Quality Gate](https://img.shields.io/sonar/quality_gate/visus%3Aphp-cuid2?server=https%3A%2F%2Fsonarcloud.io&style=for-the-badge&logo=sonarcloud&logoColor=white)](https://sonarcloud.io/summary/overall?id=visus%3Aphp-cuid2)
[![Sonar Coverage](https://img.shields.io/sonar/coverage/visus%3Aphp-cuid2?server=https%3A%2F%2Fsonarcloud.io&style=for-the-badge&logo=sonarcloud&logoColor=white)](https://sonarcloud.io/summary/overall?id=visus%3Aphp-cuid2)
[![Sonar Tests](https://img.shields.io/sonar/tests/visus%3Aphp-cuid2?server=https%3A%2F%2Fsonarcloud.io&style=for-the-badge&logo=sonarcloud&logoColor=white)](https://sonarcloud.io/summary/overall?id=visus%3Aphp-cuid2)

![PHP Version](https://img.shields.io/packagist/dependency-v/visus/cuid2/php?style=for-the-badge)
[![Packagist](https://img.shields.io/packagist/v/visus/cuid2?style=for-the-badge&logo=packagist&logoColor=white&label=stable)](https://packagist.org/packages/visus/cuid2)
![Downloads](https://img.shields.io/packagist/dt/visus/cuid2?style=for-the-badge&logo=packagist&logoColor=white&color=8)
![GitHub](https://img.shields.io/github/license/visus-io/cuid.net?style=for-the-badge)

A PHP implementation of collision-resistant identifiers that are secure, URL-safe, and horizontally scalable. Perfect for distributed systems where unique identifiers need to be generated across multiple machines without coordination.

<details>
<summary>Table of Contents</summary>

- [General Overview](#general-overview)
- [Architecture](#architecture)
  - [CUID2 Structure](#cuid2-structure)
  - [Generation Process](#generation-process)
- [Getting Started](#getting-started)
  - [Installation](#installation)
  - [Instance-Based Usage](#instance-based-usage)
  - [Static-Based Usage](#static-based-usage)
  - [Validation](#validation)
- [Considerations](#considerations)
  - [Performance: GMP Extension](#performance-gmp-extension)

</details>

## General Overview

CUID2 (Collision-resistant Unique Identifier, version 2) is a modern approach to generating unique identifiers that addresses common issues with UUIDs and other identification systems. This PHP implementation provides:

- **Collision Resistance**: Uses cryptographic hashing (SHA3-512) combined with timestamps, counters, and fingerprints to ensure uniqueness
- **Horizontal Scalability**: Safe to use across multiple machines and processes without coordination
- **Security**: Employs cryptographically secure random generation and hashing
- **URL-Safe**: Uses base36 encoding (0-9, a-z) with lowercase letters only
- **Sortability**: Incorporates timestamps for chronological ordering
- **Configurable Length**: Supports identifier lengths from 4 to 32 characters (default 24)

You can read more about CUIDs from the [official project website](https://github.com/paralleldrive/cuid2).

## Architecture

### CUID2 Structure

Each CUID2 identifier is composed of several components that are combined and hashed to ensure uniqueness:

```
[prefix][hash]
```

- **Prefix** (1 character): Random lowercase letter (a-z)
- **Hash** (remaining characters): Base36-encoded SHA3-512 hash of combined components

### Generation Process

1. **Prefix**: Random lowercase letter for additional entropy
2. **Timestamp**: Current time in milliseconds for sortability
3. **Counter**: Monotonically increasing value (prevents collisions for rapid generation)
4. **Fingerprint**: Machine/process identifier (hostname + PID + environment)
5. **Random Data**: Cryptographically secure random bytes
6. **Hashing**: All components are combined and hashed using SHA3-512
7. **Encoding**: Hash is converted from base16 to base36
8. **Truncation**: Result is trimmed to requested length (minus prefix)

Example output: `p6p168tx2rxtgyehd3p2wz04`

This architecture ensures that even if multiple processes generate CUIDs simultaneously, the combination of timestamp, counter, fingerprint, and random data prevents collisions.

## Getting Started

### Installation

Install visus/cuid2 via [Composer](https://packagist.org/packages/visus/cuid2):

```shell
composer require visus/cuid2
```

**Requirements:**
- PHP 8.2 or higher
- SHA3-512 hashing algorithm support (typically available in PHP 7.1+)

**Recommended:**
- GMP extension for optimal performance (60-300x faster base conversion)

### Instance-Based Usage

```php
<?php
require_once 'vendor/autoload.php';

use Visus\Cuid2\Cuid2;

// Generate with default length of 24 characters
$cuid = new Cuid2();

// Implicit casting
echo $cuid; // p6p168tx2rxtgyehd3p2wz04

// Explicit casting
echo $cuid->toString(); // p6p168tx2rxtgyehd3p2wz04

// Generate with custom length (4-32 characters)
$shortCuid = new Cuid2(10);
echo $shortCuid; // a1ao2r0lve
```

### Static-Based Usage

```php
<?php
require_once 'vendor/autoload.php';

use Visus\Cuid2\Cuid2;

// Generate with default length of 24 characters
$cuid = Cuid2::generate();

// Implicit casting
echo $cuid; // zbc8kp9qqoh3pvseey6m7nrq

// Explicit casting
echo $cuid->toString(); // zbc8kp9qqoh3pvseey6m7nrq

// Generate with custom length (4-32 characters)
$shortCuid = Cuid2::generate(10);
echo $shortCuid; // rywe9nkxrx
```

### Validation

The `isValid()` method checks if a string follows the CUID2 format.

> [!NOTE]
> This method validates the format only. It does not guarantee that the value was generated by this library or is globally unique.

```php
<?php
require_once 'vendor/autoload.php';

use Visus\Cuid2\Cuid2;

// Validate format
Cuid2::isValid('p6p168tx2rxtgyehd3p2wz04'); // true
Cuid2::isValid('invalid-cuid'); // false

// Validate with expected length
Cuid2::isValid('a1ao2r0lve', expectedLength: 10); // true
Cuid2::isValid('a1ao2r0lve', expectedLength: 24); // false
```

## Considerations

### Performance: GMP Extension

This library uses base conversion (base16 to base36) as part of the CUID2 generation process. For optimal performance, it is **strongly recommended** to install or enable the [GMP extension](https://www.php.net/manual/en/intro.gmp.php).

**Performance Comparison** (based on benchmarks):

| Hash Size | GMP Average | Pure PHP Average | Performance Gain |
|-----------|-------------|------------------|------------------|
| SHA3-512 (128 hex chars) | 0.81 μs | 247.77 μs | **306x faster** |
| 64 hex chars | 0.58 μs | 78.51 μs | **135x faster** |
| 32 hex chars | 0.47 μs | 28.08 μs | **60x faster** |
| 16 hex chars | 0.43 μs | 11.94 μs | **28x faster** |

**Key Takeaways:**
- GMP provides 60-300x faster base conversion
- Larger hashes benefit more from GMP (CUID2 uses SHA3-512, the largest case)
- Without GMP, the library falls back to a pure PHP implementation
- Both implementations produce identical results

**Installation:**

```shell
# Ubuntu/Debian
sudo apt-get install php-gmp

# Fedora/RHEL/CentOS/AlmaLinux/Rocky Linux
sudo dnf install php-gmp
# Or on older systems: sudo yum install php-gmp

# FreeBSD
sudo pkg install php-gmp
# Or via ports: cd /usr/ports/math/php-gmp && make install clean

# NetBSD
sudo pkgin install php-gmp
# Or via pkgsrc: cd /usr/pkgsrc/math/php-gmp && make install

# OpenBSD
sudo pkg_add php-gmp
# Or via ports: cd /usr/ports/math/php-gmp && make install

# macOS (via Homebrew)
brew install gmp
pecl install gmp

# Windows
# GMP is often bundled with PHP installations
# Enable in php.ini by uncommenting:
extension=gmp

# If not bundled, download from PECL or use package manager:
# Via Chocolatey: choco install php-gmp
```

Run `php -m | grep gmp` (Unix) or `php -m | findstr gmp` (Windows) to verify the extension is loaded.
