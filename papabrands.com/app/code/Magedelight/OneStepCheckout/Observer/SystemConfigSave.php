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

namespace Magedelight\OneStepCheckout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magedelight\OneStepCheckout\Helper\Data;

/**
 * Class SystemConfigSave
 * @package Magedelight\OneStepCheckout\Observer
 */
class SystemConfigSave implements ObserverInterface
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\OneStepCheckout\Helper\Data
     */
    protected $helper;

    /**
     * SystemConfigSave constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Magedelight\OneStepCheckout\Helper\Data $helper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magedelight\OneStepCheckout\Helper\Data $helper
    ) {
        $this->storeManager = $storeManager;
        $this->resourceConfig = $resourceConfig;
        $this->helper = $helper;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $scopeId = 1;

        $this->resourceConfig->saveConfig(
            Data::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ORDER,
            $this->helper->getGiftMessageOrderLevel(),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId
        );

        $this->resourceConfig->saveConfig(
            Data::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS,
            $this->helper->getGiftMessageItemLevel(),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId
        );

        $this->resourceConfig->saveConfig(
            Data::XML_PATH_CHECKOUT_BILLING_ADDRESS,
            $this->helper->getBillingAddressBlock() == 'shipping' ? '1' : '0',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId
        );
        if ($this->helper->getAddressLine()) {
            $this->resourceConfig->saveConfig(
                Data::XML_PATH_CUSTOMER_ADDRESS_LINE,
                $this->helper->getAddressLine(),
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                $scopeId
            );
        }
    }
}
