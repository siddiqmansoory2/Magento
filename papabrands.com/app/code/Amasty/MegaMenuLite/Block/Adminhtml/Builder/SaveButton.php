<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


namespace Amasty\MegaMenuLite\Block\Adminhtml\Builder;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label'    => __('Save'),
            'class'    => 'save primary',
            'on_click' => '',
        ];
    }
}
