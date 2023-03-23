# visus/cuid2

![GitHub](https://img.shields.io/github/license/visus-io/php-cuid2?logo=github) [![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=php-cuid2&metric=alert_status)](https://sonarcloud.io/summary/overall?id=php-cuid2) [![Coverage](https://sonarcloud.io/api/project_badges/measure?project=php-cuid2&metric=coverage)](https://sonarcloud.io/summary/overall?id=php-cuid2) ![TypeCoverage](https://shepherd.dev/github/visus/php-cuid2/coverage.svg) [![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=php-cuid2&metric=security_rating)](https://sonarcloud.io/summary/overall?id=php-cuid2)

[![Packagist Version (including pre-releases)](https://img.shields.io/packagist/v/visus/cuid2?include_prereleases)](https://packagist.org/packages/visus/cuid2) [![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/visus/cuid2)](https://packagist.org/packages/visus/cuid2)

A PHP implementation of collision-resistant ids. You can read more about CUIDs from the [official project website](https://github.com/paralleldrive/cuid2).

## Getting Started

You can install visus/cuid2 as a [composer package](https://packagist.org/packages/visus/cuid2):

```shell
composer require visus/cuid2
```

## Quick Example

```php
<?php
require_once 'vendor/autoload.php';

// new (default length of 24)
$cuid = new Visus\Cuid2\Cuid2();

// implicit casting
echo $cuid; // hw8kkckkgwkk0oo0gkw0o8sg

// explicit casting
echo $cuid->toString(); // hw8kkckkgwkk0oo0gkw0o8sg

// new (with custom length)
$cuid = new Visus\Cuid2\Cuid2(10);
echo $cuid; // psk8844ck4
```
