<?php

declare(strict_types=1);

namespace Visus\Cuid2;

use Exception;
use JsonSerializable;
use OutOfRangeException;
use Override;

use const GMP_BIG_ENDIAN;
use const GMP_MSW_FIRST;

/**
 * Generates collision-resistant, URL-safe unique identifiers (CUID2).
 *
 * This class implements the CUID2 specification to generate unique, sortable identifiers
 * that are more secure and collision-resistant than traditional UUIDs. Each CUID consists of:
 * - Random lowercase letter prefix (a-z)
 * - Timestamp in milliseconds (collision prevention across time)
 * - Monotonic counter (collision prevention in rapid succession)
 * - Machine/process fingerprint (uniqueness across machines)
 * - Cryptographically secure random data
 *
 * All components are hashed with SHA3-512 and converted to base36 for the final identifier.
 *
 * Performance Characteristics:
 * - CUID value generated immediately during construction
 * - Value cached for zero-overhead repeated access
 * - Binary data stored directly (no pack/unpack overhead)
 * - High-resolution timestamp using hrtime()
 * - Immutable once constructed
 *
 * Example:
 * ```php
 * $cuid = new Cuid2(); // Default 24 characters
 * $short = new Cuid2(10); // Shorter 10 character ID
 * echo $cuid; // e.g., "tz4a98xxat96iws9zmbrgj3a"
 * ```
 *
 * @see https://github.com/paralleldrive/cuid2 CUID2 specification
 */
final class Cuid2 implements JsonSerializable
{
    /**
     * Cached list of available hash algorithms.
     *
     * @var array<int, string>|null
     */
    private static ?array $algorithmsCache = null;

    /**
     * Cached result of SHA3-512 algorithm support check.
     */
    private static ?bool $isAlgorithmSupported = null;

    /**
     * Monotonic counter value for this CUID (prevents rapid-succession collisions).
     */
    private readonly int $counter;

    /**
     * Machine/process fingerprint as binary data (ensures uniqueness across machines).
     */
    private readonly string $fingerprint;

    /**
     * Configured length of the CUID string.
     *
     * @var int<4, 32>
     */
    private readonly int $length;

    /**
     * Random lowercase letter prefix (a-z).
     */
    private readonly string $prefix;

    /**
     * Cryptographically secure random bytes.
     */
    private readonly string $random;

    /**
     * Timestamp in milliseconds when this CUID was created.
     */
    private readonly int $timestamp;

    /**
     * The final rendered CUID string value (cached for performance).
     */
    private readonly string $value;

    /**
     * Initializes a new CUID2 instance.
     *
     * Generates all components (timestamp, counter, fingerprint, random data) and immediately
     * renders the final CUID string for predictable performance and efficient repeated access.
     *
     * @param int $maxLength Total length of the CUID string (4-32 characters, default 24).
     *
     * @throws OutOfRangeException If $maxLength is less than 4 or greater than 32.
     * @throws Exception If random byte generation fails (extremely rare).
     * @throws InvalidOperationException If SHA3-512 algorithm is not supported (extremely rare).
     */
    public function __construct(int $maxLength = 24)
    {
        if ($maxLength < 4 || $maxLength > 32) {
            throw new OutOfRangeException('maxLength: cannot be less than 4 or greater than 32.');
        }

        /** @phpstan-var int<4, 32> $maxLength */
        $this->length = $maxLength;
        $this->timestamp = self::generateTimestamp();
        $this->counter = Counter::getInstance()->getNextValue();
        $this->fingerprint = Fingerprint::getInstance()->getValue();
        $this->random = self::generateRandom($maxLength);
        $this->prefix = chr(random_int(97, 122));

        // Generate value immediately for consistent performance characteristics
        $this->value = $this->render();
    }

    /**
     * Returns the CUID string when the object is used in string context.
     *
     * @return string The cached CUID string value.
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Static factory method to generate a new CUID2.
     *
     * Convenience method equivalent to `new Cuid2($maxLength)`.
     *
     * @param int $maxLength Total length of the CUID string (4-32 characters, default 24).
     *
     * @return Cuid2 A new CUID2 instance.
     *
     * @throws OutOfRangeException If $maxLength is less than 4 or greater than 32.
     * @throws Exception If random byte generation fails (extremely rare).
     * @throws InvalidOperationException If SHA3-512 algorithm is not supported (extremely rare).
     */
    public static function generate(int $maxLength = 24): Cuid2
    {
        return new self($maxLength);
    }

