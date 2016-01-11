<?php

use ReenExe\DeliveryLimitModel\Command;

class CommandTest extends \PHPUnit_Framework_TestCase
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

        $item = [
            'id' => 1,
            'order' => 1,
            'code' => 1,
            'price' => 1,
            'quantity' => 1,
        ];

        yield [
            [$item],
            [],
            [$item]
        ];

        yield [
            [
                [
                    'id' => 1,
                    'order' => null,
                    'code' => 1,
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
                    'code' => 1,
                    'price' => 1,
                    'quantity' => 1,
                ],
            ]
        ];

        yield [
            [
                [
                    'id' => 1,
                    'order' => 1,
                    'code' => 1,
                    'price' => 1,
                    'quantity' => 1,
                ],
                [
                    'id' => 2,
                    'order' => null,
                    'code' => 2,
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
                    'code' => 1,
                    'price' => 1,
                    'quantity' => 1,
                ],
                [
                    'id' => 2,
                    'order' => 1,
                    'code' => 2,
                    'price' => 1,
                    'quantity' => 1,
                ],
            ]
        ];
    }
}
