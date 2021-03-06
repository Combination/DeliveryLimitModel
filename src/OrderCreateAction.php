<?php

namespace ReenExe\DeliveryLimitModel;

class OrderCreateAction
{
    /**
     * @var DeliveryLimitConfig
     */
    private $config;

    /**
     * @var array
     */
    private $baskets;

    private $nextBasketId;

    /**
     * @param array $config
     * @param array $baskets
     */
    public function __construct(array $baskets, DeliveryLimitConfig $config)
    {
        $this->baskets = $baskets;
        $this->config = $config;
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

        if ($this->config->isEmpty()) {
            $nextOrderId = $this->getNextOrderId($orderGroupList);

            foreach (array_keys($orderGroupList[null]) as $index) {
                $orderGroupList[null][$index]['order'] = $nextOrderId;
            }

            return call_user_func_array('array_merge', $orderGroupList);
        }

        if ($this->isFirstFreeBaskets($orderGroupList)) {
            return $this->createFirstOrders($orderGroupList);
        }

        $orderGroupAmountMap = $this->getOrderGroupAmountMap($orderGroupList);

        $freeOrderGroupAmount = $orderGroupAmountMap[null];
        $orderIdList = array_filter(array_keys($orderGroupAmountMap));
        foreach ($orderIdList as $orderId) {
            if ($this->config->inLimit($orderGroupAmountMap[$orderId] + $freeOrderGroupAmount)) {
                return $this->setFreeBasketsOrderId($orderGroupList[null], $orderId);
            }
        }

        if ($this->config->inLimit($freeOrderGroupAmount)) {
            $nextOrderId = $this->getMaxOrderId($orderGroupList) + 1;
            return $this->setFreeBasketsOrderId($orderGroupList[null], $nextOrderId);
        }

        return $this->baskets;
    }

    private function isFirstFreeBaskets($orderGroupList)
    {
        return count($orderGroupList) === 1;
    }

    private function createFirstOrders(array $orderGroupList)
    {
        $nextOrderId = 1;

        $orderGroupAmountMap = $this->getOrderGroupAmountMap($orderGroupList);
        $orderGroup = $orderGroupList[null];

        $result = [];
        if ($this->config->inLimit($orderGroupAmountMap[null])) {
            foreach ($orderGroup as $basket) {
                $basket['order'] = $nextOrderId;
                $result[] = $basket;
            }
            return $result;
        }

        $orderGroupAmountMap = new DefaultDictionary(0);

        for ($index = 0; $index < count($orderGroup); ++$index) {
            $basket = $orderGroup[$index];
            $basketAmount = $basket['price'] * $basket['quantity'];

            $orderGroupAmount = $orderGroupAmountMap[$nextOrderId] + $basketAmount;

            if ($this->config->inLimit($orderGroupAmount)) {
                $orderGroupAmountMap[$nextOrderId] = $orderGroupAmount;
                $basket['order'] = $nextOrderId;
            } elseif ($this->config->inLimit($basketAmount)) {
                $nextOrderId += 1;
                $basket['order'] = $nextOrderId;
            } elseif ($basket['quantity'] > 1) {
                $restAmount = $this->config->getMax() - $orderGroupAmountMap[$nextOrderId];

                $possibleQuantity = (int)floor($restAmount / $basket['price']);

                $restQuantity = $basket['quantity'] - $possibleQuantity;
                $restBasket = $basket;
                $restBasket['quantity'] = $restQuantity;
                $restBasket['id'] = $this->getNextBasketId();
                $orderGroup[] = $restBasket;

                $basket['quantity'] = $possibleQuantity;
                $orderGroupAmountMap[$nextOrderId] += $basket['price'] * $basket['quantity'];
                $basket['order'] = $nextOrderId;
                $nextOrderId += 1;
            }

            $result[] = $basket;
        }

        return $result;
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

    private function getNextBasketId()
    {
        return $this->nextBasketId
            ? ++$this->nextBasketId
            : $this->nextBasketId = max(array_column($this->baskets, 'id')) + 1;
    }

    private function setFreeBasketsOrderId(array $orderGroup, $orderId)
    {
        $result = array_column($this->baskets, null, 'id');
        foreach ($orderGroup as $basket) {
            $basket['order'] = $orderId;
            $result[$basket['id']] = $basket;
        }
        return $this->getSorted($result);
    }

    private function getSorted(array $baskets)
    {
        ksort($baskets);
        return array_values($baskets);
    }
}
