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
     * Tests that the generated CUID2 contains only valid base36 characters.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testContainsOnlyBase36Characters(): void
    {
        $cuid = new Cuid2();
        $result = (string)$cuid;

        $this->assertMatchesRegularExpression(
            '/^[0-9a-z]+$/',
            $result,
            'CUID should only contain base36 characters (0-9, a-z)'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/[A-Z]/',
            $result,
            'CUID should not contain uppercase letters'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/[^0-9a-z]/',
            $result,
            'CUID should not contain special characters or spaces'
        );
    }

    /**
     * Tests that the string representation of CUID2 is consistent across all methods.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testStringRepresentationConsistency(): void
    {
        $cuid = new Cuid2();

        $toString = $cuid->toString();
        $magicToString = (string)$cuid;
        $jsonSerialize = $cuid->jsonSerialize();

        $this->assertSame($toString, $magicToString, 'toString() and __toString() should return identical values');
        $this->assertSame($toString, $jsonSerialize, 'toString() and jsonSerialize() should return identical values');

        // Test immutability - multiple calls return same value
        $this->assertSame($toString, $cuid->toString(), 'Multiple calls to toString() should return same value');
        $this->assertSame($magicToString, (string)$cuid, 'Multiple casts to string should return same value');
    }

    /**
     * Tests that invalid lengths throw appropriate exceptions.
     *
     * @dataProvider invalidLengthProvider
     * @throws Exception
     */
    public function testInvalidLengthThrowsException(int $length): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('maxLength: cannot be less than 4 or greater than 32.');

        new Cuid2($length);
    }

    /**
     * Tests the default constructor produces correct format.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testDefaultConstructor(): void
    {
        $cuid = new Cuid2();
        $result = (string)$cuid;

        $this->assertEquals(24, strlen($result), 'Default CUID should be 24 characters long');
        $this->assertTrue(ctype_alnum($result), 'Default CUID should be alphanumeric');
        $this->assertMatchesRegularExpression(
            '/^[a-z][0-9a-z]*$/',
            $result,
            'CUID should start with lowercase letter'
        );
    }

    /**
     * Tests that different instances generate unique values.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testUniquenessAcrossInstances(): void
    {
        $cuid1 = new Cuid2();
        $cuid2 = new Cuid2();

        $this->assertNotEquals(
            (string)$cuid1,
            (string)$cuid2,
            'Different CUID instances should generate unique values'
        );
    }

    /**
     * Tests explicit toString method.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testExplicitToString(): void
    {
        $cuid = new Cuid2();
        $value = $cuid->toString();

        $this->assertEquals(24, strlen($value), 'toString() should return 24 character string');
        $this->assertTrue(ctype_alnum($value), 'toString() result should be alphanumeric');
        $this->assertMatchesRegularExpression(
            '/^[a-z]/',
            $value,
            'toString() result should start with lowercase letter'
        );
    }

    /**
     * Tests implicit string conversion.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testImplicitStringConversion(): void
    {
        $cuid = new Cuid2();

        $castValue = (string)$cuid;
        $this->assertEquals(24, strlen($castValue), 'Cast to string should return 24 character string');
        $this->assertTrue(ctype_alnum($castValue), 'Cast to string result should be alphanumeric');

        ob_start();
        echo $cuid;
        $echoValue = ob_get_contents();
        ob_end_clean();

        $echoValue = !empty($echoValue) ? $echoValue : '';

        $this->assertEquals(24, strlen($echoValue), 'Echo output should be 24 characters long');
        $this->assertTrue(ctype_alnum($echoValue), 'Echo output should be alphanumeric');
        $this->assertEquals($castValue, $echoValue, 'Cast and echo should produce same result');
    }

    /**
     * Tests JSON encoding integration.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testJsonEncodingIntegration(): void
    {
        $cuid = new Cuid2();
        $jsonString = json_encode($cuid);

        $this->assertIsString($jsonString, 'json_encode should return string');
        $this->assertStringStartsWith('"', $jsonString, 'JSON string should start with quote');
        $this->assertStringEndsWith('"', $jsonString, 'JSON string should end with quote');

        $decoded = json_decode($jsonString, true);
        $this->assertIsString($decoded, 'Decoded JSON should be string');
        $this->assertEquals(24, strlen($decoded), 'Decoded CUID should be 24 characters');
        $this->assertMatchesRegularExpression(
            '/^[a-z][0-9a-z]*$/',
            $decoded,
            'Decoded CUID should have correct format'
        );
    }

    /**
     * Tests JSON serialization method.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testJsonSerialize(): void
    {
        $cuid = new Cuid2();
        $jsonValue = $cuid->jsonSerialize();

        $this->assertEquals(24, strlen($jsonValue), 'JSON serialized value should be 24 characters');
        $this->assertTrue(ctype_alnum($jsonValue), 'JSON serialized value should be alphanumeric');
        $this->assertMatchesRegularExpression(
            '/^[a-z]/',
            $jsonValue,
            'JSON serialized value should start with lowercase letter'
        );
    }

    /**
     * Tests length consistency across different constructor parameters.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testLengthConsistencyWithDifferentLengths(): void
    {
        $lengths = [4, 8, 12, 16, 20, 24, 28, 32];

        foreach ($lengths as $length) {
            $cuid1 = new Cuid2($length);
            $cuid2 = new Cuid2($length);

            $result1 = (string)$cuid1;
            $result2 = (string)$cuid2;

            $this->assertEquals($length, strlen($result1), "CUID with length $length should have correct length");
            $this->assertEquals($length, strlen($result2), "CUID with length $length should have correct length");
            $this->assertNotEquals($result1, $result2, 'Different instances with same length should be unique');

            // Verify format for each length
            $this->assertMatchesRegularExpression(
                '/^[a-z][0-9a-z]*$/',
                $result1,
                "CUID with length $length should have correct format"
            );

            $this->assertMatchesRegularExpression(
                '/^[a-z][0-9a-z]*$/',
                $result2,
                "CUID with length $length should have correct format"
            );
        }
    }

    /**
     * Tests that the prefix is always a lowercase letter.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testPrefixIsAlwaysLowercaseLetter(): void
    {
        // Test multiple instances to verify consistency
        for ($i = 0; $i < 50; $i++) {
            $cuid = new Cuid2();
            $result = (string)$cuid;
            $firstChar = $result[0];

            $this->assertTrue(ctype_alpha($firstChar), 'First character should be alphabetic');
            $this->assertTrue(ctype_lower($firstChar), 'First character should be lowercase');
            $this->assertGreaterThanOrEqual('a', $firstChar, 'First character should be >= "a"');
            $this->assertLessThanOrEqual('z', $firstChar, 'First character should be <= "z"');
        }
    }

    /**
     * Tests uniqueness of generated CUIDs with larger sample size.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testUniquenessWithLargeSample(): void
    {
        $cuids = [];
        $sampleSize = 1000;

        for ($i = 0; $i < $sampleSize; $i++) {
            $cuids[] = (string)new Cuid2();
        }

        $uniqueCuids = array_unique($cuids);
        $this->assertCount($sampleSize, $uniqueCuids, "All $sampleSize generated CUIDs should be unique");

        // Verify each CUID has correct format
        foreach ($cuids as $index => $cuid) {
            $this->assertMatchesRegularExpression(
                '/^[a-z][0-9a-z]*$/',
                $cuid,
                "CUID at index $index should have correct format"
            );
        }
    }

    /**
     * @dataProvider validLengthProvider
     * @throws Exception
     */
    public function testValidLengthsProduceCorrectFormat(int $length): void
    {
        $cuid = new Cuid2($length);
        $result = (string)$cuid;

        $this->assertEquals($length, strlen($result), "CUID should have requested length of $length");
        $this->assertTrue(ctype_alnum($result), 'CUID should be alphanumeric');
        $this->assertMatchesRegularExpression(
            '/^[a-z][0-9a-z]*$/',
            $result,
            'CUID should start with lowercase letter and contain only base36 characters'
        );
    }

    /**
     * Tests variable length constructor with specific length.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testVariableLengthConstructor(): void
    {
        $length = 10;
        $cuid = new Cuid2($length);
        $result = (string)$cuid;

        $this->assertEquals($length, strlen($result), "CUID should have requested length of $length");
        $this->assertTrue(ctype_alnum($result), 'CUID should be alphanumeric');
        $this->assertMatchesRegularExpression(
            '/^[a-z]/',
            $result,
            'CUID should start with lowercase letter'
        );
    }

    /**
     * Tests that CUIDs maintain format consistency across different lengths.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testFormatConsistencyAcrossLengths(): void
    {
        $lengths = [4, 8, 16, 24, 32];

        foreach ($lengths as $length) {
            $cuid = new Cuid2($length);
            $result = (string)$cuid;

            // All CUIDs should follow the same format rules regardless of length
            $this->assertMatchesRegularExpression(
                '/^[a-z][0-9a-z]*$/',
                $result,
                "CUID with length $length should follow format rules"
            );

            $this->assertEquals($length, strlen($result), "CUID should have exact length $length");

            // Verify no invalid characters
            $this->assertDoesNotMatchRegularExpression(
                '/[^0-9a-z]/',
                $result,
                'CUID should not contain invalid characters'
            );
        }
    }

    /**
     * Tests the static generate method.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testStaticGenerateMethod(): void
    {
        $cuid = Cuid2::generate();
        $result = (string)$cuid;

        $this->assertEquals(24, strlen($result), 'Generated CUID should be 24 characters long');
        $this->assertTrue(ctype_alnum($result), 'Generated CUID should be alphanumeric');
        $this->assertMatchesRegularExpression(
            '/^[a-z][0-9a-z]*$/',
            $result,
            'Generated CUID should start with lowercase letter'
        );
    }

    /**
     * Tests the static generate method with custom length.
     *
     * @dataProvider validLengthProvider
     * @throws Exception
     */
    public function testStaticGenerateMethodWithLength(int $length): void
    {
        $cuid = Cuid2::generate($length);
        $result = (string)$cuid;

        $this->assertEquals($length, strlen($result), "Generated CUID should have requested length of $length");
        $this->assertTrue(ctype_alnum($result), 'Generated CUID should be alphanumeric');
        $this->assertMatchesRegularExpression(
            '/^[a-z][0-9a-z]*$/',
            $result,
            'Generated CUID should start with lowercase letter and contain only base36 characters'
        );
    }

    /**
     * Tests that static generate method throws exception for invalid lengths.
     *
     * @dataProvider invalidLengthProvider
     * @throws Exception
     */
    public function testStaticGenerateMethodThrowsExceptionForInvalidLength(int $length): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('maxLength: cannot be less than 4 or greater than 32.');

        Cuid2::generate($length);
    }

    /**
     * Tests that static generate method produces unique values.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testStaticGenerateMethodUniqueness(): void
    {
        $cuid1 = Cuid2::generate();
        $cuid2 = Cuid2::generate();

        $this->assertNotEquals(
            (string)$cuid1,
            (string)$cuid2,
            'Different calls to generate() should produce unique values'
        );
    }

    /**
     * Tests equivalence between constructor and static generate method.
     *
     * @throws OutOfRangeException|Exception
     */
    public function testConstructorAndGenerateMethodEquivalence(): void
    {
        $length = 16;

        // Both methods should produce CUIDs with identical characteristics
        $constructorCuid = new Cuid2($length);
        $generateCuid = Cuid2::generate($length);

        $constructorResult = (string)$constructorCuid;
        $generateResult = (string)$generateCuid;

        // Should have same length and format, but different values
        $this->assertEquals(strlen($constructorResult),
            strlen($generateResult),
            'Both methods should produce same length'
        );

        $this->assertMatchesRegularExpression('/^[a-z][0-9a-z]*$/',
            $constructorResult,
            'Constructor result should have correct format'
        );

        $this->assertMatchesRegularExpression('/^[a-z][0-9a-z]*$/',
            $generateResult,
            'Generate result should have correct format'
        );

        $this->assertNotEquals($constructorResult, $generateResult, 'Results should be unique');
    }

    /**
     * Tests isValid method with valid CUID2 strings.
     *
     * @dataProvider validCuidProvider
     */
    public function testIsValidWithValidCuids(string $cuid, ?int $expectedLength = null): void
    {
        $this->assertTrue(
            Cuid2::isValid($cuid, $expectedLength),
            "CUID '$cuid' should be considered valid"
        );
    }

    /**
     * Tests isValid method with invalid CUID2 strings.
     *
     * @dataProvider invalidCuidProvider
     */
    public function testIsValidWithInvalidCuids(string $cuid, ?int $expectedLength = null): void
    {
        $this->assertFalse(
            Cuid2::isValid($cuid, $expectedLength),
            "CUID '$cuid' should be considered invalid"
        );
    }

    /**
     * Tests isValid method with length validation.
     */
    public function testIsValidWithExpectedLength(): void
    {
        $validCuid = 'a1b2c3d4e5f6g7h8';

        // Should be valid when length matches
        $this->assertTrue(
            Cuid2::isValid($validCuid, 16),
            'Valid CUID should pass when expected length matches actual length'
        );

        // Should be invalid when length doesn't match
        $this->assertFalse(
            Cuid2::isValid($validCuid, 24),
            'Valid CUID should fail when expected length differs from actual length'
        );

        $this->assertFalse(
            Cuid2::isValid($validCuid, 8),
            'Valid CUID should fail when expected length is shorter than actual length'
        );
    }

    /**
     * Tests isValid method with edge cases for length validation.
     */
    public function testIsValidLengthEdgeCases(): void
    {
        // Test minimum valid length
        $this->assertTrue(Cuid2::isValid('a1b2', 4), 'Minimum length CUID should be valid');
        $this->assertFalse(Cuid2::isValid('a1b', 3), 'Below minimum length should be invalid even with matching expected length');

        // Test maximum valid length
        $maxLengthCuid = 'a' . str_repeat('1', 31);
        $this->assertTrue(Cuid2::isValid($maxLengthCuid, 32), 'Maximum length CUID should be valid');

        // Test above maximum length
        $tooLongCuid = 'a' . str_repeat('1', 32);
        $this->assertFalse(Cuid2::isValid($tooLongCuid, 33), 'Above maximum length should be invalid even with matching expected length');
    }

    /**
     * Tests isValid method without expected length parameter.
     */
    public function testIsValidWithoutExpectedLength(): void
    {
        $this->assertTrue(Cuid2::isValid('a1b2'), 'Valid minimum length CUID should pass without expected length');
        $this->assertTrue(Cuid2::isValid('a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6'), 'Valid maximum length CUID should pass without expected length');
        $this->assertFalse(Cuid2::isValid('abc'), 'Too short CUID should fail without expected length');
        $this->assertFalse(Cuid2::isValid('A1b2'), 'CUID with uppercase should fail without expected length');
    }

    /**
     * Tests isValid method with generated CUIDs.
     *
     * @throws Exception
     */
    public function testIsValidWithGeneratedCuids(): void
    {
        $lengths = [4, 8, 16, 24, 32];

        foreach ($lengths as $length) {
            $cuid = Cuid2::generate($length);
            $cuidString = (string)$cuid;

            // Generated CUID should always be valid
            $this->assertTrue(
                Cuid2::isValid($cuidString),
                "Generated CUID of length $length should be valid"
            );

            // Generated CUID should be valid with correct expected length
            $this->assertTrue(
                Cuid2::isValid($cuidString, $length),
                "Generated CUID should be valid with matching expected length $length"
            );

            // Generated CUID should be invalid with wrong expected length
            $wrongLength = $length === 4 ? 8 : 4;
            $this->assertFalse(
                Cuid2::isValid($cuidString, $wrongLength),
                "Generated CUID should be invalid with wrong expected length $wrongLength"
            );
        }
    }

    /**
     * Tests isValid method with character set validation.
     */
    public function testIsValidCharacterSetValidation(): void
    {
        // Test all valid base36 characters
        $validChars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $testCuid = 'a' . substr(str_shuffle($validChars), 0, 7);
        $this->assertTrue(Cuid2::isValid($testCuid), 'CUID with all valid base36 characters should be valid');

        // Test invalid characters
        $invalidChars = ['A', 'Z', '-', '_', '@', '#', '$', '%', '^', '&', '*'];
        foreach ($invalidChars as $invalidChar) {
            $invalidCuid = 'a1b2' . $invalidChar . '3d4';
            $this->assertFalse(
                Cuid2::isValid($invalidCuid),
                "CUID containing invalid character '$invalidChar' should be invalid"
            );
        }
    }

    /**
     * Tests isValid method regex pattern matching.
     */
    public function testIsValidRegexPattern(): void
    {
        // Test the regex pattern directly through various scenarios
        $validPatterns = [
            'a123',
            'z999',
            'x1y2z3a4b5c6',
            'q0w1e2r3t4y5u6i7o8p9',
        ];

        foreach ($validPatterns as $pattern) {
            $this->assertTrue(
                Cuid2::isValid($pattern),
                "Pattern '$pattern' should match valid CUID regex"
            );
        }

        $invalidPatterns = [
            'A123', // uppercase
            'a-23', // contains hyphen
            'a_23', // contains underscore
            'a 23', // contains space
            'a.23', // contains dot
        ];

        foreach ($invalidPatterns as $pattern) {
            $this->assertFalse(
                Cuid2::isValid($pattern),
                "Pattern '$pattern' should not match valid CUID regex"
            );
        }
    }
}
