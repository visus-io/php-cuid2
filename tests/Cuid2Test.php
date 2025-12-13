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
     * Provides valid CUID2 strings for testing isValid method.
     *
     * @return array<string, array<mixed>>
     */
    public static function validCuidProvider(): array
    {
        return [
            'minimum length' => ['a1b2', 4],
            'short cuid' => ['x7z9k2m1', 8],
            'medium cuid' => ['q5w8e3r6t9y2u1i4', 16],
            'default length' => ['p0o9i8u7y6t5r4e3w2q1a2s3', 24],
            'maximum length' => ['z1x2c3v4b5n6m7q8w9e0r1t2y3u4i5o6', 32],
            'mixed characters' => ['a1b2c3d4e5f6g7h8i9j0k1l2', 24],
            'all letters' => ['abcd', 4],
            'starts with letter and numbers' => ['a123', 4],
        ];
    }

    /**
     * Provides invalid CUID2 strings for testing isValid method.
     *
     * @return array<string, array<mixed>>
     */
    public static function invalidCuidProvider(): array
    {
        return [
            'too short' => ['abc', null],
            'too long' => ['a' . str_repeat('1', 32), null],
            'empty string' => ['', null],
            'contains uppercase' => ['A1b2c3d4', null],
            'contains special chars' => ['a1b2-c3d4', null],
            'contains spaces' => ['a1b2 c3d4', null],
            'contains underscore' => ['a1b2_c3d4', null],
            'starts with number' => ['1abc', null],
            'unicode characters' => ['a1b2ñ3d4', null],
            'contains symbols' => ['a1b2@3d4', null],
        ];
    }

    /**
     * @throws OutOfRangeException|Exception
     */
    public function testGeneratesValidBase36Format(): void
    {
        $cuid = new Cuid2();
        $result = (string) $cuid;

        $this->assertMatchesRegularExpression(
            '/^[a-z][0-9a-z]+$/',
            $result,
            'CUID should start with lowercase letter followed by base36 characters'
        );
    }

    /**
     * @throws OutOfRangeException|Exception
     */
    public function testStringRepresentationsAreConsistent(): void
    {
        $cuid = new Cuid2();

        $toString = $cuid->toString();
        $magicToString = (string) $cuid;
        $jsonSerialize = $cuid->jsonSerialize();

        $this->assertSame($toString, $magicToString);
        $this->assertSame($toString, $jsonSerialize);

        // Test immutability - multiple calls return same value
        $this->assertSame($toString, $cuid->toString());
        $this->assertSame($magicToString, (string) $cuid);
    }

    /**
     * @throws Exception
     *
     * @dataProvider invalidLengthProvider
     */
    public function testThrowsExceptionForInvalidLength(int $length): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('maxLength: cannot be less than 4 or greater than 32.');

        new Cuid2($length);
    }

    /**
     * @throws OutOfRangeException|Exception
     */
    public function testGeneratesDefaultLengthOf24(): void
    {
        $cuid = new Cuid2();
        $result = (string) $cuid;

        $this->assertEquals(24, strlen($result));
        $this->assertMatchesRegularExpression('/^[a-z][0-9a-z]*$/', $result);
    }

    /**
     * @throws OutOfRangeException|Exception
     */
    public function testGeneratesUniqueValuesAcrossInstances(): void
    {
        $cuid1 = new Cuid2();
        $cuid2 = new Cuid2();

        $this->assertNotEquals((string) $cuid1, (string) $cuid2);
    }

    /**
     * @throws OutOfRangeException|Exception
     */
    public function testJsonEncodesCorrectly(): void
    {
        $cuid = new Cuid2();
        $jsonString = json_encode($cuid);
        $this->assertIsString($jsonString);

        $decoded = json_decode($jsonString, true);
        $this->assertIsString($decoded);
        $this->assertEquals((string) $cuid, $decoded);
        $this->assertMatchesRegularExpression('/^[a-z][0-9a-z]*$/', $decoded);
    }

    /**
     * @throws OutOfRangeException|Exception
     */
    public function testStartsWithLowercaseLetter(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $cuid = new Cuid2();
            $result = (string) $cuid;
            $firstChar = $result[0];

            $this->assertMatchesRegularExpression('/^[a-z]$/', $firstChar);
        }
    }

    /**
     * @throws OutOfRangeException|Exception
     */
    public function testGeneratesUniqueValuesInLargeSample(): void
    {
        $cuids = [];
        $sampleSize = 1000;

        for ($i = 0; $i < $sampleSize; $i++) {
            $cuids[] = (string) new Cuid2();
        }

        $uniqueCuids = array_unique($cuids);
        $this->assertCount($sampleSize, $uniqueCuids);
    }

    /**
     * @throws Exception
     *
     * @dataProvider validLengthProvider
     */
    public function testGeneratesCorrectLength(int $length): void
    {
        $cuid = new Cuid2($length);
        $result = (string) $cuid;

        $this->assertEquals($length, strlen($result));
        $this->assertMatchesRegularExpression('/^[a-z][0-9a-z]*$/', $result);
    }

    /**
     * @throws OutOfRangeException|Exception
     */
    public function testStaticGenerateCreatesDefaultLength(): void
    {
        $cuid = Cuid2::generate();
        $result = (string) $cuid;

        $this->assertEquals(24, strlen($result));
        $this->assertMatchesRegularExpression('/^[a-z][0-9a-z]*$/', $result);
    }

    /**
     * @throws Exception
     *
     * @dataProvider validLengthProvider
     */
    public function testStaticGenerateCreatesCustomLength(int $length): void
    {
        $cuid = Cuid2::generate($length);
        $result = (string) $cuid;

        $this->assertEquals($length, strlen($result));
        $this->assertMatchesRegularExpression('/^[a-z][0-9a-z]*$/', $result);
    }

    /**
     * @throws Exception
     *
     * @dataProvider invalidLengthProvider
     */
    public function testStaticGenerateThrowsExceptionForInvalidLength(int $length): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('maxLength: cannot be less than 4 or greater than 32.');

        Cuid2::generate($length);
    }

    /**
     * @throws OutOfRangeException|Exception
     */
    public function testStaticGenerateProducesUniqueValues(): void
    {
        $cuid1 = Cuid2::generate();
        $cuid2 = Cuid2::generate();

        $this->assertNotEquals((string) $cuid1, (string) $cuid2);
    }

    /**
     * @dataProvider validCuidProvider
     */
    public function testIsValidAcceptsValidCuids(string $cuid, ?int $expectedLength = null): void
    {
        $this->assertTrue(Cuid2::isValid($cuid, $expectedLength));
    }

    /**
     * @dataProvider invalidCuidProvider
     */
    public function testIsValidRejectsInvalidCuids(string $cuid, ?int $expectedLength = null): void
    {
        $this->assertFalse(Cuid2::isValid($cuid, $expectedLength));
    }

    public function testIsValidChecksExpectedLength(): void
    {
        $validCuid = 'a1b2c3d4e5f6g7h8';

        $this->assertTrue(Cuid2::isValid($validCuid, 16));
        $this->assertFalse(Cuid2::isValid($validCuid, 24));
        $this->assertFalse(Cuid2::isValid($validCuid, 8));
    }

    public function testIsValidChecksLengthBounds(): void
    {
        $this->assertTrue(Cuid2::isValid('a1b2', 4));
        $this->assertFalse(Cuid2::isValid('a1b', 3));

        $maxLengthCuid = 'a' . str_repeat('1', 31);
        $this->assertTrue(Cuid2::isValid($maxLengthCuid, 32));

        $tooLongCuid = 'a' . str_repeat('1', 32);
        $this->assertFalse(Cuid2::isValid($tooLongCuid, 33));
    }

    public function testIsValidRejectsInvalidExpectedLength(): void
    {
        // Valid CUID but invalid expectedLength parameter
        $validCuid = 'a1b2c3d4';

        $this->assertFalse(Cuid2::isValid($validCuid, 3)); // expectedLength too small
        $this->assertFalse(Cuid2::isValid($validCuid, 33)); // expectedLength too large
        $this->assertFalse(Cuid2::isValid($validCuid, 0)); // expectedLength zero
        $this->assertFalse(Cuid2::isValid($validCuid, -1)); // expectedLength negative
    }

    /**
     * @throws Exception
     */
    public function testIsValidAcceptsGeneratedCuids(): void
    {
        $lengths = [4, 8, 16, 24, 32];

        foreach ($lengths as $length) {
            $cuid = Cuid2::generate($length);
            $cuidString = (string) $cuid;

            $this->assertTrue(Cuid2::isValid($cuidString));
            $this->assertTrue(Cuid2::isValid($cuidString, $length));

            $wrongLength = $length === 4 ? 8 : 4;
            $this->assertFalse(Cuid2::isValid($cuidString, $wrongLength));
        }
    }
}
