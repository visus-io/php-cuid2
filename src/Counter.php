<?php

declare(strict_types=1);

namespace Visus\Cuid2;

use Exception;
use Random\Engine\Secure;
use Random\Randomizer;

/**
 * Singleton responsible for maintaining a monotonically increasing counter for CUID2 generation.
 *
 * This class prevents collisions when generating multiple CUIDs in rapid succession within the same
 * process. The counter is initialized with a cryptographically secure random value and increments
 * with each CUID generated.
 *
 * The counter uses a bias-free random initialization algorithm to ensure uniform distribution
 * within the RANGE (0 to 476782367). When the counter reaches RANGE, it wraps back to 0 to
 * prevent integer overflow in long-running processes.
 *
 * Thread Safety:
 * - In PHP, each request/process maintains its own singleton instance
 * - The singleton pattern ensures consistency within a single process lifecycle
 * - Different processes will have different counter values (by design)
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
final class Counter
{
    /**
     * The range for the counter value.
     *
     * 476782367 is chosen to provide a large enough space for unique values while fitting within
     * the constraints of the CUID2 specification. This value is derived from the maximum safe
     * integer range for the algorithm, ensuring uniform distribution and minimizing bias when
     * generating random values.
     *
     * The counter wraps back to 0 when this value is reached to prevent integer overflow.
     *
     * @see https://github.com/paralleldrive/cuid2 CUID2 specification
     */
    private const RANGE = 476782367;

    /**
     * The singleton instance.
     */
    private static ?Counter $instance = null;

    /**
     * The current counter value.
     *
     * Initialized with a cryptographically secure random value in the range [0, RANGE) and
     * increments with each call to getNextValue(). Wraps back to 0 when RANGE is reached.
     *
     * @psalm-readonly-allow-private-mutation
     */
    private int $value;

    /**
     * Initializes the counter with a cryptographically secure random value.
     *
     * Uses PHP 8.2's Random extension with the Secure engine, which provides
     * cryptographically secure random number generation with automatic bias-free
     * sampling. This eliminates the need for manual rejection sampling algorithms.
     *
     * The Randomizer handles:
     * - CSPRNG (Cryptographically Secure Pseudo-Random Number Generator)
     * - Bias-free integer generation via internal rejection sampling
     * - Uniform distribution within the specified range
     *
     * @throws Exception If the random source is unavailable (extremely rare in PHP 8.2+).
     */
    private function __construct()
    {
        $randomizer = new Randomizer(new Secure());
        $this->value = $randomizer->getInt(0, self::RANGE - 1);
    }

    /**
     * Prevents cloning of the singleton instance.
     *
     * Cloning would break the singleton pattern and could lead to inconsistent counter
     * states, potentially causing CUID collisions. This method is intentionally private
     * and empty to prevent cloning attempts.
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
     * Unserializing would create a new instance with a potentially outdated counter value,
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
     * Gets or creates the singleton Counter instance.
     *
     * This method implements lazy initialization - the Counter is only created when first
     * accessed. Subsequent calls return the same instance, ensuring a consistent counter
     * value throughout the process lifecycle.
     *
     * Usage:
     * ```php
     * $counter = Counter::getInstance();
     * $value = $counter->getNextValue();
     * ```
     *
     * @return Counter The singleton Counter instance.
     *
     * @throws Exception If the random source is unavailable during first initialization.
     */
    public static function getInstance(): Counter
    {
        return self::$instance ??= new Counter();
    }

    /**
     * Returns the current counter value and increments it for the next call.
     *
     * This method is the core of collision prevention in CUID2 generation. Each call
     * returns a unique value within the current process, ensuring CUIDs generated in
     * rapid succession have different counter components.
     *
     * The counter automatically wraps back to 0 when it reaches RANGE (476782367),
     * preventing integer overflow in long-running processes while maintaining uniqueness
     * through other CUID components (timestamp, random, fingerprint).
     *
     * Behavior:
     * - Returns current value
     * - Increments internal counter
     * - Wraps to 0 at RANGE boundary
     *
     * Usage:
     * ```php
     * $counter = Counter::getInstance();
     * $val1 = $counter->getNextValue(); // e.g., 123456
     * $val2 = $counter->getNextValue(); // e.g., 123457
     * $val3 = $counter->getNextValue(); // e.g., 123458
     * ```
     *
     * @return int The current counter value before incrementing (range: 0 to RANGE-1).
     */
    public function getNextValue(): int
    {
        $value = $this->value;
        $this->value = ($this->value + 1) % self::RANGE;

        return $value;
    }
}
