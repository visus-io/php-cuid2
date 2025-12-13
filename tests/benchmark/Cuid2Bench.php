<?php

declare(strict_types=1);

namespace Visus\Cuid2\Test\Benchmark;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Visus\Cuid2\Cuid2;

/**
 * Benchmarks for CUID2 generation performance.
 *
 * Tests various aspects of CUID2 generation including:
 * - Different CUID lengths
 * - Construction methods (constructor vs static factory)
 * - String conversion operations
 */
final class Cuid2Bench
{
    /**
     * Benchmark CUID2 generation with default length (24 characters).
     */
    #[Revs(1000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['generation', 'default'])]
    public function benchGenerateDefault(): void
    {
        new Cuid2();
    }

    /**
     * Benchmark CUID2 generation using static factory method.
     */
    #[Revs(1000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['generation', 'factory'])]
    public function benchGenerateStatic(): void
    {
        Cuid2::generate();
    }

    /**
     * Benchmark CUID2 generation with various lengths.
     *
     * @param array{length: int} $params
     */
    #[ParamProviders('provideLengths')]
    #[Revs(1000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['generation', 'lengths'])]
    public function benchGenerateWithLength(array $params): void
    {
        new Cuid2($params['length']);
    }

    /**
     * Benchmark string conversion performance.
     *
     * Since the value is cached during construction, this benchmarks
     * the overhead of accessing the cached value.
     */
    #[BeforeMethods('setUp')]
    #[Revs(10000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['conversion', 'string'])]
    public function benchToString(): void
    {
        (string) $this->cuid;
    }

    /**
     * Benchmark toString() method performance.
     */
    #[BeforeMethods('setUp')]
    #[Revs(10000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['conversion', 'string'])]
    public function benchToStringMethod(): void
    {
        // @phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
        $result = $this->cuid->toString();
    }

    /**
     * Benchmark JSON serialization performance.
     */
    #[BeforeMethods('setUp')]
    #[Revs(10000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['conversion', 'json'])]
    public function benchJsonSerialize(): void
    {
        json_encode($this->cuid);
    }

    /**
     * Benchmark batch generation of CUIDs.
     *
     * Tests performance when generating multiple CUIDs in sequence.
     *
     * @param array{count: int} $params
     */
    #[ParamProviders('provideBatchSizes')]
    #[Revs(100)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['generation', 'batch'])]
    public function benchBatchGeneration(array $params): void
    {
        for ($i = 0; $i < $params['count']; $i++) {
            new Cuid2();
        }
    }

    /**
     * Provides various CUID lengths for benchmarking.
     *
     * @return iterable<string, array{length: int}>
     */
    public function provideLengths(): iterable
    {
        yield 'minimum-4' => ['length' => 4];
        yield 'short-10' => ['length' => 10];
        yield 'default-24' => ['length' => 24];
        yield 'maximum-32' => ['length' => 32];
    }

    /**
     * Provides batch sizes for batch generation benchmarks.
     *
     * @return iterable<string, array{count: int}>
     */
    public function provideBatchSizes(): iterable
    {
        yield 'small-10' => ['count' => 10];
        yield 'medium-100' => ['count' => 100];
        yield 'large-1000' => ['count' => 1000];
    }

    /**
     * CUID instance for conversion benchmarks.
     */
    private Cuid2 $cuid;

    /**
     * Set up CUID instance for conversion benchmarks.
     */
    public function setUp(): void
    {
        $this->cuid = new Cuid2();
    }
}
