<?php

declare(strict_types=1);

namespace Visus\Cuid2\Test\Benchmark;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Visus\Cuid2\Utils;

/**
 * Benchmarks for Utils base36 conversion performance.
 *
 * Tests Utils::hexToBase36() and Utils::bytesToBase36() at representative
 * sizes: the base_convert() fast-path boundary, just past it, and the
 * SHA3-512 digest size actually used by Cuid2 in production.
 */
final class UtilsBench
{
    /**
     * Provides raw byte strings at sizes matching hexToBase36's boundaries.
     *
     * @return iterable<string, array{bytes: string}>
     */
    public function provideByteValues(): iterable
    {
        yield 'fast-path-7-bytes' => ['bytes' => str_repeat("\xa1", 7)];
        yield 'sha3-256-sized-32-bytes' => ['bytes' => str_repeat("\xa1", 32)];
        yield 'sha3-512-sized-64-bytes' => ['bytes' => str_repeat("\xa1", 64)];
    }

    /**
     * Provides hex strings at the fast-path boundary and beyond.
     *
     * @return iterable<string, array{hex: string}>
     */
    public function provideHexValues(): iterable
    {
        yield 'fast-path-14-chars' => ['hex' => str_repeat('a1', 7)];
        yield 'just-past-fast-path-15-chars' => ['hex' => str_repeat('a1', 7) . 'a'];
        yield 'hash-sized-128-chars' => ['hex' => str_repeat('a1', 64)];
    }

    /**
     * Benchmark bytesToBase36() across representative input sizes.
     *
     * @param array{bytes: string} $params
     */
    #[ParamProviders('provideByteValues')]
    #[Revs(1000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['utils', 'bytesToBase36'])]
    public function benchBytesToBase36(array $params): string
    {
        return Utils::bytesToBase36($params['bytes']);
    }

    /**
     * Benchmark hexToBase36() across representative input sizes.
     *
     * @param array{hex: string} $params
     */
    #[ParamProviders('provideHexValues')]
    #[Revs(1000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['utils', 'hexToBase36'])]
    public function benchHexToBase36(array $params): string
    {
        return Utils::hexToBase36($params['hex']);
    }
}
