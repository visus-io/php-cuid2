<?php

declare(strict_types=1);

namespace Xaevik\Cuid2;

use Exception;
use OutOfRangeException;

final class Cuid2
{
    private readonly int $counter;

    /**
     * @var array<array-key, mixed>
     */
    private readonly array $fingerprint;

    /**
     * @var int<1, max>
     */
    private readonly int $length;

    private readonly string $prefix;

    /**
     * @var array<array-key, mixed>
     */
    private readonly array $random;

    /**
     * @var array<array-key, mixed>
     */
    private readonly array $salt;

    private readonly int $timestamp;

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

        $this->prefix = chr(rand(97, 122));
        $this->salt = self::generateRandom();

        $this->timestamp = (int)(microtime(true) * 1000);
        $this->random = self::generateRandom();
        $this->fingerprint = self::generateFingerprint();
    }

    /**
     * @return array<array-key, mixed>
     * @throws Exception
     */
    private static function generateFingerprint(): array
    {
        $random = bin2hex(random_bytes(8));

        /** @var string $host */
        $host = self::getRemoteHostAddr() ?: gethostname() ?: bin2hex(random_bytes(4));
        $process = (string)(getmygid() ?: random_int(PHP_INT_MIN, PHP_INT_MAX) * 2063);

        $hash = hash_init('sha3-512');

        hash_update($hash, $random);
        hash_update($hash, $host);
        hash_update($hash, $process);

        $result = unpack('C*', hash_final($hash));

        return !$result ? [] : $result;
    }

    private static function getRemoteHostAddr(): string|bool
    {
        $fields = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_COMING_FROM',
            'HTTP_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'HTTP_VIA',
            'HTTP_XROXY_CONNECTION',
            'HTTP_PROXY_CONNECTION',
            'REMOTE_ADDR'
        ];

        $addresses = [];

        foreach ($fields as $field) {
            if (empty($_SERVER[$field])) {
                continue;
            }

            /** @var string|bool $result */
            $result = filter_var(
                $_SERVER[$field],
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE
            );

            if (!$result) {
                continue;
            }

            $addresses[] = $result;
        }

        return reset($addresses);
    }

    /**
     * @return array<array-key, mixed>
     * @throws Exception
     */
    private function generateRandom(): array
    {
        $result = unpack('C*', random_bytes($this->length));

        return !$result ? [] : $result;
    }

    public function __toString(): string
    {
        $hash = hash_init('sha3-512');

        hash_update($hash, (string)$this->timestamp);
        hash_update($hash, (string)$this->counter);

        hash_update($hash, bin2hex(pack('C*', ...$this->random)));
        hash_update($hash, bin2hex(pack('C*', ...$this->fingerprint)));
        hash_update($hash, bin2hex(pack('C*', ...$this->salt)));

        $hash = hash_final($hash);

        if (extension_loaded('gmp')) {
            $result = gmp_strval(gmp_init($hash, 16), 36);
        } else {
            $result = base_convert($hash, 16, 36);
        }

        return $this->prefix . substr($result, 0, $this->length - 1);
    }
}
