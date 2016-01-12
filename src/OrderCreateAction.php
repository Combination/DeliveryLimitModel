<?php

namespace ReenExe\DeliveryLimitModel;

class OrderCreateAction
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $baskets;

    /**
     * @param array $config
     * @param array $baskets
     */
    public function __construct(array $config, array $baskets)
    {
        $this->config = $config;
        $this->baskets = $baskets;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        if (empty($this->baskets)) {
            return [];
        }

        $orderGroupList = $this->groupByOrder($this->baskets);

        if (empty($orderGroupList[null])) {
            return $this->baskets;
        }

        if (empty($this->config)) {
            $nextOrderId = $this->getMaxOrderId($orderGroupList) ?: 1;

            foreach (array_keys($orderGroupList[null]) as $index) {
                $orderGroupList[null][$index]['order'] = $nextOrderId;
            }

            return call_user_func_array('array_merge', $orderGroupList);
        }

        $config = array_merge(
            [
                'min' => 0,
                'max' => PHP_INT_MAX,
            ],
            $this->config
        );

        if (count($orderGroupList) === 1) {
            $nextOrderId = $this->getMaxOrderId($orderGroupList) ?: 1;

            $orderGroupAmountMap[$nextOrderId] = 0;

            foreach (array_keys($orderGroupList[null]) as $index) {
                $basket = $orderGroupList[null][$index];

                $basketAmount = $basket['price'] * $basket['quantity'];

                $orderGroupAmount = $orderGroupAmountMap[$nextOrderId] + $basketAmount;

                if ($this->inRange($config, $orderGroupAmount)) {
                    $orderGroupAmountMap[$nextOrderId] = $orderGroupAmount;
                } elseif ($config['min'] <= $basketAmount && $basketAmount <= $config['max']) {
                    $nextOrderId += 1;
                } else {
                    continue;
                }

                $orderGroupList[null][$index]['order'] = $nextOrderId;
            }

            return call_user_func_array('array_merge', $orderGroupList);
        }

        return $this->baskets;
    }

    private function groupByOrder(array $baskets)
    {
        $result = [];
        foreach ($baskets as $basket) {
            $result[$basket['order']][] = $basket;
        }
        return $result;
    }

    private function getMaxOrderId(array $orderGroupList)
    {
        return max(array_keys($orderGroupList));
    }

    private function getOrderGroupAmountMap(array $orderGroupList)
    {
        $result = [];
        foreach ($orderGroupList as $orderId => $orderGroup) {
            $result[$orderId] = 0;
            foreach ($orderGroup as $basket) {
                $result[$orderId] += $basket['price'] * $basket['quantity'];
            }
        }
        return $result;
    }

    private function inRange(array $config, $amount)
    {
        return $config['min'] <= $amount && $amount <= $config['max'];
    }
}
