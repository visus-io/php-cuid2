<?php

declare(strict_types=1);

namespace Visus\Cuid2;

use Exception;

use const PHP_OS_FAMILY;

/**
 * Singleton responsible for generating and storing a unique machine/process fingerprint for CUID2 generation.
 *
 * This class ensures that CUIDs generated on different machines or in different processes are unique
 * by creating a fingerprint that combines:
 * - Hostname (machine identification)
 * - Process ID (process identification)
 * - Environment variables (additional entropy and context)
 * - Random fallback (when hostname is unavailable)
 *
 * The fingerprint is computed once per process lifecycle and cached for performance. It is hashed
 * using SHA3-512 to create a uniform, high-entropy identifier that becomes part of every CUID
 * generated in this process.
 *
 * Thread Safety:
 * - In PHP, each request/process maintains its own singleton instance
 * - The singleton pattern ensures the fingerprint remains consistent within a single process
 * - Different processes will have different fingerprints (by design)
 *
 * Singleton Protections:
 * - Cannot be cloned (__clone is private)
 * - Cannot be unserialized (__wakeup throws exception)
 * - Cannot be directly instantiated (constructor is private)
 *
 * @internal This class is internal to the CUID2 library and should not be used directly.
 *
 * @psalm-internal Visus\Cuid2
 */
final class Fingerprint
{
    /**
     * Maximum hostname length on Windows systems.
     *
     * Windows limits NetBIOS names to 15 characters. This constant ensures the random
     * fallback identity matches the expected length constraints of the platform.
     */
    private const WINDOWS_HOSTNAME_LENGTH = 15;

    /**
     * Maximum hostname length on Unix-like systems.
     *
     * Unix-like systems (Linux, macOS, BSD) typically support hostnames up to 64 characters,
     * but we use 32 characters to balance uniqueness with performance and storage efficiency.
     */
    private const UNIX_HOSTNAME_LENGTH = 32;

    /**
     * The singleton instance.
     */
    private static ?Fingerprint $instance = null;

    /**
     * Cached serialized environment variables.
     *
     * Environment variables are serialized once and cached to avoid repeated serialization
     * overhead. This cache persists for the lifetime of the process.
     */
    private static ?string $cachedEnvironment = null;

    /**
     * The fingerprint value as binary string.
     *
     * Contains the raw bytes of the SHA3-512 hash that represents this machine/process
     * fingerprint. Stored as a readonly string to ensure immutability after initialization.
     *
     * @psalm-readonly-allow-private-mutation
     */
    private readonly string $value;

    /**
     * Initializes the fingerprint by generating a unique machine/process identifier.
     *
     * The fingerprint combines multiple sources of entropy to ensure uniqueness:
     * - Machine hostname (or random fallback if unavailable)
     * - Process ID from the operating system
     * - Serialized environment variables
     *
     * All components are hashed together using SHA3-512 to create a uniform,
     * high-entropy fingerprint that helps ensure CUID uniqueness across different
     * machines and processes.
     *
     * @throws Exception If SHA3-512 hashing is unavailable or fingerprint generation fails.
     */
    private function __construct()
    {
        $this->value = $this->generateFingerprint();
    }

    /**
     * Prevents cloning of the singleton instance.
     *
     * Cloning would break the singleton pattern and could lead to inconsistent fingerprints,
     * potentially causing CUID collisions across cloned instances. This method is intentionally
     * private and empty to prevent cloning attempts.
     *
     * @codeCoverageIgnore
     */
    private function __clone(): void
    {
        // Prevent cloning
    }

    /**
     * Prevents unserialization of the singleton instance.
     *
     * Unserializing would create a new instance with a potentially outdated fingerprint,
     * breaking the singleton pattern and risking CUID collisions. This method throws an
     * exception if unserialization is attempted.
     *
     * @throws InvalidOperationException Always thrown to prevent unserialization.
     */
    public function __wakeup(): void
    {
        throw new InvalidOperationException('Cannot unserialize singleton');
    }

    /**
     * Gets or creates the singleton Fingerprint instance.
     *
     * This method implements lazy initialization - the Fingerprint is only created when first
     * accessed. Subsequent calls return the same instance, ensuring a consistent fingerprint
     * throughout the process lifecycle.
     *
     * The fingerprint is computed once and cached, making this operation very efficient for
     * repeated CUID generation within the same process.
     *
     * Usage:
     * ```php
     * $fingerprint = Fingerprint::getInstance();
     * $value = $fingerprint->getValue();
     * ```
     *
     * @return Fingerprint The singleton Fingerprint instance.
     *
     * @throws Exception If SHA3-512 hashing is unavailable during first initialization.
     */
    public static function getInstance(): Fingerprint
    {
        return self::$instance ??= new Fingerprint();
    }

    /**
     * Returns the fingerprint value as a binary string.
     *
     * The fingerprint is represented as a binary string derived from the SHA3-512 hash
     * of the combined identity components. This binary data is used as part of the CUID2
     * generation process to ensure uniqueness across machines and processes.
     *
     * Usage:
     * ```php
     * $fingerprint = Fingerprint::getInstance();
     * $bytes = $fingerprint->getValue(); // binary string
     * ```
     *
     * @return string Binary string representing the fingerprint.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Generates the machine/process fingerprint by combining multiple entropy sources.
     *
     * This method creates a unique identifier by hashing together:
     * 1. Machine identity (hostname or random fallback)
     * 2. Process ID (from the operating system)
     * 3. Environment variables (serialized for additional context)
     *
     * The combined data is hashed using SHA3-512 to produce a uniform, high-entropy
     * fingerprint returned as a binary string.
     *
     * Platform Considerations:
     * - Windows: Uses 15-character identity (NetBIOS limit)
     * - Unix-like: Uses 32-character identity (balance of uniqueness and performance)
     *
     * Fallback Behavior:
     * If the hostname cannot be determined (containerized environments, restricted systems),
     * a random identity string is generated as a fallback to maintain uniqueness.
     *
     * @return string Binary string from the SHA3-512 hash.
     *
     * @throws Exception If SHA3-512 hashing is unavailable or hash generation fails.
     */
    private function generateFingerprint(): string
    {
        $identity = gethostname();

        // Fallback if native gethostname() returns false/empty (extremely rare)
        // The compat.php polyfill already handles missing gethostname() function
        // @codeCoverageIgnoreStart
        if ($identity === false || $identity === '') {
            $length = PHP_OS_FAMILY === 'Windows'
                ? self::WINDOWS_HOSTNAME_LENGTH
                : self::UNIX_HOSTNAME_LENGTH;

            $identity = substr(str_shuffle('abcdefghjkmnpqrstvwxyz0123456789'), 0, $length);
        }
        // @codeCoverageIgnoreEnd

        $hash = hash_init('sha3-512');

        hash_update($hash, $identity);
        hash_update($hash, (string) getmypid());
        hash_update($hash, $this->getCachedEnvironment());

        return hash_final($hash, true);
    }

    /**
     * Returns a cached serialized representation of environment variables.
     *
     * Environment variables provide additional entropy and context for the fingerprint.
     * They are serialized once and cached to avoid repeated serialization overhead on
     * every CUID generation.
     *
     * The serialized environment is used as part of the hash input to ensure that
     * processes with different environment configurations generate different fingerprints,
     * even if they share the same hostname and process ID pattern.
     *
     * @return string Serialized environment variables, cached for the process lifetime.
     */
    private function getCachedEnvironment(): string
    {
        return self::$cachedEnvironment ??= serialize(getenv());
    }
}
