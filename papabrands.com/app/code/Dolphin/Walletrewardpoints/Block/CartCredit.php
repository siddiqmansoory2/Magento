<?php

namespace Dolphin\Walletrewardpoints\Block;

use Dolphin\Walletrewardpoints\Model\AdditionalConfigProvider;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\View\Element\Template\Context;

class CartCredit extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        Context $context,
        CatalogSession $catalogSession,
        AdditionalConfigProvider $additionalConfigProvider
    ) {
        $this->catalogSession = $catalogSession;
        $this->additionalConfigProvider = $additionalConfigProvider;
        parent::__construct($context);
    }

    public function getCreditDiscount()
    {
        return abs($this->catalogSession->getApplyCredit());
    }

    public function getConfigValue()
    {
        return $this->additionalConfigProvider->getConfig();
    }
}
