<?php

namespace Keboola\DynamoDbExtractor;

class ScanLimitTest extends \PHPUnit_Framework_TestCase
{
    public function testLimitNull()
    {
        $scanLimit = new ScanLimit(100);

        $this->assertEquals(100, $scanLimit->getBatchSize());
        $this->assertTrue($scanLimit->shouldContinue());

        $scanLimit->decreaseLimit(100);  // shouldn't take any effect

        $this->assertEquals(100, $scanLimit->getBatchSize());
        $this->assertTrue($scanLimit->shouldContinue());
    }

    public function testLimitEqualsBatchSize()
    {
        $scanLimit = new ScanLimit(100, 100);
        $scanLimit->decreaseLimit(100);

        $this->assertEquals(0, $scanLimit->getBatchSize());
        $this->assertFalse($scanLimit->shouldContinue());
    }

    public function testLimitLessThanBatchSize()
    {
        $scanLimit = new ScanLimit(100, 10);

        $this->assertEquals(10, $scanLimit->getBatchSize());
        $this->assertTrue($scanLimit->shouldContinue());

        $scanLimit->decreaseLimit(10);

        $this->assertEquals(0, $scanLimit->getBatchSize());
        $this->assertFalse($scanLimit->shouldContinue());
    }

    public function testLimitGreaterThanBatchSize()
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
