<?php

namespace Meetanshi\MaintenancePage\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PageLayout implements ArrayInterface
{
    const ONE_COLUMN_TEMPLATE = 'Meetanshi_MaintenancePage::1column.phtml';
    const TWO_COLUMN_TEMPLATE = 'Meetanshi_MaintenancePage::2columns.phtml';
    const ONE_COLUMN = ['identifier' => 'maintenancepage_1column', 'template' => self::ONE_COLUMN_TEMPLATE];
    const TWO_COLUMN = ['identifier' => 'maintenancepage_2columns', 'template' => self::TWO_COLUMN_TEMPLATE];

    public function toOptionArray()
    {
        return [
            ['value' => self::ONE_COLUMN['identifier'], 'label' => __('1 Column')],
            ['value' => self::TWO_COLUMN['identifier'], 'label' => __('2 Columns')]
        ];
    }
}
