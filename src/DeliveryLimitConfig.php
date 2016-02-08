<?php

namespace ReenExe\DeliveryLimitModel;

class DeliveryLimitConfig
{
    /**
     * @var int
     */
    private $min = 0;

    /**
     * @var int
     */
    private $max = PHP_INT_MAX;
    /**
     * @var bool
     */
    private $empty = true;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (isset($config['min'])) {
            $this->min = $config['min'];
            $this->empty = false;
        }

        if (isset($config['max'])) {
            $this->max = $config['max'];
            $this->empty = false;
        }
    }

    /**
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->empty;
    }

    /**
     * @param $amount
     * @return bool
     */
    public function inLimit($amount)
    {
        return $this->min <= $amount && $amount <= $this->max;
    }
}
