<?php

namespace Dolphin\Walletrewardpoints\Plugin\Sales\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\RefundOperation as CreditmemoRefundOperation;

class RefundOperation
{
    public function beforeExecute(
        CreditmemoRefundOperation $subject,
        CreditmemoInterface $creditmemo,
        OrderInterface $order,
        $online = false
    ) {
        if ($creditmemo->getState() == Creditmemo::STATE_REFUNDED
            && $creditmemo->getOrderId() == $order->getEntityId()
        ) {
            $currentAmount = abs($order->getCreditFeeAmountRefunded()) + abs($creditmemo->getCreditFeeAmount());
            $baseAmount = abs($order->getCreditBaseFeeAmountRefunded()) + abs($creditmemo->getCreditBaseFeeAmount());
            $currentAmount = -1.00 * $currentAmount;
            $baseAmount = -1.00 * $baseAmount;
            $order->setCreditFeeAmountRefunded($currentAmount);
            $order->setCreditBaseFeeAmountRefunded($baseAmount);
        }

        return [$creditmemo, $order, $online];
    }
}
