<?php

declare(strict_types=1);

namespace Visus\Cuid2;

/**
 * Utility functions for CUID2 generation.
 */
final class Utils
{
    /**
     * Base36 alphabet for encoding (0-9, a-z).
     */
    private const BASE36_ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyz';

    /**
     * Prevents instantiation of utility class.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Converts a single hexadecimal character to its numeric value.
     *
     * @param string $char Single hex character (0-9, a-f, A-F).
     *
     * @return int Numeric value (0-15).
     */
    private static function parseHexCharacter(string $char): int
    {
        return match (true) {
            $char >= '0' && $char <= '9' => ord($char) - 48,
            $char >= 'a' && $char <= 'f' => ord($char) - 87,
            $char >= 'A' && $char <= 'F' => ord($char) - 55,
            default => 0,
        };
    }

    /**
     * Converts a hexadecimal string to large base representation.
     *
     * @param string $hexValue Hexadecimal string to convert.
     * @param int $base Large base for digit storage (100 million).
     *
     * @return array<int> Array of digits in large base representation.
     */
    private static function convertHexToLargeBase(string $hexValue, int $base): array
    {
        $digits = [0];

        for ($i = 0, $len = strlen($hexValue); $i < $len; $i++) {
            $hexDigit = self::parseHexCharacter($hexValue[$i]);
            $carry = $hexDigit;

            for ($j = 0, $jlen = count($digits); $j < $jlen; $j++) {
                $current = $digits[$j] * 16 + $carry;
                $digits[$j] = $current % $base;
                $carry = intdiv($current, $base);
            }

            while ($carry > 0) {
                $digits[] = $carry % $base;
                $carry = intdiv($carry, $base);
            }
        }

        return $digits;
    }

    /**
     * Converts large base digit array to base36 string.
     *
     * @param array<int> $digits Array of digits in large base representation.
     * @param int $base Large base (100 million).
     *
     * @return string Base36 encoded string.
     */
    private static function convertLargeBaseToBase36(array $digits, int $base): string
    {
        $resultChars = [];

        while (count($digits) > 1 || $digits[0] !== 0) {
            $carry = 0;
            $newDigits = [];

            for ($i = count($digits) - 1; $i >= 0; $i--) {
                $current = $carry * $base + $digits[$i];
                $quotient = intdiv($current, 36);
                $carry = $current % 36;

                if ($quotient > 0 || $newDigits !== []) {
                    $newDigits[] = $quotient;
                }
            }

            $resultChars[] = self::BASE36_ALPHABET[$carry];
            $digits = $newDigits !== [] ? array_reverse($newDigits) : [0];
        }

        return implode('', array_reverse($resultChars));
    }

    /**
     * Converts a hexadecimal string to base36 encoding.
     *
     * This function performs arbitrary precision base conversion without requiring
     * the GMP extension. It uses a large intermediate base (100 million) for efficient
     * arithmetic operations on large numbers.
     *
     * Base36 encoding uses digits 0-9 and lowercase letters a-z (36 characters total),
     * producing shorter strings than hexadecimal while remaining URL-safe.
     *
     * Algorithm:
     * 1. Convert hex string to internal representation using base 100M
     * 2. Convert internal representation to base36 by repeated division
     *
     * @param string $hexValue Hexadecimal string to convert (case-insensitive).
     *
     * @return string The value encoded in base36 (lowercase alphanumeric).
     */
    public static function hexToBase36(string $hexValue): string
    {
        if ($hexValue === '' || $hexValue === '0') {
            return '0';
        }

        $base = 100_000_000;
        $digits = self::convertHexToLargeBase($hexValue, $base);

        return self::convertLargeBaseToBase36($digits, $base);
    }
}
