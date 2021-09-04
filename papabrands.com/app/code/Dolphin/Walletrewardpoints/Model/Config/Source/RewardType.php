<?php

namespace Dolphin\Walletrewardpoints\Model\Config\Source;

class RewardType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('Fixed')], ['value' => 1, 'label' => __('Percentage of Order Subtotal')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Fixed'), 1 => __('Percentage of Order Subtotal')];
    }
}
