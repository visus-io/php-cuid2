<?php

declare(strict_types=1);

namespace Visus\Cuid2;

use Exception;

/**
 * Singleton responsible for keeping track of iterations.
 *
 * @internal
 * @psalm-internal Visus\Cuid2
 */
final class Counter
{
    private const RANGE = 476782367;

    private const MAX_ATTEMPTS = 1000;

    private static ?Counter $instance = null;

    /**
     * @psalm-readonly-allow-private-mutation
     */
    private int $value;

    private function __construct()
    {
        $max = PHP_INT_MAX - (PHP_INT_MAX % self::RANGE);

        // Fallback: If the bias-free range is insufficient (i.e., max is less than half of PHP_INT_MAX),
        // use a simple modulus operation, which may introduce bias but is acceptable as a last resort.
        if ($max < PHP_INT_MAX / 2) {
            $this->value = random_int(0, PHP_INT_MAX) % self::RANGE;
            return;
        }

        $attempts = 0;

        do {
            if (++$attempts > self::MAX_ATTEMPTS) {
                $this->value = random_int(0, PHP_INT_MAX) % self::RANGE;
                return;
            }

            $randomInt = random_int(0, PHP_INT_MAX);
        } while ($randomInt >= $max);

        $this->value = $randomInt % self::RANGE;
    }

    /**
     * Gets the current instance.
     *
     * @return Counter
     */
    public static function getInstance(): Counter
    {
        return self::$instance ??= new Counter();
    }

    /**
     * Gets the next value from the current instance.
     *
     * @return int
     */
    public function getNextValue(): int
    {
        return $this->value++;
    }
}
