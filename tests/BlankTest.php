<?php

use ReenExe\DeliveryLimitModel\Command;

class BlankTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     * @param array $input
     * @param array $config
     * @param array $output
     */
    public function test(array $input, array $config, array $output)
    {
        $command = new Command();

        $this->assertSame($command->execute($input, $config), $output);
    }

    public function dataProvider()
    {
        yield [
            [],
            [],
            []
        ];

        yield [
            [
                [
                    'id' => 1,
                    'order' => 1,
                    'price' => 1,
                    'quantity' => 1,
                ],
            ],
            [

            ],
            [
                [
                    'id' => 1,
                    'order' => 1,
                    'price' => 1,
                    'quantity' => 1,
                ],
            ]
        ];
    }
}
