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
    private const string BASE36_ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyz';

    /**
     * Number of base36 digits that limbsToBase36() extracts per pass.
     *
     * 36^5 equals 60,466,176. This is the largest power of 36 that keeps
     * `(remainder << 32) | limb` inside a 64-bit signed integer. 36^6 would overflow
     * this range. Each pass scans the whole limb array, and this scan is the main cost
     * of the conversion. Extracting 5 digits per pass instead of 1 cuts the number of
     * scans by about 5 times.
     */
    private const int BASE36_DIGITS_PER_PASS = 5;

    /**
     * Divisor for one limbsToBase36() pass.
     *
     * Equals 36 to the power of BASE36_DIGITS_PER_PASS.
     */
    private const int BASE36_PASS_RADIX = 60_466_176;

    /**
     * Prevents instantiation of utility class.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Converts a raw binary string to base36 encoding.
     *
     * This method reads bytes directly, for example a raw hash digest. It does not
     * build a hex string first. It uses fixed-width base-2^32 limbs instead of
     * arbitrary precision arithmetic. It reads the byte string as big-endian: the
     * first byte is the most significant byte. A hex string of the same value uses
     * the same byte order.
     *
     * @param string $bytes Binary string to convert.
     *
     * @return string The base36 value. Uses lowercase letters and digits.
     */
    public static function bytesToBase36(string $bytes): string
    {
        $len = strlen($bytes);

        if ($len === 0) {
            return '0';
        }

        return self::limbsToBase36(self::packBytesToLimbs($bytes), $len * 8);
    }

    /**
     * Converts a hexadecimal string to base36 encoding.
     *
     * This method does not need the GMP extension. For values of 14 hex characters or
     * fewer, it uses the native base_convert() function. This fast path gives the best
     * performance for short values. For longer values, it packs the hex string into
     * base-2^32 limbs. It then converts the limbs to base36.
     *
     * Base36 encoding uses the digits 0-9 and the letters a-z. It has 36 characters in
     * total. Base36 strings are shorter than hex strings and stay URL-safe.
     *
     * @param string $hexValue Hexadecimal string to convert. Not case-sensitive.
     *
     * @return string The base36 value. Uses lowercase letters and digits.
     */
    public static function hexToBase36(string $hexValue): string
    {
        $hexValue = preg_replace('/[^0-9a-fA-F]/', '', $hexValue) ?? '';

        if ($hexValue === '' || $hexValue === '0') {
            return '0';
        }

        if (strlen($hexValue) <= 14) {
            return base_convert($hexValue, 16, 36);
        }

        return self::limbsToBase36(self::packHexToLimbs($hexValue), strlen($hexValue) * 4);
    }

    /**
     * Divides the whole limb array by 36^5, in place, for one limbsToBase36() pass.
     *
     * The pass carries the remainder from the most significant limb to the least
     * significant limb. It tracks the most significant non-zero limb. Later passes then
     * skip limbs that are already zero.
     *
     * @param array<int, int> $limbs Limbs, least significant limb first. Divided in place.
     * @param int $end Number of limbs still in use, from a previous pass.
     *
     * @return array{0: int, 1: int} The pass remainder, and the new $end for the next pass.
     */
    private static function divideLimbsByPassRadix(array &$limbs, int $end): array
    {
        $remainder = 0;
        $newEnd = 0;

        for ($j = $end - 1; $j >= 0; $j--) {
            $current = ($remainder << 32) | $limbs[$j];
            $quotient = intdiv($current, self::BASE36_PASS_RADIX);
            $limbs[$j] = $quotient;
            $remainder = $current % self::BASE36_PASS_RADIX;

            if ($quotient !== 0 && $newEnd === 0) {
                $newEnd = $j + 1;
            }
        }

        return [$remainder, $newEnd];
    }

    /**
     * Converts base-2^32 limbs to a base36 string.
     *
     * Each pass divides the whole limb array by 36^5 and emits 5 base36 digits at once.
     *
     * @param array<int, int> $limbs Limbs, least significant limb first.
     * @param int $bitLength Upper bound on the bit length of the value. Used to size the
     *                       output buffer.
     *
     * @return string Base36 encoded string.
     */
    private static function limbsToBase36(array $limbs, int $bitLength): string
    {
        // 5 is less than log2(36), which is about 5.17. So dividing by 5 always
        // overestimates the digit count. This guarantees a large enough buffer and
        // avoids floating-point math.
        $bufferLength = intdiv($bitLength, 5) + 1;
        $buffer = str_repeat('0', $bufferLength);
        $i = $bufferLength;

        $end = count($limbs);

        while ($end > 0) {
            [$remainder, $end] = self::divideLimbsByPassRadix($limbs, $end);
            self::writeBase36Group($buffer, $i, $bufferLength, $remainder, $end === 0);
        }

        return substr($buffer, $i);
    }

    /**
     * Packs a raw binary string into base-2^32 limbs.
     *
     * Limb 0 holds the least significant bits.
     *
     * @param string $bytes Binary string to pack. Must be non-empty.
     *
     * @return array<int, int> Limbs, least significant limb first.
     */
    private static function packBytesToLimbs(string $bytes): array
    {
        $len = strlen($bytes);
        $limbCount = intdiv($len + 3, 4);
        $limbs = array_fill(0, $limbCount, 0);

        $pos = $len;

        for ($limbIndex = 0; $limbIndex < $limbCount; $limbIndex++) {
            $take = min(4, $pos);
            $limb = 0;

            for ($b = 0; $b < $take; $b++) {
                $limb |= ord($bytes[$pos - 1 - $b]) << $b * 8;
            }

            $limbs[$limbIndex] = $limb;
            $pos -= $take;
        }

        return $limbs;
    }

    /**
     * Packs a hexadecimal string into base-2^32 limbs.
     *
     * Limb 0 holds the least significant bits. Each limb holds up to 8 hex characters.
     * 8 hex characters equal exactly 32 bits, since 16^8 equals 2^32. So hexdec() can
     * convert each chunk straight into a limb. This method needs no multiply or carry
     * step.
     *
     * @param string $hexValue Hexadecimal string to pack. Must be non-empty and valid.
     *
     * @return array<int, int> Limbs, least significant limb first.
     */
    private static function packHexToLimbs(string $hexValue): array
    {
        $len = strlen($hexValue);
        $limbCount = intdiv($len + 7, 8);
        $limbs = array_fill(0, $limbCount, 0);

        $pos = $len;

        for ($limbIndex = 0; $limbIndex < $limbCount; $limbIndex++) {
            $take = min(8, $pos);
            $limbs[$limbIndex] = (int) hexdec(substr($hexValue, $pos - $take, $take));
            $pos -= $take;
        }

        return $limbs;
    }

    /**
     * Writes one pass's base36 digits into the output buffer, from the end backward.
     *
     * A non-final group always writes BASE36_DIGITS_PER_PASS digits, padded with
     * leading zeros. The final (most significant) group writes only its significant
     * digits, with no padding. If the whole value is zero and nothing is written yet,
     * it writes a single zero.
     *
     * @param string $buffer Output buffer, written in place.
     * @param int $i Cursor into $buffer, updated in place.
     * @param int $bufferLength Original length of $buffer.
     * @param int $remainder Remainder from the pass, holding this group's digits.
     * @param bool $isFinalGroup Whether this is the most significant group.
     */
    private static function writeBase36Group(
        string &$buffer,
        int &$i,
        int $bufferLength,
        int $remainder,
        bool $isFinalGroup
    ): void {
        if (!$isFinalGroup) {
            for ($d = 0; $d < self::BASE36_DIGITS_PER_PASS; $d++) {
                $i--;
                $buffer[$i] = self::BASE36_ALPHABET[$remainder % 36];
                $remainder = intdiv($remainder, 36);
            }

            return;
        }

        if ($remainder === 0 && $i === $bufferLength) {
            $i--;
            $buffer[$i] = '0';
        }

        while ($remainder > 0) {
            $i--;
            $buffer[$i] = self::BASE36_ALPHABET[$remainder % 36];
            $remainder = intdiv($remainder, 36);
        }
    }
}
