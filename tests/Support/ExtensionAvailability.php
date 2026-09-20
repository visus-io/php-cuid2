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
 */
final class ExtensionAvailability
{
    /**
     * @var array<string, true>
     */
    private static array $disabledExtensions = [];

    public static function disable(string $extension): void
    {
        self::$disabledExtensions[$extension] = true;
    }

    public static function isDisabled(string $extension): bool
    {
        return isset(self::$disabledExtensions[$extension]);
    }

    public static function reset(): void
    {
        self::$disabledExtensions = [];
    }
}
