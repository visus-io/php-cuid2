# visus/cuid2

[![Continuous Integration](https://github.com/visus-io/php-cuid2/actions/workflows/ci.yaml/badge.svg)](https://github.com/visus-io/php-cuid2/actions/workflows/ci.yaml)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/f3aa91cf90a44d1cb372ef4aa85442bd)](https://app.codacy.com/gh/visus-io/php-cuid2/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/f3aa91cf90a44d1cb372ef4aa85442bd)](https://app.codacy.com/gh/visus-io/php-cuid2/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_coverage)

[![Latest Stable Version](http://poser.pugx.org/visus/cuid2/v)](https://packagist.org/packages/visus/cuid2)
[![Total Downloads](http://poser.pugx.org/visus/cuid2/downloads)](https://packagist.org/packages/visus/cuid2)
[![License](http://poser.pugx.org/visus/cuid2/license)](https://packagist.org/packages/visus/cuid2)
[![PHP Version Require](http://poser.pugx.org/visus/cuid2/require/php)](https://packagist.org/packages/visus/cuid2)
![TypeCoverage](https://shepherd.dev/github/visus-io/php-cuid2/coverage.svg)

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
