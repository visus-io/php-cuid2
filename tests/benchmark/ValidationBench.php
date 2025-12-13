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
 * Benchmarks for CUID2 validation performance.
 *
 * Tests the performance of the isValid() method with:
 * - Valid CUIDs of different lengths
 * - Invalid CUIDs (various failure modes)
 * - Strict length validation
 */
final class ValidationBench
{
    /**
     * Benchmark validation of valid CUIDs without expected length.
     *
     * @param array{cuid: string} $params
     */
    #[ParamProviders('provideValidCuids')]
    #[Revs(10000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['validation', 'valid'])]
    public function benchValidateValidCuid(array $params): void
    {
        Cuid2::isValid($params['cuid']);
    }

    /**
     * Benchmark validation of valid CUIDs with expected length.
     *
     * @param array{cuid: string, length: int} $params
     */
    #[ParamProviders('provideValidCuidsWithLength')]
    #[Revs(10000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['validation', 'valid', 'strict'])]
    public function benchValidateValidCuidStrictLength(array $params): void
    {
        Cuid2::isValid($params['cuid'], $params['length']);
    }

    /**
     * Benchmark validation of invalid CUIDs.
     *
     * @param array{cuid: string} $params
     */
    #[ParamProviders('provideInvalidCuids')]
    #[Revs(10000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['validation', 'invalid'])]
    public function benchValidateInvalidCuid(array $params): void
    {
        Cuid2::isValid($params['cuid']);
    }

    /**
     * Benchmark validation of empty string.
     */
    #[Revs(10000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['validation', 'invalid', 'edge'])]
    public function benchValidateEmptyString(): void
    {
        Cuid2::isValid('');
    }

    /**
     * Benchmark validation of very long string.
     */
    #[BeforeMethods('setUpLongString')]
    #[Revs(10000)]
    #[Iterations(10)]
    #[Warmup(2)]
    #[Groups(['validation', 'invalid', 'edge'])]
    public function benchValidateLongString(): void
    {
        Cuid2::isValid($this->longString);
    }

    /**
     * Provides valid CUIDs of various lengths.
     *
     * @return iterable<string, array{cuid: string}>
     */
    public function provideValidCuids(): iterable
    {
        yield 'minimum-4' => ['cuid' => (new Cuid2(4))->toString()];
        yield 'short-10' => ['cuid' => (new Cuid2(10))->toString()];
        yield 'default-24' => ['cuid' => (new Cuid2())->toString()];
        yield 'maximum-32' => ['cuid' => (new Cuid2(32))->toString()];
    }

    /**
     * Provides valid CUIDs with their expected lengths.
     *
     * @return iterable<string, array{cuid: string, length: int}>
     */
    public function provideValidCuidsWithLength(): iterable
    {
        yield 'minimum-4' => ['cuid' => (new Cuid2(4))->toString(), 'length' => 4];
        yield 'short-10' => ['cuid' => (new Cuid2(10))->toString(), 'length' => 10];
        yield 'default-24' => ['cuid' => (new Cuid2())->toString(), 'length' => 24];
        yield 'maximum-32' => ['cuid' => (new Cuid2(32))->toString(), 'length' => 32];
    }

    /**
     * Provides invalid CUID strings.
     *
     * @return iterable<string, array{cuid: string}>
     */
    public function provideInvalidCuids(): iterable
    {
        yield 'uppercase-first' => ['cuid' => 'A23456789012345678901234'];
        yield 'uppercase-middle' => ['cuid' => 'a234567890123456789O1234'];
        yield 'contains-dash' => ['cuid' => 'a234567890-234567890123'];
        yield 'contains-underscore' => ['cuid' => 'a2345678_01234567890123'];
        yield 'contains-special' => ['cuid' => 'a23456789@1234567890123'];
        yield 'starts-with-number' => ['cuid' => '123456789012345678901234'];
        yield 'too-short' => ['cuid' => 'abc'];
    }

    /**
     * Long invalid string for edge case testing.
     */
    private string $longString;

    /**
     * Set up long string for validation benchmark.
     */
    public function setUpLongString(): void
    {
        $this->longString = str_repeat('a', 100);
    }
}
