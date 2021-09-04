<?php

namespace Dolphin\Walletrewardpoints\ViewModel;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ConfigData implements ArgumentInterface
{
    public function __construct(
        DataHelper $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    public function getIsLoggedIn()
    {
        return $this->dataHelper->getIsLoggedIn();
    }

    public function getMinOrderTotal()
    {
        return $this->dataHelper->getMinOrderTotal();
    }

    public function getCreatingOrderRewardPoint()
    {
        return $this->dataHelper->getCreatingOrderRewardPoint();
    }

    public function getEnableCreatingOrder()
    {
        return $this->dataHelper->getEnableCreatingOrder();
    }

    public function getMinOrderedQty()
    {
        return $this->dataHelper->getMinOrderedQty();
    }

    public function getCurrencySymbol()
    {
        return $this->dataHelper->getCurrencySymbol();
    }

    public function getCreatingOrderEarnType()
    {
        return $this->dataHelper->getCreatingOrderEarnType();
    }

    public function getDisplayRewardPointOnProduct()
    {
        return $this->dataHelper->getDisplayRewardPointOnProduct();
    }

    public function getEnableReward()
    {
        return $this->dataHelper->getEnableReward();
    }

    public function checkForLogin()
    {
        return $this->dataHelper->checkForLogin();
    }

    public function getCustomerIdFromSession()
    {
        return $this->dataHelper->getCustomerIdFromSession();
    }

    public function getWalletCredit($customer_id)
    {
        return $this->dataHelper->getWalletCredit($customer_id);
    }

    public function getHelperModel()
    {
        return $this->dataHelper;
    }
}
