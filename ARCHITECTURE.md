# Architecture

This document describes the internal design of `visus/cuid2`. It targets contributors and coding agents. For the public API and usage examples, see [README.md](README.md).

## Generation Flow

`Cuid2::generate()` builds each identifier through these steps:

1. Generate a random lowercase prefix character.
2. Read the current timestamp with `hrtime()`.
3. Increment the `Counter` singleton. Read its new value.
4. Read the `Fingerprint` singleton. The fingerprint is a hash of the hostname, the process ID, and environment data.
5. Generate entropy with `random_bytes()`.
6. Combine the timestamp, the counter, the fingerprint, and the entropy. Hash the combined value with SHA3-512. The result is a raw 64-byte digest. It is not a hex string.
7. Convert the raw digest to base36 with `Utils::bytesToBase36()`.
8. Prepend the prefix to the converted hash. Truncate the result to the requested length.

## Singletons

`Counter` and `Fingerprint` are process-scoped singletons.

- Each singleton has a private constructor.
- Each singleton exposes a static `getInstance(): static` method.
- Each singleton throws on `__clone` and on `__wakeup`.

Do not introduce static state outside these two classes.

## `Utils::hexToBase36()` and `Utils::bytesToBase36()`

`Cuid2::convert()` reads the raw hash digest bytes directly. It does not build a hex
string first. It uses `gmp_import()` and `gmp_strval()` when `ext-gmp` is loaded.
Otherwise, it calls `Utils::bytesToBase36()`.

`Utils::hexToBase36()` stays as a public entry point for other callers. It takes a hex
string. It shares its core logic with `bytesToBase36()`.

- For hex strings of 14 characters or fewer, `hexToBase36()` uses `base_convert()`. This
  is the fast path.
- For longer hex strings, and for every call to `bytesToBase36()`, the method packs the
  input into fixed-width base-2³² limbs. Limb 0 holds the least significant bits. The
  method then divides the limbs by 36^5 repeatedly. Each division extracts 5 base36
  digits at once. This cuts the number of full array scans by about 5 times. Each pass
  tracks the most significant non-zero limb. Later passes skip limbs that are already
  zero.

The GMP path and the pure-PHP path must produce the same output for the same input.

## `src/compat.php`

Composer loads this file through the `files` autoload key. The file polyfills `getmypid()` and `gethostname()`. It defines each function only when the function is missing natively.

Do not add polyfills to this file without strong justification.
