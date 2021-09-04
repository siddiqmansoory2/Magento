<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


namespace Amasty\MegaMenuLite\Block\Adminhtml\Builder\Store;

class Switcher extends \Magento\Backend\Block\Store\Switcher
{
    /**
     * @var bool
     */
    protected $_hasDefaultOption = false;

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        $storeId = parent::getStoreId();
        if (!$storeId) {
            $storeId = $this->_storeManager->getDefaultStoreView()->getId();
        }

        return $storeId;
    }
}
