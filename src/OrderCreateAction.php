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
            $nextOrderId = $this->getNextOrderId($orderGroupList);

            foreach (array_keys($orderGroupList[null]) as $index) {
                $orderGroupList[null][$index]['order'] = $nextOrderId;
            }

            return call_user_func_array('array_merge', $orderGroupList);
        }

        $this->rebuildConfig();

        if (count($orderGroupList) === 1) {
            $nextOrderId = $this->getNextOrderId($orderGroupList);

            $orderGroupAmountMap[$nextOrderId] = 0;

            $result = [];
            $orderGroup = $orderGroupList[null];
            foreach ($orderGroup as $basket) {

                $basketAmount = $basket['price'] * $basket['quantity'];

                $orderGroupAmount = $orderGroupAmountMap[$nextOrderId] + $basketAmount;

                if ($this->inLimit($orderGroupAmount)) {
                    $orderGroupAmountMap[$nextOrderId] = $orderGroupAmount;
                    $basket['order'] = $nextOrderId;
                } elseif ($this->inLimit($basketAmount)) {
                    $nextOrderId += 1;
                    $basket['order'] = $nextOrderId;
                }

                $result[] = $basket;
            }

            return $result;
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

    private function getNextOrderId(array $orderGroupList)
    {
        return $this->getMaxOrderId($orderGroupList) ?: 1;
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

    private function inLimit($amount)
    {
        return $this->config['min'] <= $amount && $amount <= $this->config['max'];
    }

    private function rebuildConfig()
    {
        $this->config = array_merge(
            [
                'min' => 0,
                'max' => PHP_INT_MAX,
            ],
            $this->config
        );
    }
}
