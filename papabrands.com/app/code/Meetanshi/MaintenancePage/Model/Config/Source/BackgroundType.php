<?php

namespace Meetanshi\MaintenancePage\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class BackgroundType implements ArrayInterface
{
    const IMAGE = 'image';
    const VIDEO = 'video';

    public function toOptionArray()
    {
        return [
            ['value' => self::IMAGE, 'label' => __('Image')],
            ['value' => self::VIDEO, 'label' => __('Video')]
        ];
    }
}
