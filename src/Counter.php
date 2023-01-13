<?php
declare(strict_types=1);

namespace Xaevik\Cuid2;

final class Counter
{
    private static self|null $instance = null;

    private int $value;

    private function __construct()
    {
        $this->value = random_int(PHP_INT_MIN, PHP_INT_MAX) * 2057;
    }

    public static function getInstance(): static
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function getNextValue(): int
    {
        return $this->value++;
    }
}