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

class Agreements
{
    public function toOptionArray()
    {
        $responseArray = [
            [
                'label' => 'Below Payment Method',
                'value' => 'payment'
            ],
            [
                'label' => 'Before Place Order Button',
                'value' => 'sidebar'
            ]
        ];
        return $responseArray;
    }
}
