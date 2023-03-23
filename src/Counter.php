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
     * @var Counter|null
     */
    private static ?Counter $instance = null;

    /**
     * @psalm-readonly-allow-private-mutation
     */
    private int $value;

    /**
     * @throws Exception
     */
    private function __construct()
    {
        $this->value = (int)(random_int(PHP_INT_MIN, PHP_INT_MAX) * 476782367);
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
