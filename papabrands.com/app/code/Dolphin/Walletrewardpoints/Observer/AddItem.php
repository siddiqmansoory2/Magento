<?php

namespace Dolphin\Walletrewardpoints\Observer;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class AddItem implements ObserverInterface
{
    public function __construct(
        ManagerInterface $messageManager,
        CheckoutSession $cart,
        Customer $customer,
        CatalogSession $catalogSession,
        DataHelper $dataHelper
    ) {
        $this->messageManager = $messageManager;
        $this->cart = $cart;
        $this->customer = $customer;
        $this->catalogSession = $catalogSession;
        $this->dataHelper = $dataHelper;
    }

    public function execute(Observer $observer)
    {
        $maxAllowCredit = $this->dataHelper->getCustomerMaxCredit();

        $max_credit_percentage_order = 10;
        if ($maxAllowCredit == '') {
            $maxAllowCredit = 0;
        }
        $customerId = $this->dataHelper->getCustomerIdFromSession();
        if ($customerId) {
            $customerData = $this->customer->load($customerId);
            $customerWalletCredit = $customerData->getWalletCredit();
            $applyCredit = $this->catalogSession->getApplyCredit();
            $quote = $this->cart->getQuote()->getAllItems();
            $carttotals = $this->cart->getQuote()->getTotals();
            $cartSubtotal = $carttotals['subtotal']->getValue();
            if ($cartSubtotal < abs($applyCredit)) {
                $this->catalogSession->setApplyCredit(0);
                $this->messageManager->addNotice(
                    __(
                        'Current credit(s) is greater than Cart Subtotal.'
                    )
                );
            } else {
                $allow_use_credit_per_order = $this->dataHelper->getAllowUseMaxCreditOrder();
                if ($allow_use_credit_per_order) {
                    $percentage_of_order_subtotal = $this->dataHelper->getPerceOfOrderSubtotal();
                    if ($percentage_of_order_subtotal) {
                        $maxOrderPercentage = $cartSubtotal * $percentage_of_order_subtotal / 100;
                        if ($maxOrderPercentage < abs($applyCredit)) {
                            $applyCredit = $maxOrderPercentage;
                        }
                    }
                }
                $applyCredit = -1.00 * abs($applyCredit);
                $this->catalogSession->setApplyCredit($applyCredit);
            }
        }
    }
}
