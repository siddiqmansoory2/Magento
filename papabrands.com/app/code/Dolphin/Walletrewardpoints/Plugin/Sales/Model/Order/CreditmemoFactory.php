<?php

namespace Dolphin\Walletrewardpoints\Plugin\Sales\Model\Order;

use Magento\Sales\Model\Order\Creditmemo;

class CreditmemoFactory extends \Magento\Sales\Model\Order\CreditmemoFactory
{
    /**
     * Initialize creditmemo state based on requested parameters
     *
     * @param Creditmemo $creditmemo
     * @param array $data
     * @return void
     */
    protected function initData($creditmemo, $data)
    {
        if (isset($data['shipping_amount'])) {
            $creditmemo->setBaseShippingAmount((double) $data['shipping_amount']);
            $creditmemo->setBaseShippingInclTax((double) $data['shipping_amount']);
        }
        if (isset($data['adjustment_positive'])) {
            $creditmemo->setAdjustmentPositive($data['adjustment_positive']);
        }
        if (isset($data['adjustment_negative'])) {
            $creditmemo->setAdjustmentNegative($data['adjustment_negative']);
        }
        // Adding Credit Discount to creditmemo
        if (isset($data['credit_fee_amount'])) {
            $creditfee = abs($data['credit_fee_amount']);
            $creditmemo->setCreditBaseFeeAmount(-1.00 * $creditfee);
        }
    }
}
