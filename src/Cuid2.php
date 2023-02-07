<?php

declare(strict_types=1);

namespace Xaevik\Cuid2;

use Exception;
use JsonSerializable;
use OutOfRangeException;

final class Cuid2 implements JsonSerializable
{
    /** @readonly */
    private int $counter;

    /**
     * @var array<array-key, mixed>
     * @readonly
     */
    private array $fingerprint;

    /**
     * @var int<1, max>
     * @readonly
     */
    private int $length;

    /** @readonly */
    private string $prefix;

    /**
     * @var array<array-key, mixed>
     * @readonly
     */
    private array $random;

    /** @readonly */
    private int $timestamp;

    /**
     * Initializes a new instance of Cuid2.
     *
     * @param  int $maxLength The maximum string length value of the CUID.
     * @throws OutOfRangeException The value of $maxLength was less than 4 or greater than 32.
     * @throws Exception
     */
    public function __construct(int $maxLength = 24)
    {
        if ($maxLength < 4 || $maxLength > 32) {
            throw new OutOfRangeException("maxLength: cannot be less than 4 or greater than 32.");
        }

        $this->length = $maxLength;
        $this->counter = Counter::getInstance()->getNextValue();

        $this->fingerprint = Fingerprint::getInstance()->getValue();
        $this->prefix = chr(random_int(97, 122));
        $this->random = self::generateRandom();
        $this->timestamp = (int)(microtime(true) * 1000);
    }

    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * @return array<array-key, mixed>
     * @throws Exception
     */
    private function generateRandom(): array
    {
        $result = unpack('C*', random_bytes($this->length * 2));

        return !$result ? [] : $result;
    }

    public function toString(): string
    {
        return $this->render();
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): string
    {
        return $this->render();
    }

    private function render(): string
    {
        $hash = hash_init('sha3-512');

        hash_update($hash, (string)$this->timestamp);
        hash_update($hash, (string)$this->counter);

        hash_update($hash, bin2hex(pack('C*', ...$this->random)));
        hash_update($hash, bin2hex(pack('C*', ...$this->fingerprint)));

        $hash = hash_final($hash);

        if (extension_loaded('gmp')) {
            $result = gmp_strval(gmp_init($hash, 16), 36);
        } else {
            $result = base_convert($hash, 16, 36);
        }

        return $this->prefix . substr($result, 0, $this->length - 1);
    }
}
