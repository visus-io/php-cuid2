<?php

declare(strict_types=1);

namespace Visus\Cuid2\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Visus\Cuid2\Counter;
use Visus\Cuid2\InvalidOperationException;

class CounterTest extends TestCase
{
    /**
     * Reset the singleton instance between tests.
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $reflection = new ReflectionClass(Counter::class);
        $instance = $reflection->getProperty('instance');
        $instance->setValue(null, null);
    }

    /**
     * @throws Exception
     */
    public function testGetInstanceReturnsSameSingletonInstance(): void
    {
        $counter1 = Counter::getInstance();
        $counter2 = Counter::getInstance();

        $this->assertSame($counter1, $counter2);
    }

    /**
     * @throws Exception
     */
    public function testGetNextValueReturnsInteger(): void
    {
        $counter = Counter::getInstance();
        $value = $counter->getNextValue();

        $this->assertGreaterThanOrEqual(0, $value);
    }

    /**
     * @throws Exception
     */
    public function testGetNextValueIncrementsSequentially(): void
    {
        $counter = Counter::getInstance();

        $value1 = $counter->getNextValue();
        $value2 = $counter->getNextValue();
        $value3 = $counter->getNextValue();

        $this->assertEquals($value1 + 1, $value2);
        $this->assertEquals($value2 + 1, $value3);
    }

    /**
     * @throws Exception
     */
    public function testGetNextValueReturnsValueWithinRange(): void
    {
        $counter = Counter::getInstance();
        $reflection = new ReflectionClass(Counter::class);
        $rangeConstant = $reflection->getConstant('RANGE');

        for ($i = 0; $i < 100; $i++) {
            $value = $counter->getNextValue();
            $this->assertGreaterThanOrEqual(0, $value);
            $this->assertLessThan($rangeConstant, $value);
        }
    }

    /**
     * @throws Exception
     */
    public function testGetNextValueWrapsAtRange(): void
    {
        $counter = Counter::getInstance();
        $reflection = new ReflectionClass(Counter::class);
        /** @var int $rangeConstant */
        $rangeConstant = $reflection->getConstant('RANGE');

        // Use reflection to set the counter value near RANGE
        $valueProperty = $reflection->getProperty('value');
        $valueProperty->setValue($counter, $rangeConstant - 2);

        $value1 = $counter->getNextValue();
        $value2 = $counter->getNextValue();
        $value3 = $counter->getNextValue();

        $this->assertEquals($rangeConstant - 2, $value1);
        $this->assertEquals($rangeConstant - 1, $value2);
        $this->assertEquals(0, $value3); // Wrapped to 0
    }

    /**
     * @throws Exception
     */
    public function testWakeupThrowsException(): void
    {
        $counter = Counter::getInstance();

        $this->expectException(InvalidOperationException::class);
        $this->expectExceptionMessage('Cannot unserialize singleton');

        $counter->__wakeup();
    }

    /**
     * @throws Exception
     */
    public function testInitialValueIsRandom(): void
    {
        // Create multiple instances (by resetting singleton) and verify they start with different values
        $initialValues = [];

        for ($i = 0; $i < 10; $i++) {
            $reflection = new ReflectionClass(Counter::class);
            $instance = $reflection->getProperty('instance');
            $instance->setValue(null, null);

            $counter = Counter::getInstance();
            $initialValues[] = $counter->getNextValue();
        }

        // Statistical test: At least some values should be different
        $uniqueValues = array_unique($initialValues);
        $this->assertGreaterThan(1, count($uniqueValues));
    }

    /**
     * @throws Exception
     */
    public function testPersistsAcrossMultipleCalls(): void
    {
        $counter1 = Counter::getInstance();
        $value1 = $counter1->getNextValue();

        $counter2 = Counter::getInstance();
        $value2 = $counter2->getNextValue();

        // Should be the same instance, so value2 should be value1 + 1
        $this->assertEquals($value1 + 1, $value2);
    }
}
