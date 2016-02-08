<?php

use ReenExe\DeliveryLimitModel\DeliveryLimitConfig;

class DeliveryLimitConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \ReenExe\DeliveryLimitModel\DeliveryLimitConfig::isEmpty
     * @covers \ReenExe\DeliveryLimitModel\DeliveryLimitConfig::inLimit
     */
    public function testEmpty()
    {
        $config = new DeliveryLimitConfig([]);

        $this->assertTrue($config->isEmpty());

        $this->assertTrue($config->inLimit(1e7));
    }

    /**
     * @covers \ReenExe\DeliveryLimitModel\DeliveryLimitConfig::isEmpty
     * @covers \ReenExe\DeliveryLimitModel\DeliveryLimitConfig::getMin
     * @covers \ReenExe\DeliveryLimitModel\DeliveryLimitConfig::getMax
     * @covers \ReenExe\DeliveryLimitModel\DeliveryLimitConfig::inLimit
     */
    public function testMax()
    {
        $max = 1e3;

        $config = new DeliveryLimitConfig([
            'max' => $max
        ]);

        $this->assertFalse($config->isEmpty());
        $this->assertSame($config->getMin(), 0);
        $this->assertSame($config->getMax(), $max);

        $this->assertTrue($config->inLimit(1));
        $this->assertTrue($config->inLimit(1e2));
        $this->assertFalse($config->inLimit(1e5));
    }

    /**
     * @covers \ReenExe\DeliveryLimitModel\DeliveryLimitConfig::isEmpty
     * @covers \ReenExe\DeliveryLimitModel\DeliveryLimitConfig::getMin
     * @covers \ReenExe\DeliveryLimitModel\DeliveryLimitConfig::getMax
     * @covers \ReenExe\DeliveryLimitModel\DeliveryLimitConfig::inLimit
     */
    public function testMin()
    {
        $min = 10;
        $max = 1e3;
        $config = new DeliveryLimitConfig([
            'min' => $min,
            'max' => $max,
        ]);

        $this->assertFalse($config->isEmpty());
        $this->assertSame($config->getMin(), $min);
        $this->assertSame($config->getMax(), $max);

        $this->assertFalse($config->inLimit(1));
        $this->assertTrue($config->inLimit(1e2));
        $this->assertFalse($config->inLimit(1e5));
    }
}
