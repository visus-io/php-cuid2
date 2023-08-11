<?php

declare(strict_types=1);

namespace Visus\Cuid2;

use Exception;

/**
 * Singleton responsible for generating and storing a fingerprint
 *
 * @internal
 * @psalm-internal Visus\Cuid2
 */
final class Fingerprint
{
    private static ?Fingerprint $instance = null;

    /**
     * @var array<array-key, mixed>
     */
    private readonly array $value;

    /**
     * @throws Exception
     */
    private function __construct()
    {
        $this->value = $this->generateFingerprint();
    }

    /**
     * Gets the current instance.
     *
     * @return Fingerprint
     */
    public static function getInstance(): Fingerprint
    {
        if (is_null(self::$instance)) {
            self::$instance = new Fingerprint();
        }

        return self::$instance;
    }

    /**
     * Gets the value from the instance.
     *
     * @return array<array-key, mixed>
     */
    public function getValue(): array
    {
        return $this->value;
    }

    private function getRemoteHostAddr(): bool|string
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

            $result = filter_var($_SERVER[$field], FILTER_VALIDATE_IP);

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
    private function generateFingerprint(): array
    {
        $random = bin2hex(random_bytes(8));
        $host = $this->getRemoteHostAddr() ?: gethostname() ?: bin2hex(random_bytes(4));
        $process = (string)(getmypid() ?: random_int(1, 32768));

        $hash = hash_init('sha3-512');

        hash_update($hash, $random);

        /** @var string $host */
        hash_update($hash, $host);

        hash_update($hash, $process);

        $result = unpack('C*', hash_final($hash));

        return !$result ? [] : $result;
    }
}
