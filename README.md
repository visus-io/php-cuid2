# xaevik/cuid2

![GitHub](https://img.shields.io/github/license/xaevik/php-cuid2?logo=github) [![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=php-cuid2&metric=alert_status)](https://sonarcloud.io/summary/overall?id=php-cuid2) [![Coverage](https://sonarcloud.io/api/project_badges/measure?project=php-cuid2&metric=coverage)](https://sonarcloud.io/summary/overall?id=php-cuid2) [![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=php-cuid2&metric=security_rating)](https://sonarcloud.io/summary/overall?id=php-cuid2)

[![Packagist Version (including pre-releases)](https://img.shields.io/packagist/v/xaevik/cuid2?include_prereleases)](https://packagist.org/packages/xaevik/cuid2) [![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/xaevik/cuid2)](https://packagist.org/packages/xaevik/cuid2) ![Packagist Downloads](https://img.shields.io/packagist/dt/xaevik/cuid2)

A PHP implementation of collision-resistant ids. You can read more about CUIDs from the [official project website](https://github.com/paralleldrive/cuid2).

## Getting Started

You can install xaevik/cuid2 as a [composer package](https://packagist.org/packages/xaevik/cuid2):

```shell
composer require xaevik/cuid2
```

## Quick Example

```php
<?php
require_once 'vendor/autoload.php';

// new (default length of 24)
$cuid = new Xaevik\Cuid2\Cuid2();
echo $cuid; // hw8kkckkgwkk0oo0gkw0o8sg

// new (with custom length)
$cuid = new Xaevik\Cuid2\Cuid2(10);
echo $cuid; // psk8844ck4
```
