<?php

declare(strict_types=1);

namespace Xaevik\Cuid2;

/**
 * Singleton responsible for keeping track of iterations.
 *
 * @internal
 * @psalm-internal Xaevik\Cuid2
 */
final class Counter
{
    private static self|null $instance = null;

    /**
     * @psalm-readonly-allow-private-mutation
     */
    private int $value;

    private function __construct()
    {
        $this->value = (int)(random_int(PHP_INT_MIN, PHP_INT_MAX) * 2057);
    }

    public static function getInstance(): Counter
    {
        if (is_null(self::$instance)) {
            self::$instance = new Counter();
        }

        return self::$instance;
    }

    public function getNextValue(): int
    {
        return $this->value++;
    }
}
