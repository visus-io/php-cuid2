<?php

declare(strict_types=1);

namespace Xaevik\Cuid2\Test;

use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use Xaevik\Cuid2\Cuid2;

class Cuid2Test extends TestCase
{
    public function testDefaultConstructor(): void
    {
        $cuid = new Cuid2();

        $result = strlen((string)$cuid) === 24 &&
            ctype_alnum((string)$cuid);

        $this->assertTrue($result);
    }

    public function testVariableLengthConstructor(): void
    {
        $cuid = new Cuid2(10);

        $result = strlen((string)$cuid) === 10 &&
            ctype_alnum((string)$cuid);

        $this->assertTrue($result);
    }

    public function testConstructorThrowsOutOfRangeException(): void
    {
        $this->expectException(OutOfRangeException::class);

        $_ = new Cuid2(48);
    }

    public function testExplicitToString(): void
    {
        $cuid = new Cuid2();

        $value = $cuid->toString();

        $result = strlen($value) === 24 &&
            ctype_alnum($value);

        $this->assertTrue($result);
    }
}
