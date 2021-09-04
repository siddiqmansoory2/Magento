<?php

namespace Dolphin\Walletrewardpoints\Observer\Refund;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CreditmemoAllowZero implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $creditMemo = $observer->getEvent()->getCreditmemo();
        $creditMemo->setAllowZeroGrandTotal(true);
    }
}
