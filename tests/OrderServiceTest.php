<?php

use ReenExe\DeliveryLimitModel\OrderService;

class OrderServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     * @param array $input
     * @param array $config
     * @param array $output
     */
    public function test(array $input, array $config, array $output)
    {
        $command = new OrderService();

        $this->assertSame($command->create($input, $config), $output);
    }

    public function dataProvider()
    {
        // Передаємо пустий кошик
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

        // Передаємо вже готові замовлення, без нових товарів
        yield [
            [$item],
            [],
            [$item]
        ];

        // Без лімітів - створюємо замовлення
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

        // Без лімітів - об’єднуємо з існуючим замовленням
        $orderIdList = [1, 250, 1025];
        foreach ($orderIdList as $orderId) {
            yield [
                [
                    [
                        'id' => 1,
                        'order' => $orderId,
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
                        'order' => $orderId,
                        'code' => 1,
                        'price' => 1,
                        'quantity' => 1,
                    ],
                    [
                        'id' => 2,
                        'order' => $orderId,
                        'code' => 2,
                        'price' => 1,
                        'quantity' => 1,
                    ],
                ]
            ];
        }

        // Існує нижній ліміт і замовлення його задовольняє
        $minAmountDataProvider = [20, 50, 100];
        foreach ($minAmountDataProvider as $min) {
            yield [
                [
                    [
                        'id' => 1,
                        'order' => null,
                        'code' => 1,
                        'price' => 100,
                        'quantity' => 1,
                    ],
                ],
                [
                    'min' => $min
                ],
                [
                    [
                        'id' => 1,
                        'order' => 1,
                        'code' => 1,
                        'price' => 100,
                        'quantity' => 1,
                    ],
                ]
            ];
        }

        // Намагаємось створити замовлення меньше можливого
        yield [
            [
                [
                    'id' => 1,
                    'order' => null,
                    'code' => 1,
                    'price' => 100,
                    'quantity' => 1,
                ],
            ],
            [
                'min' => 150
            ],
            [
                [
                    'id' => 1,
                    'order' => null,
                    'code' => 1,
                    'price' => 100,
                    'quantity' => 1,
                ],
            ]
        ];

        // Тільки частина корзини може бути оформлена в замовлення, а залишок меньше ліміта
        $minAmountDataProvider = [150, 200];
        foreach ($minAmountDataProvider as $min) {
            yield [
                [
                    [
                        'id' => 1,
                        'order' => null,
                        'code' => 1,
                        'price' => 100,
                        'quantity' => 1,
                    ],
                    [
                        'id' => 2,
                        'order' => null,
                        'code' => 2,
                        'price' => 250,
                        'quantity' => 1,
                    ],
                ],
                [
                    'min' => $min
                ],
                [
                    [
                        'id' => 1,
                        'order' => 1,
                        'code' => 1,
                        'price' => 100,
                        'quantity' => 1,
                    ],
                    [
                        'id' => 2,
                        'order' => 1,
                        'code' => 2,
                        'price' => 250,
                        'quantity' => 1,
                    ],
                ]
            ];
        }

        // Тільки частина корзини може бути оформлена в замовлення, а в залишку товар, який більше ліміта
        $maxAmountDataProvider = [150, 200];
        foreach ($maxAmountDataProvider as $max) {
            yield [
                [
                    [
                        'id' => 1,
                        'order' => null,
                        'code' => 1,
                        'price' => 100,
                        'quantity' => 1,
                    ],
                    [
                        'id' => 2,
                        'order' => null,
                        'code' => 2,
                        'price' => 250,
                        'quantity' => 1,
                    ],
                ],
                [
                    'max' => $max
                ],
                [
                    [
                        'id' => 1,
                        'order' => 1,
                        'code' => 1,
                        'price' => 100,
                        'quantity' => 1,
                    ],
                    [
                        'id' => 2,
                        'order' => null,
                        'code' => 2,
                        'price' => 250,
                        'quantity' => 1,
                    ],
                ]
            ];
        }

        /**
         * Якщо в корзині є продукт з кількістю 2+, що перевищує максимальний ліміт
         * то маємо розділити в різні замовлення.
         */
        yield [
            [
                [
                    'id' => 1,
                    'order' => null,
                    'code' => 1,
                    'price' => 100,
                    'quantity' => 2,
                ],
            ],
            [
                'max' => 100
            ],
            [
                [
                    'id' => 1,
                    'order' => 1,
                    'code' => 1,
                    'price' => 100,
                    'quantity' => 1,
                ],
                [
                    'id' => 2,
                    'order' => 2,
                    'code' => 1,
                    'price' => 100,
                    'quantity' => 1,
                ],
            ]
        ];

        // Після розділення - залишаеться продукт виходить за межі лімітів
        yield [
            [
                [
                    'id' => 1,
                    'order' => null,
                    'code' => 1,
                    'price' => 50,
                    'quantity' => 3,
                ],
            ],
            [
                'min' => 75,
                'max' => 100
            ],
            [
                [
                    'id' => 1,
                    'order' => 1,
                    'code' => 1,
                    'price' => 50,
                    'quantity' => 2,
                ],
                [
                    'id' => 2,
                    'order' => null,
                    'code' => 1,
                    'price' => 50,
                    'quantity' => 1,
                ],
            ]
        ];
    }
}
