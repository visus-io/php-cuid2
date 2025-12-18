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
     * Converts a hexadecimal string to large base representation.
     *
     * Processes 8 hexadecimal characters at a time for improved performance,
     * reducing loop iterations by 8x compared to single-character processing.
     *
     * @param string $hexValue Hexadecimal string to convert.
     * @param int $base Large base for digit storage (100 million).
     *
     * @return array<int> Array of digits in large base representation.
     */
    private static function convertHexToLargeBase(string $hexValue, int $base): array
    {
        $digits = [0];
        $len = strlen($hexValue);

        $remainder = $len % 8;
        $i = 0;

        if ($remainder > 0) {
            $chunk = substr($hexValue, 0, $remainder);
            $chunkValue = (int) hexdec($chunk);
            $carry = $chunkValue;
            $multiplier = 1 << $remainder * 4; // 16^remainder = 2^(remainder*4)

            for ($j = 0, $jlen = count($digits); $j < $jlen; $j++) {
                $current = $digits[$j] * $multiplier + $carry;
                $digits[$j] = $current % $base;
                $carry = intdiv($current, $base);
            }

            while ($carry > 0) {
                $digits[] = $carry % $base;
                $carry = intdiv($carry, $base);
            }

            $i = $remainder;
        }

        for (; $i < $len; $i += 8) {
            $chunk = substr($hexValue, $i, 8);
            $chunkValue = (int) hexdec($chunk);
            $carry = $chunkValue;

            for ($j = 0, $jlen = count($digits); $j < $jlen; $j++) {
                $current = $digits[$j] * 4294967296 + $carry; // 16^8 = 2^32 = 4294967296
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
     * the GMP extension. For small values (≤14 hex chars), it uses the native
     * base_convert() function for optimal performance. For larger values, it uses
     * a large intermediate base (100 million) for efficient arithmetic operations.
     *
     * Base36 encoding uses digits 0-9 and lowercase letters a-z (36 characters total),
     * producing shorter strings than hexadecimal while remaining URL-safe.
     *
     * Algorithm:
     * 1. Fast path: Use base_convert() for small values (≤14 hex chars / 56 bits)
     * 2. Large values: Convert hex string to internal representation using base 100M
     * 3. Convert internal representation to base36 by repeated division
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

        if (strlen($hexValue) <= 14) {
            return base_convert($hexValue, 16, 36);
        }

        $base = 100_000_000;
        $digits = self::convertHexToLargeBase($hexValue, $base);

        return self::convertLargeBaseToBase36($digits, $base);
    }
}
