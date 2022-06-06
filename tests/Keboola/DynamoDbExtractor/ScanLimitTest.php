<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

use PHPUnit\Framework\TestCase;

class ScanLimitTest extends TestCase
{
    public function testLimitNull(): void
    {
        $scanLimit = new ScanLimit(100);

        $this->assertEquals(100, $scanLimit->getBatchSize());
        $this->assertTrue($scanLimit->shouldContinue());

        $scanLimit->decreaseLimit(100);  // shouldn't take any effect

        $this->assertEquals(100, $scanLimit->getBatchSize());
        $this->assertTrue($scanLimit->shouldContinue());
    }

    public function testLimitEqualsBatchSize(): void
    {
        $scanLimit = new ScanLimit(100, 100);
        $scanLimit->decreaseLimit(100);

        $this->assertEquals(0, $scanLimit->getBatchSize());
        $this->assertFalse($scanLimit->shouldContinue());
    }

    public function testLimitLessThanBatchSize(): void
    {
        $scanLimit = new ScanLimit(100, 10);

        $this->assertEquals(10, $scanLimit->getBatchSize());
        $this->assertTrue($scanLimit->shouldContinue());

        $scanLimit->decreaseLimit(10);

        $this->assertEquals(0, $scanLimit->getBatchSize());
        $this->assertFalse($scanLimit->shouldContinue());
    }

    public function testLimitGreaterThanBatchSize(): void
    {
        $scanLimit = new ScanLimit(100, 223);
        $scanLimit->decreaseLimit(100);

        $this->assertEquals(100, $scanLimit->getBatchSize());
        $this->assertTrue($scanLimit->shouldContinue());

        $scanLimit->decreaseLimit(100);

        $this->assertEquals(23, $scanLimit->getBatchSize());
        $this->assertTrue($scanLimit->shouldContinue());

        $scanLimit->decreaseLimit(100); // decreasing by greater number than remaining limit

        $this->assertEquals(0, $scanLimit->getBatchSize());
        $this->assertFalse($scanLimit->shouldContinue());
    }
}
