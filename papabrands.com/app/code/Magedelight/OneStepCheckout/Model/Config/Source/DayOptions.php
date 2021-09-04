<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OneStepCheckout
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\OneStepCheckout\Model\Config\Source;

class DayOptions
{
    public function toOptionArray()
    {
        $days = [
            [
                'value' => 0,
                'label' => 'Sunday'
            ],
            [
                'value' => 1,
                'label' => 'Monday'
            ],
            [
                'value' => 2,
                'label' => 'Tuesday'
            ],
            [
                'value' => 3,
                'label' => 'Wednesday'
            ],
            [
                'value' => 4,
                'label' => 'Thursday'
            ],
            [
                'value' => 5,
                'label' => 'Friday'
            ],
            [
                'value' => 6,
                'label' => 'Saturday'
            ],
        ];
        return $days;
    }

    public function getDateLabelByValue($value)
    {
        $time = $this->toOptionArray();
        foreach ($time as $key => $val) {
            if ($val['value'] == $value) {
                return $val['label'];
            }
        }
    }
}
