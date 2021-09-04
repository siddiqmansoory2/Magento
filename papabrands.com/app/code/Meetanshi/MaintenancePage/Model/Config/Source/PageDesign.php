<?php

namespace Meetanshi\MaintenancePage\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PageDesign implements ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Template One')],
            ['value' => 2, 'label' => __('Template Two')],
            ['value' => 3, 'label' => __('Template Three')],
            ['value' => 4, 'label' => __('Custom Design')]
        ];
    }
}