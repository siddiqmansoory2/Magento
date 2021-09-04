<?php

namespace Magedelight\OneStepCheckout\Model\Config\Source;

class Layout implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $layout = [
            [
                'label' => '2 Column',
                'value' => '2column'
            ],
            [
                'label' => '3 Column',
                'value' => '3column'
            ],
        ];
        return $layout;
    }
}
