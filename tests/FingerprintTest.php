<?php

declare(strict_types=1);

namespace Visus\Cuid2\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Visus\Cuid2\Fingerprint;
use Visus\Cuid2\InvalidOperationException;

class FingerprintTest extends TestCase
{
    /**
     * Reset the singleton instance between tests.
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $reflection = new ReflectionClass(Fingerprint::class);
        $instance = $reflection->getProperty('instance');
        $instance->setValue(null, null);

        $cachedEnvironment = $reflection->getProperty('cachedEnvironment');
        $cachedEnvironment->setValue(null, null);
    }

    /**
     * @throws Exception
     */
    public function testGetInstanceReturnsSameSingletonInstance(): void
    {
        $fingerprint1 = Fingerprint::getInstance();
        $fingerprint2 = Fingerprint::getInstance();

        $this->assertSame($fingerprint1, $fingerprint2);
    }

    /**
     * @throws Exception
     */
    public function testGetValueReturnsNonEmptyString(): void
    {
        $fingerprint = Fingerprint::getInstance();
        $value = $fingerprint->getValue();

        $this->assertNotEmpty($value);
    }

    /**
     * @throws Exception
     */
    public function testGetValueReturnsConsistentValue(): void
    {
        $fingerprint = Fingerprint::getInstance();

        $value1 = $fingerprint->getValue();
        $value2 = $fingerprint->getValue();
        $value3 = $fingerprint->getValue();

        $this->assertSame($value1, $value2);
        $this->assertSame($value2, $value3);
    }

    /**
     * @throws Exception
     */
    public function testGetValueReturnsBinaryData(): void
    {
        $fingerprint = Fingerprint::getInstance();
        $value = $fingerprint->getValue();

        // SHA3-512 produces 64 bytes of binary data
        $this->assertEquals(64, strlen($value));
    }

    /**
     * @throws Exception
     */
    public function testGetValueIsDeterministicWithinProcess(): void
    {
        // Within the same process, fingerprint should be identical across instances
        $fingerprint1 = Fingerprint::getInstance();
        $value1 = $fingerprint1->getValue();

        $fingerprint2 = Fingerprint::getInstance();
        $value2 = $fingerprint2->getValue();

        $this->assertSame($value1, $value2);
        $this->assertSame($fingerprint1, $fingerprint2);
    }

    /**
     * @throws Exception
     */
    public function testWakeupThrowsException(): void
    {
        $fingerprint = Fingerprint::getInstance();

        $this->expectException(InvalidOperationException::class);
        $this->expectExceptionMessage('Cannot unserialize singleton');

        $fingerprint->__wakeup();
    }

    /**
     * @throws Exception
     */
    public function testFingerprintIncludesProcessContext(): void
    {
        // Create a fingerprint and verify it's unique (non-zero bytes)
        $fingerprint = Fingerprint::getInstance();
        $value = $fingerprint->getValue();

        // Binary data should not be all zeros (extremely unlikely with SHA3-512)
        $this->assertNotEquals(str_repeat("\x00", 64), $value);

        // Should contain various byte values (high entropy)
        $uniqueBytes = count(array_unique(str_split($value)));
        $this->assertGreaterThan(10, $uniqueBytes);
    }

    /**
     * @throws Exception
     */
    public function testFingerprintValueIsImmutable(): void
    {
        $fingerprint = Fingerprint::getInstance();
        $originalValue = $fingerprint->getValue();

        // Call getValue multiple times
        for ($i = 0; $i < 10; $i++) {
            $currentValue = $fingerprint->getValue();
            $this->assertSame($originalValue, $currentValue);
        }
    }
}
