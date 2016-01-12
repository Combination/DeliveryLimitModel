<?php

namespace ReenExe\DeliveryLimitModel;

class OrderService
{
    /**
     * @param array $baskets
     * @param array $config
     * @return array
     */
    public function create(array $baskets, array $config)
    {
        $action = new OrderCreateAction($config, $baskets);

        return $action->getResponse();
    }
}
