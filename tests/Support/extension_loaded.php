<?php

declare(strict_types=1);

namespace Visus\Cuid2;

use Visus\Cuid2\Test\Support\ExtensionAvailability;

/**
 * Overrides extension_loaded() for unqualified calls made from inside this namespace.
 *
 * PHP resolves an unqualified function call by first checking the caller's namespace,
 * then falling back to the global namespace. Cuid2::convert() calls extension_loaded()
 * unqualified, so this override intercepts that call during tests. It reports an
 * extension as unavailable when ExtensionAvailability::disable() names it, and defers to
 * the real, global extension_loaded() otherwise.
 *
 * This must stay a namespaced function, not a static class method. Only a function
 * named extension_loaded() in the Visus\Cuid2 namespace intercepts Cuid2::convert()'s
 * unqualified call.
 */
// phpcs:ignore Squiz.Functions.GlobalFunction.Found
function extension_loaded(string $extension): bool
{
    if (ExtensionAvailability::isDisabled($extension)) {
        return false;
    }

    return \extension_loaded($extension);
}
