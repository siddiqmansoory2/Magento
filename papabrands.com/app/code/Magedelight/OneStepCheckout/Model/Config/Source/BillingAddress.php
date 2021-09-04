<?php

namespace Magedelight\OneStepCheckout\Model\Config\Source;

class BillingAddress implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $layout = [
            [
                'label' => 'Display in payment block',
                'value' => 'payment'
            ],
            [
                'label' => 'Display after shipping address',
                'value' => 'shipping'
            ],
        ];
        return $layout;
    }
}
