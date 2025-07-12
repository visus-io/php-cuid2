<?php

declare(strict_types=1);

namespace Visus\Cuid2\Test;

use Exception;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use Visus\Cuid2\Cuid2;

class Cuid2Test extends TestCase
{
    /**
     * Provides invalid lengths for CUID2.
     *
     * @return array<string, array<int>>
     */
    public static function invalidLengthProvider(): array
    {
        return [
            'too small' => [3],
            'negative' => [-1],
            'zero' => [0],
            'too large' => [33],
            'way too large' => [100],
        ];
    }

    /**
     * Provides valid lengths for CUID2.
     *
     * @return array<string, array<int>>
     */
    public static function validLengthProvider(): array
    {
        return [
            'minimum length' => [4],
            'small length' => [8],
            'medium length' => [16],
            'default length' => [24],
            'maximum length' => [32],
        ];
    }

    /**
     * Tests that the generated CUID2 contains only base36 characters (0-9, a-z).
     *
     * @throws OutOfRangeException|Exception
     */
    public function testBase36Characters(): void
    {
        $cuid = new Cuid2();
        $result = (string)$cuid;

        // Should only contain characters from 0-9 and a-z
        $this->assertMatchesRegularExpression('/^[0-9a-z]+$/', $result);
    }

    /**
     * Tests that the string representation of CUID2 is consistent across methods.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testConsistentStringRepresentation(): void
    {
        $cuid = new Cuid2();

        $toString = $cuid->toString();
        $magicToString = (string)$cuid;
        $jsonSerialize = $cuid->jsonSerialize();

        $this->assertEquals($toString, $magicToString);
        $this->assertEquals($toString, $jsonSerialize);
    }

    /**
     * Tests the constructor with a length of 24.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testConstructorThrowsOutOfRangeException(): void
    {
        $this->expectException(OutOfRangeException::class);

        $_ = new Cuid2(48);
    }

    /**
     * Tests the default constructor of Cuid2.
     *
     * @throws OutOfRangeException
     */
    public function testDefaultConstructor(): void
    {
        $cuid = new Cuid2();

        $result = strlen((string)$cuid) === 24 &&
            ctype_alnum((string)$cuid);

        $this->assertTrue($result);
    }

    /**
     * Tests that different instances of Cuid2 generate different values.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testDifferentInstancesHaveDifferentValues(): void
    {
        $cuid1 = new Cuid2();
        $cuid2 = new Cuid2();

        $this->assertNotEquals((string)$cuid1, (string)$cuid2);
    }

    /**
     * Tests the constructor with a length of 4.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testExplicitToString(): void
    {
        $cuid = new Cuid2();

        $value = $cuid->toString();

        $result = strlen($value) === 24 &&
            ctype_alnum($value);

        $this->assertTrue($result);
    }

    /**
     * Tests the implicit string conversion of Cuid2.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testImplicitToString(): void
    {
        $cuid = new Cuid2();

        ob_start();
        echo $cuid;
        $value = ob_get_contents();
        ob_end_clean();

        $result = strlen((string)$value) === 24 &&
            ctype_alnum($value);

        $this->assertTrue($result);
    }

    /**
     * @dataProvider invalidLengthProvider
     * @throws Exception
     */
    public function testInvalidLengthThrowsException(int $length): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("maxLength: cannot be less than 4 or greater than 32.");

