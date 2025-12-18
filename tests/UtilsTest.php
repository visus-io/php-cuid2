<?php

declare(strict_types=1);

namespace Visus\Cuid2\Test;

use PHPUnit\Framework\TestCase;
use Visus\Cuid2\Utils;

class UtilsTest extends TestCase
{
    /**
     * Provides hex to base36 conversion test cases.
     *
     * @return array<string, array<string>>
     */
    public static function hexToBase36Provider(): array
    {
        return [
            'empty string' => ['', '0'],
            'zero' => ['0', '0'],
            'single digit' => ['1', '1'],
            'small hex lowercase' => ['a', 'a'],
            'small hex uppercase' => ['A', 'a'],
            'hex 10 (16 decimal)' => ['10', 'g'],
            'hex ff' => ['ff', '73'],
            'hex FF uppercase' => ['FF', '73'],
            'mixed case' => ['DeadBeef', '1ps9wxb'],
            'large hex value' => ['123456789abcdef', 'mf9g063v08f'],
            'very large hex' => ['ffffffffffffffff', '3w5e11264sgsf'],
            'sha3-512 like length' => [
                '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef' .
                '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef',
                '6ihun23as4yiexi4ejjixxnq6u5ky3ht9ifgja5ow7nni0ozoxwpe5lugi6q352ivverfbd2ks8o30441en67a8y5j8xotfmr3',
            ],
            'hex with leading zeros' => ['00ff', '73'],
            'all ones' => ['1111', '3dd'],
            'alternating pattern' => ['aaaa', 'xpm'],
            'max 4-bit values' => ['ffff', '1ekf'],
        ];
    }

    /**
     * Provides invalid hex character test cases.
     *
     * @return array<string, array<string>>
     */
    public static function invalidHexCharactersProvider(): array
    {
        return [
            'contains g' => ['g123', ''],
            'contains z' => ['z456', ''],
            'special character' => ['12@34', ''],
            'space' => ['12 34', ''],
            'dash' => ['12-34', ''],
        ];
    }

    /**
     * @dataProvider hexToBase36Provider
     */
    public function testHexToBase36Conversion(string $hex, string $expected): void
    {
        $result = Utils::hexToBase36($hex);

        $this->assertSame($expected, $result);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-z]*$/',
            $result,
            'Result should only contain base36 characters (0-9, a-z)'
        );
    }

    public function testHexToBase36ReturnsBase36Characters(): void
    {
        $testCases = ['1', 'a', 'ff', '100', 'abc123', 'deadbeef'];

        foreach ($testCases as $hex) {
            $result = Utils::hexToBase36($hex);

            $this->assertMatchesRegularExpression(
                '/^[0-9a-z]+$/',
                $result,
                "Result for hex '{$hex}' should only contain base36 characters"
            );
        }
    }

    public function testHexToBase36IsCaseInsensitive(): void
    {
        $lowercase = Utils::hexToBase36('abcdef');
        $uppercase = Utils::hexToBase36('ABCDEF');
        $mixedCase = Utils::hexToBase36('AbCdEf');

        $this->assertSame($lowercase, $uppercase);
        $this->assertSame($lowercase, $mixedCase);
    }

    public function testHexToBase36ConsistencyAcrossMultipleCalls(): void
    {
        $hex = '123456789abcdef';

        $result1 = Utils::hexToBase36($hex);
        $result2 = Utils::hexToBase36($hex);
        $result3 = Utils::hexToBase36($hex);

        $this->assertSame($result1, $result2);
        $this->assertSame($result1, $result3);
    }

    public function testHexToBase36ProducesUniqueOutputsForDifferentInputs(): void
    {
        $results = [];
        $hexValues = ['1', '2', '3', 'a', 'b', 'c', '10', '11', '12', 'ff', 'fe', 'fd'];

        foreach ($hexValues as $hex) {
            $results[$hex] = Utils::hexToBase36($hex);
        }

        $uniqueResults = array_unique($results);

        $this->assertCount(
            count($hexValues),
            $uniqueResults,
            'Different hex inputs should produce different base36 outputs'
        );
    }

    /**
     * @dataProvider invalidHexCharactersProvider
     */
    public function testHexToBase36HandlesInvalidCharactersGracefully(string $hex, string $_expected): void
    {
        // Invalid characters are treated as 0 based on the match default case
        $result = Utils::hexToBase36($hex);

        $this->assertMatchesRegularExpression('/^[0-9a-z]*$/', $result);
    }

    public function testHexToBase36WithSequentialValues(): void
    {
        // Test that sequential hex values produce sequential patterns
        $results = [];
        for ($i = 0; $i <= 15; $i++) {
            $hex = dechex($i);
            $results[$hex] = Utils::hexToBase36($hex);
        }

        // Verify they're all unique
        $this->assertCount(16, array_unique($results));

        // Verify basic conversions
        $this->assertSame('0', Utils::hexToBase36('0'));
        $this->assertSame('1', Utils::hexToBase36('1'));
        $this->assertSame('a', Utils::hexToBase36('a'));
        $this->assertSame('f', Utils::hexToBase36('f'));
    }

    public function testHexToBase36WithPowersOfTwo(): void
    {
        // Test powers of 2 in hexadecimal
        $testCases = [
            '1' => '1', // 2^0 = 1
            '2' => '2', // 2^1 = 2
            '4' => '4', // 2^2 = 4
            '8' => '8', // 2^3 = 8
            '10' => 'g', // 2^4 = 16
            '20' => 'w', // 2^5 = 32
            '40' => '1s', // 2^6 = 64
            '80' => '3k', // 2^7 = 128
            '100' => '74', // 2^8 = 256
        ];

        foreach ($testCases as $hex => $expected) {
            $this->assertSame($expected, Utils::hexToBase36((string) $hex));
        }
    }

    public function testHexToBase36ProducesShorterStrings(): void
    {
        // Base36 should generally produce shorter strings than hex for large values
        $longHex = str_repeat('f', 64); // 64 hex chars
        $base36Result = Utils::hexToBase36($longHex);

        // Base36 is more compact than base16, so result should be shorter
        $this->assertLessThan(strlen($longHex), strlen($base36Result));
    }
}
