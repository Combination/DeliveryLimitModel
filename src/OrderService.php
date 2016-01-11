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
        if (empty($baskets)) {
            return [];
        }

        $orderGroupList = $this->groupByOrder($baskets);

        if (empty($orderGroupList[null])) {
            return $baskets;
        }

        if (empty($config)) {
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
            $config
        );

        $orderGroupAmountMap = $this->getOrderGroupAmountMap($orderGroupList);

        if (count($orderGroupAmountMap) === 1) {
            $nextOrderId = $this->getMaxOrderId($orderGroupList) ?: 1;

            foreach (array_keys($orderGroupList[null]) as $index) {
                $orderGroupList[null][$index]['order'] = $nextOrderId;
            }

            return call_user_func_array('array_merge', $orderGroupList);
        }

        return $baskets;
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
}