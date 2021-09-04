<?php

namespace Dolphin\Walletrewardpoints\Model;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class AdditionalConfigProvider extends AbstractModel
{
    public function __construct(
        UrlInterface $urlInterface,
        DataHelper $dataHelper,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        CheckoutSession $checkoutSession
    ) {
        $this->urlInterface = $urlInterface;
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->checkoutSession = $checkoutSession;
    }

    public function getConfig()
    {
        $customer_id = $this->dataHelper->getCustomerIdFromSession();
        $currencySymbol = $this->dataHelper->getCurrencySymbol();
        $customerWalletCredit = $this->dataHelper->getWalletCredit($customer_id);
        $output = [];
        $output['is_enable'] = $this->dataHelper->getIsEnableWalletExtension();
        $output['currencySymbol'] = $currencySymbol;
        $output['customerwalletcredit'] = $customerWalletCredit;
        $output['isLoggedIn'] = $this->dataHelper->getIsLoggedIn();
        $allow_use_credit_per_order = $this->dataHelper->getAllowUseMaxCreditOrder();
        if ($allow_use_credit_per_order) {
            $maximum_allow_credit = $max_credit_per_order = $this->dataHelper->getMaxAllowCreditOrder();
            $percentage_of_order_subtotal = $this->dataHelper->getPerceOfOrderSubtotal();
            if ($percentage_of_order_subtotal) {
                $subTotal = $this->checkoutSession->getQuote()->getSubtotal();
                $maxOrderPercentage = $subTotal * $percentage_of_order_subtotal / 100;
                if ($maxOrderPercentage > $max_credit_per_order) {
                    $maximum_allow_credit = $maxOrderPercentage;
                }
            }
            if (!$maximum_allow_credit || $maximum_allow_credit > $customerWalletCredit) {
                $maximum_allow_credit = $customerWalletCredit;
            }
        } else {
            $maximum_allow_credit = $customerWalletCredit;
        }
        $output['maximum_allow_credit'] = $currencySymbol . $maximum_allow_credit;
        $output['max_allow_credit'] = $maximum_allow_credit;
        $output['applyCreditUrl'] = $this->urlInterface->getUrl('walletrewardpoints/customer/discount');
        $output['allow_with_coupon'] = $this->dataHelper->getUseCreditWithCoupon();

        return $output;
    }
}