    /**
     * Validates whether a string conforms to the CUID2 format.
     *
     * Performs format validation only - checks length and character pattern.
     * Does NOT verify the string was actually generated by this library.
     *
     * Format requirements:
     * - First character: lowercase letter (a-z)
     * - Remaining characters: lowercase alphanumeric (a-z, 0-9)
     * - Total length: 4-32 characters
     *
     * @param string $id The string to validate.
     * @param int|null $expectedLength Optional expected length for strict validation.
     *                                  If null, any length between 4-32 is accepted.
     *
     * @return bool True if the string matches CUID2 format, false otherwise.
     */
    public static function isValid(string $id, ?int $expectedLength = null): bool
    {
        $length = strlen($id);

        // Validate all length constraints in one check
        if (
            $length < 4 || $length > 32 ||
            ($expectedLength !== null && ($expectedLength < 4 || $expectedLength > 32 || $length !== $expectedLength))
        ) {
            return false;
        }

        // Fast character-by-character validation
        // First character must be lowercase letter
        if ($id[0] < 'a' || $id[0] > 'z') {
            return false;
        }

        // Remaining characters must be lowercase alphanumeric
        $validChars = true;
        for ($i = 1; $i < $length; $i++) {
            $char = $id[$i];
            $isDigit = $char >= '0' && $char <= '9';
            $isLowerLetter = $char >= 'a' && $char <= 'z';

            if (!$isDigit && !$isLowerLetter) {
                $validChars = false;

                break;
            }
        }

        return $validChars;
    }

    /**
     * Specifies data to be serialized to JSON.
     *
     * @return string The CUID string value for JSON encoding.
     */
    #[Override]
    public function jsonSerialize(): string
    {
        return $this->value;
    }

    /**
     * Returns the CUID string representation.
     *
     * @return string The cached CUID string value.
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Converts a raw binary digest to base36.
     *
     * This method uses the GMP extension when available. GMP gives much better
     * performance. Otherwise, it calls Utils::bytesToBase36(). Both paths read the raw
     * digest bytes directly. Neither path builds an intermediate hex string.
     *
     * Base36 encoding uses the digits 0-9 and the letters a-z. It has 36 characters in
     * total. Base36 strings are shorter than hex strings. They stay URL-safe and are
     * not case-sensitive.
     *
     * @param string $value Raw binary digest to convert.
     *
     * @return string The value encoded in base36.
     */
    private static function convert(string $value): string
    {
        if (extension_loaded('gmp')) {
            $number = gmp_import($value, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);

            return gmp_strval($number, 36);
        }

        return Utils::bytesToBase36($value);
    }

    /**
     * Generates cryptographically secure random bytes.
     *
     * Uses PHP's random_bytes() which provides CSPRNG (Cryptographically Secure
     * Pseudo-Random Number Generator) for high-quality entropy.
     *
     * @param int<1, max> $length Number of random bytes to generate.
     *
     * @return string Binary string of random bytes.
     *
     * @throws Exception If an appropriate source of randomness cannot be found.
     */
    private static function generateRandom(int $length): string
    {
        return random_bytes($length);
    }

    /**
     * Generates current timestamp in milliseconds.
     *
     * Uses hrtime() high-resolution monotonic timer for better performance and precision
     * compared to DateTime object creation. The monotonic clock is not affected by system
     * time adjustments.
     *
     * @return int Current timestamp in milliseconds since an arbitrary epoch.
     */
    private static function generateTimestamp(): int
    {
        return (int) (hrtime(as_number: true) / 1_000_000);
    }

    /**
     * Checks if a hash algorithm is supported by the current PHP installation.
     *
     * Results are cached in a static property to avoid repeated calls to hash_algos().
     *
     * @param string $algorithm Hash algorithm name to check (e.g., 'sha3-512').
     *
     * @return bool True if the algorithm is supported, false otherwise.
     */
    private static function isSupportedAlgorithm(string $algorithm): bool
    {
        self::$algorithmsCache ??= hash_algos();

        return in_array($algorithm, self::$algorithmsCache, true);
    }

    /**
     * Renders the CUID2 by hashing all components and converting to base36.
     *
     * Process:
     * 1. Verifies SHA3-512 algorithm support
     * 2. Initializes SHA3-512 hash context
     * 3. Updates hash with timestamp, counter, random bytes, and fingerprint (binary data)
     * 4. Finalizes the hash into a raw binary digest
     * 5. Converts the digest to base36 with GMP, or the pure-PHP fallback
     * 6. Prepends random letter prefix and truncates to requested length
     *
     * @return string The final CUID2 identifier string.
     *
     * @throws InvalidOperationException If SHA3-512 algorithm is not supported.
     */
    private function render(): string
    {
        self::$isAlgorithmSupported ??= self::isSupportedAlgorithm('sha3-512');
        if (!self::$isAlgorithmSupported) {
            // @codeCoverageIgnoreStart
            // phpcs:ignore Generic.Files.LineLength
            throw new InvalidOperationException('SHA3-512 appears to be unsupported - make sure you have support for it, or upgrade your version of PHP.');
            // @codeCoverageIgnoreEnd
        }

        $hash = hash_init('sha3-512');

        hash_update($hash, (string) $this->timestamp);
        hash_update($hash, (string) $this->counter);
        hash_update($hash, $this->random);
        hash_update($hash, $this->fingerprint);

        $hash = hash_final($hash, true);

        $result = self::convert($hash);

        return $this->prefix . substr($result, 0, $this->length - 1);
    }
}
