<?php

namespace Dolphin\Walletrewardpoints\Observer\Paypal;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PaymentCreditDiscountPaypal implements ObserverInterface
{
    public $checkoutSession;

    /**
     * [__construct Initialise Dependencies]
     * @param Session $checkoutSession [description]
     */
    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
        $cart = $observer->getEvent()->getCart();
        $quote = $this->checkoutSession->getQuote();
        $creditDiscount = $quote->getCreditBaseFeeAmount();
        if ($creditDiscount != 0 && $creditDiscount != null && $creditDiscount != '') {
            $creditDiscount = -1.00 * abs($creditDiscount);
            $cart->addCustomItem('Wallet Credit Discount', 1, $creditDiscount);
        }

        return $this;
    }
}
