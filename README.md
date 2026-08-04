# visus/cuid2

[![GitHub Workflow Status (with event)](https://img.shields.io/github/actions/workflow/status/visus-io/php-cuid2/ci.yml?style=for-the-badge&logo=github)](https://github.com/visus-io/php-cuid2/actions/workflows/ci.yaml)

[![Sonar Quality Gate](https://img.shields.io/sonar/quality_gate/visus%3Aphp-cuid2?server=https%3A%2F%2Fsonarcloud.io&style=for-the-badge&logo=sonarcloud&logoColor=white)](https://sonarcloud.io/summary/overall?id=visus%3Aphp-cuid2)
[![Sonar Coverage](https://img.shields.io/sonar/coverage/visus%3Aphp-cuid2?server=https%3A%2F%2Fsonarcloud.io&style=for-the-badge&logo=sonarcloud&logoColor=white)](https://sonarcloud.io/summary/overall?id=visus%3Aphp-cuid2)
[![Sonar Tests](https://img.shields.io/sonar/tests/visus%3Aphp-cuid2?server=https%3A%2F%2Fsonarcloud.io&style=for-the-badge&logo=sonarcloud&logoColor=white)](https://sonarcloud.io/summary/overall?id=visus%3Aphp-cuid2)

![PHP Version](https://img.shields.io/packagist/dependency-v/visus/cuid2/php?style=for-the-badge)
[![Packagist](https://img.shields.io/packagist/v/visus/cuid2?style=for-the-badge&logo=packagist&logoColor=white&label=stable)](https://packagist.org/packages/visus/cuid2)
![Downloads](https://img.shields.io/packagist/dt/visus/cuid2?style=for-the-badge&logo=packagist&logoColor=white&color=8)
![GitHub](https://img.shields.io/github/license/visus-io/cuid.net?style=for-the-badge)

This library is a PHP implementation of CUID2. CUID2 identifiers are secure, URL-safe, and collision-resistant. You can generate these identifiers on multiple machines at the same time. The machines do not need to coordinate with each other.

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
- [Contributing](#contributing)

</details>

## General Overview

CUID2 stands for Collision-resistant Unique Identifier, version 2. CUID2 solves common problems with UUIDs and other identifier systems. This PHP library gives you these features:

- **Collision resistance**: The library combines a timestamp, a counter, and a fingerprint. It hashes these values with SHA3-512. This method prevents collisions.
- **Horizontal scalability**: You can use this library on multiple machines and processes at the same time. The machines do not need to coordinate with each other.
- **Security**: The library uses cryptographically secure random data and hashing.
- **URL safety**: The library encodes each identifier in base36. It uses only the digits 0-9 and the lowercase letters a-z.
- **Sortability**: Each identifier includes a timestamp. You can sort identifiers by time.
- **Configurable length**: You can set the identifier length from 4 to 32 characters. The default length is 24 characters.

For more information about CUID2, go to the [official project website](https://github.com/paralleldrive/cuid2).

## Architecture

### CUID2 Structure

Each CUID2 identifier contains several components. The library combines and hashes these components to make the identifier unique.

```
[prefix][hash]
```

- **Prefix** (1 character): a random lowercase letter, from a to z
- **Hash** (remaining characters): the SHA3-512 hash of the combined components, encoded in base36

### Generation Process

1. **Prefix**: The library adds a random lowercase letter. This adds entropy.
2. **Timestamp**: The library records the current time in milliseconds. This makes the identifier sortable.
3. **Counter**: The library adds a value that always increases. This prevents collisions when you generate identifiers quickly.
4. **Fingerprint**: The library adds an identifier for the machine and the process. This identifier includes the hostname, the process ID, and environment data.
5. **Random Data**: The library adds cryptographically secure random bytes.
6. **Hashing**: The library combines all components and hashes them with SHA3-512.
7. **Encoding**: The library converts the hash from base16 to base36.
8. **Truncation**: The library trims the result to the length you request, minus the prefix.

Example output: `p6p168tx2rxtgyehd3p2wz04`

This design prevents collisions. Even when multiple processes generate identifiers at the same time, the timestamp, the counter, the fingerprint, and the random data keep each identifier unique.

## Getting Started

### Installation

Use [Composer](https://packagist.org/packages/visus/cuid2) to install visus/cuid2:

```shell
composer require visus/cuid2
```

**Requirements:**
- PHP 8.3 or a later version
- SHA3-512 hashing support. PHP 7.1 and later versions usually include this support.

**Recommended:**
- The GMP extension, for better performance. GMP makes base conversion 60 to 300 times faster.

### Instance-Based Usage

```php
<?php
require_once 'vendor/autoload.php';

use Visus\Cuid2\Cuid2;

// Generate an identifier with the default length of 24 characters
$cuid = new Cuid2();

// Cast the identifier to a string implicitly
echo $cuid; // p6p168tx2rxtgyehd3p2wz04

// Cast the identifier to a string explicitly
echo $cuid->toString(); // p6p168tx2rxtgyehd3p2wz04

// Generate an identifier with a custom length, from 4 to 32 characters
$shortCuid = new Cuid2(10);
echo $shortCuid; // a1ao2r0lve
```

### Static-Based Usage

```php
<?php
require_once 'vendor/autoload.php';

use Visus\Cuid2\Cuid2;

// Generate an identifier with the default length of 24 characters
$cuid = Cuid2::generate();

// Cast the identifier to a string implicitly
echo $cuid; // zbc8kp9qqoh3pvseey6m7nrq

// Cast the identifier to a string explicitly
echo $cuid->toString(); // zbc8kp9qqoh3pvseey6m7nrq

// Generate an identifier with a custom length, from 4 to 32 characters
$shortCuid = Cuid2::generate(10);
echo $shortCuid; // rywe9nkxrx
```

### Validation

The `isValid()` method checks whether a string matches the CUID2 format.

> [!NOTE]
> This method validates only the format. It does not guarantee that this library generated the value. It does not guarantee that the value is globally unique.

```php
<?php
require_once 'vendor/autoload.php';

use Visus\Cuid2\Cuid2;

// Validate the format
Cuid2::isValid('p6p168tx2rxtgyehd3p2wz04'); // true
Cuid2::isValid('invalid-cuid'); // false

// Validate the format and check the expected length
Cuid2::isValid('a1ao2r0lve', expectedLength: 10); // true
Cuid2::isValid('a1ao2r0lve', expectedLength: 24); // false
```

## Considerations

### Performance: GMP Extension

This library converts values from base16 to base36 during identifier generation. For the best performance, install and enable the [GMP extension](https://www.php.net/manual/en/intro.gmp.php). This step is **strongly recommended**.

**Performance Comparison** (from benchmark tests):

| Hash Size | GMP Average | Pure PHP Average | Performance Gain |
|-----------|-------------|------------------|------------------|
| SHA3-512 (128 hex chars) | 0.81 μs | 247.77 μs | **306x faster** |
| 64 hex chars | 0.58 μs | 78.51 μs | **135x faster** |
| 32 hex chars | 0.47 μs | 28.08 μs | **60x faster** |
| 16 hex chars | 0.43 μs | 11.94 μs | **28x faster** |

**Key Takeaways:**
- GMP makes base conversion 60 to 300 times faster.
- Larger hashes gain more benefit from GMP. CUID2 uses SHA3-512, the largest hash in this comparison.
- Without GMP, the library uses a pure PHP implementation instead.
- Both implementations produce the same results.

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
# Windows installations often include GMP.
# To enable GMP, remove the semicolon before this line in php.ini:
extension=gmp

# If your installation does not include GMP, download it from PECL.
# Or use a package manager:
# Via Chocolatey: choco install php-gmp
```

To check whether the extension is loaded, run one of these commands. On Unix, run `php -m | grep gmp`. On Windows, run `php -m | findstr gmp`.

## Contributing

You can contribute to this project. See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines about:

- How to set up your development environment
- How to run tests and code quality checks
- Coding standards and conventions
- How to submit a pull request

This project follows the [Conventional Commits](https://www.conventionalcommits.org/) standard. This project maintains 100% code coverage.
