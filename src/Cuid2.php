<?php
declare(strict_types=1);

namespace Xaevik\Cuid2;

use Exception;
use OutOfRangeException;

/**
 * Represents a collision resistant unique identifier
 */
final class Cuid2
{
    private readonly int $counter;

    private readonly array $fingerprint;

    private readonly int $length;

    private readonly string $prefix;

    private readonly array $random;

    private readonly array $salt;

    private readonly int $timestamp;

    /**
     * Initializes a new instance of Cuid2.
     *
     * @param int $maxLength The length of the CUIDv2 value that should be returned.
     * @throws OutOfRangeException The value of $maxLength was less than 4 or greater than 32.
     * @throws Exception An underlying exception occurred.
     */
    public function __construct(int $maxLength = 24)
    {
        if ($maxLength < 4 || $maxLength > 32) {
            throw new OutOfRangeException("maxLength: cannot be less than 4 or greater than 32.");
        }

        $this->counter = Counter::getInstance()->getNextValue();

        $this->prefix = chr(rand(97, 122));
        $this->salt = unpack('C*', random_bytes(32));

        $this->timestamp = (int)(microtime(true) * 1000);
        $this->random = unpack('C*', random_bytes(32));

        $this->fingerprint = unpack(
            'C*',
            hash('sha3-512', random_int(PHP_INT_MIN, PHP_INT_MAX) * 2063 . serialize($_SERVER))
        );

        $this->length = $maxLength;
    }

    public function __toString(): string
    {
        $hash = hash_init('sha3-512');

        hash_update($hash, (string)$this->timestamp);
        hash_update($hash, (string)$this->counter);

        hash_update($hash, bin2hex(pack('C*', ...$this->random)));
        hash_update($hash, bin2hex(pack('C*', ...$this->fingerprint)));
        hash_update($hash, bin2hex(pack('C*', ...$this->salt)));

        $result = base_convert(hash_final($hash), 16, 36);

        return $this->prefix . substr($result, 0, $this->length - 1);
    }
}
