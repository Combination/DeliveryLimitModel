<?php

namespace ReenExe\DeliveryLimitModel;

class Command
{
    public function execute(array $baskets, array $config)
    {
        if (empty($baskets)) {
            return [];
        }

        $orderGroupList = $this->groupByOrder($baskets);

        if (empty($config)) {
            if (isset($orderGroupList[null])) {
                $nextOrderId = $this->getMaxOrderId($orderGroupList) ?: 1;

                foreach (array_keys($orderGroupList[null]) as $index) {
                    $orderGroupList[null][$index]['order'] = $nextOrderId;
                }
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
}
