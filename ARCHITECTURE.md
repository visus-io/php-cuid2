# Architecture

This document describes the internal design of `visus/cuid2`. It targets contributors and coding agents. For the public API and usage examples, see [README.md](README.md).

## Generation Flow

`Cuid2::generate()` builds each identifier through these steps:

1. Generate a random lowercase prefix character.
2. Read the current timestamp with `hrtime()`.
3. Increment the `Counter` singleton. Read its new value.
4. Read the `Fingerprint` singleton. The fingerprint is a hash of the hostname, the process ID, and environment data.
5. Generate entropy with `random_bytes()`.
6. Combine the timestamp, the counter, the fingerprint, and the entropy. Hash the combined value with SHA3-512.
7. Convert the hash from hex to base36 with `Utils::hexToBase36()`.
8. Prepend the prefix to the converted hash. Truncate the result to the requested length.

## Singletons

`Counter` and `Fingerprint` are process-scoped singletons.

- Each singleton has a private constructor.
- Each singleton exposes a static `getInstance(): static` method.
- Each singleton throws on `__clone` and on `__wakeup`.

Do not introduce static state outside these two classes.

## `Utils::hexToBase36()`

This method converts a hex string to base36. It selects a conversion path based on the input:

- For hex strings of 14 characters or fewer, it uses a fast path with `base_convert()`.
- For longer hex strings, it uses chunked base-100,000,000 conversion.
- When `ext-gmp` is loaded, it uses the GMP path instead of the pure-PHP path.

The GMP path and the pure-PHP path must produce identical output for the same input.

## `src/compat.php`

Composer loads this file through the `files` autoload key. The file polyfills `getmypid()` and `gethostname()`. It defines each function only when the function is missing natively.

Do not add polyfills to this file without strong justification.
