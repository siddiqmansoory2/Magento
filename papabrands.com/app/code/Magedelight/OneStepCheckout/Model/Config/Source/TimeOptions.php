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

/**
 * Class TimeOptions
 * @package Magedelight\OneStepCheckout\Model\Config\Source
 */
class TimeOptions
{
    public function toOptionArray()
    {
        $time = [];
        $hoursArray = range(1, 24);
        foreach ($hoursArray as $hour) {
            $AmPm = $hour >= 12 && $hour <= 23 ? 'PM' : 'AM';
            $label = str_pad($hour, 2, '0', STR_PAD_LEFT).':00 '.$AmPm;
            $time[] = [
                'value' => $hour,
                'label' => $label
            ];
        }
        return $time;
    }

    public function getTimeLabelByValue($value)
    {
        $time = $this->toOptionArray();
        foreach ($time as $key => $val) {
            if ($val['value'] == $value) {
                return $val['label'];
            }
        }
    }
}
