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
    /**
     * The range for the counter value.
     * 476782367 is chosen to provide a large enough space for unique values while fitting within
     * the constraints of the CUID2 specification. This value is derived from the maximum safe
     * integer range for the algorithm, ensuring uniform distribution and minimizing bias when
     * generating random values. See https://github.com/paralleldrive/cuid2 for details.
     */
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
        // The threshold of half PHP_INT_MAX is chosen because, for values of $max below this,
        // the probability of bias in the modulus operation increases significantly, and the
        // number of attempts required for bias-free sampling may become impractically large.
        // This is a pragmatic balance between statistical correctness and performance.
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
