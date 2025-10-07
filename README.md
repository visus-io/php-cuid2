# visus/cuid2

[![GitHub Workflow Status (with event)](https://img.shields.io/github/actions/workflow/status/visus-io/php-cuid2/ci.yml?style=for-the-badge&logo=github)](https://github.com/visus-io/php-cuid2/actions/workflows/ci.yaml)

[![Sonar Quality Gate](https://img.shields.io/sonar/quality_gate/visus%3Aphp-cuid2?server=https%3A%2F%2Fsonarcloud.io&style=for-the-badge&logo=sonarcloud&logoColor=white)](https://sonarcloud.io/summary/overall?id=visus%3Aphp-cuid2)
[![Sonar Coverage](https://img.shields.io/sonar/coverage/visus%3Aphp-cuid2?server=https%3A%2F%2Fsonarcloud.io&style=for-the-badge&logo=sonarcloud&logoColor=white)](https://sonarcloud.io/summary/overall?id=visus%3Aphp-cuid2)
[![Sonar Tests](https://img.shields.io/sonar/tests/visus%3Aphp-cuid2?server=https%3A%2F%2Fsonarcloud.io&style=for-the-badge&logo=sonarcloud&logoColor=white)](https://sonarcloud.io/summary/overall?id=visus%3Aphp-cuid2)

![PHP Version](https://img.shields.io/packagist/dependency-v/visus/cuid2/php?style=for-the-badge)
[![Packagist](https://img.shields.io/packagist/v/visus/cuid2?style=for-the-badge&logo=packagist&logoColor=white&label=stable)](https://packagist.org/packages/visus/cuid2)
![Downloads](https://img.shields.io/packagist/dt/visus/cuid2?style=for-the-badge&logo=packagist&logoColor=white&color=8)
![GitHub](https://img.shields.io/github/license/visus-io/cuid.net?style=for-the-badge)

A PHP implementation of collision-resistant ids. You can read more about CUIDs from the [official project website](https://github.com/paralleldrive/cuid2).

## Getting Started

You can install visus/cuid2 as a [composer package](https://packagist.org/packages/visus/cuid2):

```shell
composer require visus/cuid2
```
> [!TIP]
> Consider installing/enabling the PHP extension [GMP](https://www.php.net/manual/en/intro.gmp.php).
> If this is not an option then [markrogoyski/math-php](https://github.com/markrogoyski/math-php) will be used as a fallback.

## Quick Example

### Instance Based

```php
<?php
require_once 'vendor/autoload.php';

// new (default length of 24)
$cuid = new Visus\Cuid2\Cuid2();

// implicit casting
echo $cuid; // apr5hhh4ox45krsg9gycbs9k

// explicit casting
echo $cuid->toString(); // apr5hhh4ox45krsg9gycbs9k

// new (with custom length)
$cuid = new Visus\Cuid2\Cuid2(10);
echo $cuid; // pekw02xwsd
```
### Static Based

```php
<?php
require_once 'vendor/autoload.php';

// new (default length of 24)
$cuid = Visus\Cuid2\Cuid2::generate();

// implicit casting
echo $cuid; // apr5hhh4ox45krsg9gycbs9k

// explicit casting
echo $cuid->toString(); // apr5hhh4ox45krsg9gycbs9k

// new (with custom length)
$cuid = Visus\Cuid2\Cuid2::generate(10);
echo $cuid; // pekw02xwsd
```
