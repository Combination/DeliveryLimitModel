<?php

use ReenExe\DeliveryLimitModel\Command;

class BlankTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     * @param array $input
     * @param array $output
     */
    public function test(array $input, array $output)
    {
        $command = new Command();

        $this->assertSame($command->execute($input), $output);
    }

    public function dataProvider()
    {
        yield [
            [],
            []
        ];
    }
}
