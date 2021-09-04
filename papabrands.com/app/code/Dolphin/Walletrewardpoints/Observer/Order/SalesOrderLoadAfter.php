<?php

namespace Dolphin\Walletrewardpoints\Observer\Order;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderLoadAfter implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->canUnhold()) {
            return $this;
        }
        if ($order->isCanceled() || $order->getState() === \Magento\Sales\Model\Order::STATE_CLOSED) {
            return $this;
        }
        if ($order->getBaseSubtotalInvoiced() - $order->getBaseSubtotalRefunded() > 0) {
            $order->setForcedCanCreditmemo(true);
        } else {
            $order->setForcedCanCreditmemo(false);
        }

        return $this;
    }
}
