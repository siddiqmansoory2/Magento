<?php

namespace Dolphin\Walletrewardpoints\Observer\Coupon;

use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class ApplyCouponCode implements ObserverInterface
{
    /**
     * [__construct Initialise Dependencies]
     * @param ManagerInterface $messageManager  [description]
     * @param CatalogSession   $catalogSession  [description]
     * @param CheckoutSession  $checkoutSession [description]
     */
    public function __construct(
        ManagerInterface $messageManager,
        CatalogSession $catalogSession,
        CheckoutSession $checkoutSession
    ) {
        $this->messageManager = $messageManager;
        $this->catalogSession = $catalogSession;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
        $orderGrandTotal = $this->checkoutSession->getQuote()->getGrandTotal();
        $applyCredit = $this->catalogSession->getApplyCredit();
        if ($orderGrandTotal < 0) {
            $applyCredit = -1.00 * (abs($applyCredit) - abs($orderGrandTotal));
            $this->catalogSession->setApplyCredit($applyCredit);
            $this->checkoutSession->getQuote()->setCreditFeeAmount($applyCredit);
            $this->messageManager->addNotice(
                __(
                    'Applied credit is greater than Grand Total.'
                )
            );
        }
    }
}
