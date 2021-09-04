<?php

namespace Magedelight\OneStepCheckout\Model\Config\Source;

class PlaceOrder implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $layout = [
            [
                'label' => 'Display below payment method',
                'value' => 'payment'
            ],
            [
                'label' => 'Display in order review section',
                'value' => 'global'
            ],
        ];
        return $layout;
    }
}
