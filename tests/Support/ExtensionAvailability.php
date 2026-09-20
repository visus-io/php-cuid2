<?php

declare(strict_types=1);

namespace Visus\Cuid2\Test\Support;

/**
 * Test-only toggle read by the extension_loaded() override in extension_loaded.php.
 *
 * Cuid2::convert() picks its base36 conversion path with extension_loaded('gmp'). CI
 * always has the gmp extension installed, so the pure-PHP fallback path never runs. This
 * class lets a test force that check to report an extension as unavailable, without
 * actually disabling the extension in the PHP process.
 *
 * This class holds the toggle in an environment variable, not a static property. The
 * repository reserves static state for the Counter and Fingerprint singletons. An
 * environment variable keeps this test seam outside that rule while still surviving
 * across the function call boundary in extension_loaded.php.
 */
final class ExtensionAvailability
{
    private const ENV_PREFIX = 'CUID2_TEST_DISABLED_EXTENSION_';

    public static function disable(string $extension): void
    {
        putenv(self::envName($extension) . '=1');
    }

    public static function isDisabled(string $extension): bool
    {
        return getenv(self::envName($extension)) !== false;
    }

    public static function reset(): void
    {
        $environment = getenv();
        if ($environment === false) {
            return;
        }

        foreach (array_keys($environment) as $name) {
            if (str_starts_with($name, self::ENV_PREFIX)) {
                putenv($name);
            }
        }
    }

    private static function envName(string $extension): string
    {
        return self::ENV_PREFIX . strtoupper($extension);
    }
}
