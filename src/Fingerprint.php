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

    private static ?string $cachedEnvironment = null;

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
        return self::$instance ??= new Fingerprint();
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

    /**
     * @return array<array-key, mixed>
     * @throws Exception
     */
    private function generateFingerprint(): array
    {
        $hostnameLength = PHP_OS_FAMILY === 'Windows' ? 15 : 32;

        $hostname = gethostname();

        $identity = !empty($hostname)
            ? $hostname
            : $this->generateRandomIdentity($hostnameLength);

        $hash = hash_init('sha3-512');

        hash_update($hash, $identity);
        hash_update($hash, (string)getmypid());
        hash_update($hash, $this->getCachedEnvironment());
        hash_update($hash, bin2hex(random_bytes(32)));

        $result = unpack('C*', hash_final($hash, true));

        return $result !== false ? array_values($result) : [];
    }

    private function getCachedEnvironment(): string
    {
        return self::$cachedEnvironment ??= serialize(getenv());
    }

    /**
     * @codeCoverageIgnore
     */
    private function generateRandomIdentity(int $length): string
    {
        $chars = 'abcdefghjkmnpqrstvwxyz0123456789';
        $result = '';
        $max = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max)];
        }

        return $result;
    }
}