        new Cuid2($length);
    }

    /**
     * Tests the JSON encoding of Cuid2.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testJsonEncodeIntegration(): void
    {
        $cuid = new Cuid2();
        $jsonString = json_encode($cuid);

        $this->assertIsString($jsonString);
        $this->assertStringStartsWith('"', $jsonString);
        $this->assertStringEndsWith('"', $jsonString);

        $decoded = json_decode($jsonString, true);
        $this->assertIsString($decoded);
        $this->assertEquals(24, strlen($decoded));
    }

    /**
     * Tests the JSON serialization of Cuid2.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testJsonSerialize(): void
    {
        $cuid = new Cuid2();
        $jsonValue = $cuid->jsonSerialize();

        $this->assertEquals(24, strlen($jsonValue));
        $this->assertTrue(ctype_alnum($jsonValue));
    }

    /**
     * Tests that the length of the generated CUID2 matches the specified length.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testLengthConsistencyAcrossInstances(): void
    {
        $lengths = [4, 8, 12, 16, 20, 24, 28, 32];

        foreach ($lengths as $length) {
            $cuid1 = new Cuid2($length);
            $cuid2 = new Cuid2($length);

            $this->assertEquals($length, strlen((string)$cuid1));
            $this->assertEquals($length, strlen((string)$cuid2));
            $this->assertNotEquals((string)$cuid1, (string)$cuid2);
        }
    }

    /**
     * Tests the memory usage of CUID2 generation.
     *
     * This test checks that generating multiple CUID2 instances does not consume excessive memory.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testMemoryUsage(): void
    {
        $initialMemory = memory_get_usage();

        $cuids = [];
        for ($i = 0; $i < 100; $i++) {
            $cuids[] = new Cuid2();
        }

        $finalMemory = memory_get_usage();
        $memoryUsed = $finalMemory - $initialMemory;

        // Memory usage should be reasonable (adjust threshold as needed)
        $this->assertLessThan(5 * 1024 * 1024, $memoryUsed, 'Memory usage should be reasonable');
    }

    /**
     * Tests that multiple calls to the string representation return the same value.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testMultipleCallsReturnSameValue(): void
    {
        $cuid = new Cuid2();

        $first = $cuid->toString();
        $second = $cuid->toString();
        $third = (string)$cuid;
        $fourth = $cuid->jsonSerialize();

        $this->assertEquals($first, $second);
        $this->assertEquals($first, $third);
        $this->assertEquals($first, $fourth);
    }

    /**
     * Tests that the generated CUID2 does not contain any special characters.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testNoSpecialCharacters(): void
    {
        $cuid = new Cuid2();
        $result = (string)$cuid;

        // Should not contain any special characters, spaces, or uppercase letters
        $this->assertDoesNotMatchRegularExpression('/[^0-9a-z]/', $result);
    }

    /**
     * Tests the performance of CUID2 generation with multiple instances.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testPerformanceWithMultipleInstances(): void
    {
        $startTime = microtime(true);
        $count = 100;

        for ($i = 0; $i < $count; $i++) {
            $cuid = new Cuid2();
            $result = (string)$cuid;
            $this->assertNotEmpty($result);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should complete within reasonable time (adjust as needed)
        $this->assertLessThan(5.0, $executionTime, 'CUID generation should be performant');
    }

    /**
     * Tests that the prefix of CUID2 is a lowercase letter.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testPrefixIsLowercaseLetter(): void
    {
        $cuid = new Cuid2();
        $result = (string)$cuid;
        $firstChar = $result[0];

        $this->assertTrue(ctype_alpha($firstChar));
        $this->assertTrue(ctype_lower($firstChar));
        $this->assertGreaterThanOrEqual('a', $firstChar);
        $this->assertLessThanOrEqual('z', $firstChar);
    }

    /**
     * Tests the uniqueness of generated CUIDs.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testUniqueness(): void
    {
        $cuids = [];
        $count = 1000;

        for ($i = 0; $i < $count; $i++) {
            $cuids[] = (string)new Cuid2();
        }

        $uniqueCuids = array_unique($cuids);
        $this->assertCount($count, $uniqueCuids, 'All CUIDs should be unique');
    }

    /**
     * @dataProvider validLengthProvider
     * @throws Exception
     */
    public function testValidLengthRange(int $length): void
    {
        $cuid = new Cuid2($length);
        $result = (string)$cuid;

        $this->assertEquals($length, strlen($result));
        $this->assertTrue(ctype_alnum($result));
    }

    /**
     * Tests the constructor with a variable length.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testVariableLengthConstructor(): void
    {
        $cuid = new Cuid2(10);

        $result = strlen((string)$cuid) === 10 &&
            ctype_alnum((string)$cuid);

        $this->assertTrue($result);
    }
}
