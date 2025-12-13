<?php

declare(strict_types=1);

namespace Visus\Cuid2\Test\Benchmark;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Visus\Cuid2\Counter;
use Visus\Cuid2\Fingerprint;

/**
 * Benchmarks for singleton access performance.
 *
 * Tests the performance of singleton getInstance() calls and value retrieval
 * for both Counter and Fingerprint classes. Since these are cached, this
 * benchmarks the overhead of singleton access after initialization.
 */
final class SingletonBench
{
    /**
     * Benchmark Counter singleton getInstance() access.
     *
     * Tests the performance of accessing an already-initialized Counter singleton.
     * After the first access, this should be extremely fast due to caching.
     */
    #[Revs(100000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['singleton', 'counter'])]
    public function benchCounterGetInstance(): void
    {
        Counter::getInstance();
    }

    /**
     * Benchmark Counter getNextValue() performance.
     *
     * Tests the performance of incrementing the counter and returning its value.
     * This includes the modulo operation for wrapping at RANGE.
     */
    #[Revs(100000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['singleton', 'counter'])]
    public function benchCounterGetNextValue(): void
    {
        Counter::getInstance()->getNextValue();
    }

    /**
     * Benchmark Fingerprint singleton getInstance() access.
     *
     * Tests the performance of accessing an already-initialized Fingerprint singleton.
     * After the first access, this should be extremely fast due to caching.
     */
    #[Revs(100000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['singleton', 'fingerprint'])]
    public function benchFingerprintGetInstance(): void
    {
        Fingerprint::getInstance();
    }

    /**
     * Benchmark Fingerprint getValue() performance.
     *
     * Tests the performance of retrieving the cached fingerprint value.
     * This should be extremely fast as it simply returns a readonly property.
     */
    #[Revs(100000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['singleton', 'fingerprint'])]
    public function benchFingerprintGetValue(): void
    {
        // @phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
        $result = Fingerprint::getInstance()->getValue();
    }

    /**
     * Benchmark combined singleton access for CUID generation.
     *
     * This simulates the singleton access pattern used during CUID generation,
     * where both Counter and Fingerprint are accessed.
     */
    #[Revs(10000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['singleton', 'combined'])]
    public function benchCombinedSingletonAccess(): void
    {
        // @phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
        $counterValue = Counter::getInstance()->getNextValue();
        // @phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
        $fingerprintValue = Fingerprint::getInstance()->getValue();
    }
}
