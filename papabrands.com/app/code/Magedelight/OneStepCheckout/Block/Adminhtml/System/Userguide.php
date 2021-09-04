<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ScheduleShipping
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\OneStepCheckout\Block\Adminhtml\System;

class Userguide extends \Magento\Config\Block\System\Config\Form\Fieldset
{

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->addChild('position_block', 'Magedelight\OneStepCheckout\Block\Adminhtml\Widget\System\Config\Userguide');

        return parent::_prepareLayout();
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->getChildHtml('position_block');
    }
}
