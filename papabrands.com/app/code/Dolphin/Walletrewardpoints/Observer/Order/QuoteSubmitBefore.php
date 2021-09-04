<?php

namespace Dolphin\Walletrewardpoints\Observer\Order;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class QuoteSubmitBefore implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $quote = $observer->getQuote();
        $order = $observer->getOrder();
        $order->setCreditFeeAmount($quote->getCreditFeeAmount());
        $order->setCreditBaseFeeAmount($quote->getCreditBaseFeeAmount());
    }
}
