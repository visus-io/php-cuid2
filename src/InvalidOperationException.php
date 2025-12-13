<?php

declare(strict_types=1);

namespace Visus\Cuid2;

use RuntimeException;

/**
 * Exception thrown when an operation cannot be performed due to invalid state or environment.
 */
final class InvalidOperationException extends RuntimeException
{
}
