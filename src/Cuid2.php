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

        $this->prefix = chr(rand(97,122));
        $this->salt = unpack('C*', random_bytes(32));

        $this->timestamp = (int)(microtime(true) * 1000);
        $this->random = unpack('C*', random_bytes(32));
        $this->fingerprint = self::generateFingerprint();

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

    private static function generateFingerprint(): array
    {
        $system = unpack('C*', self::retrieveSystemName());
        $process = unpack('C*', (string)getmypid());

        $bytes = array_merge($system, $process);

        if (count($bytes) > 32) {
            $bytes = array_slice($bytes, 0, 32);
        }

        $diff = 32 - count($bytes);

        $salt = unpack('C*', random_bytes($diff));

        return array_merge($bytes, $salt);
    }

    private static function retrieveSystemName(): string
    {
        if (!($machineName = gethostname())) {
            $machineName = self::generateSystemName();
        }

        return $machineName;
    }

    private static function generateSystemName(): string
    {
        $name = bin2hex(random_bytes(24));
        return strtoupper(substr($name, 0, 15));
    }
}