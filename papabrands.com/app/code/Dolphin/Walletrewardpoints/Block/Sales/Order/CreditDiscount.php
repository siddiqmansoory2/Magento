<?php

namespace Dolphin\Walletrewardpoints\Block\Sales\Order;

use Magento\Framework\View\Element\Template;

class CreditDiscount extends Template
{
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $source = $parent->getSource();

        if ($source->getCreditFeeAmount() == 0) {
            return $this;
        }

        $creditFeeAmount = $source->getCreditFeeAmount();
        $baseCreditFeeAmount = $source->getCreditBaseFeeAmount();

        $creditDiscountTotal = [
            'code' => 'wallet_credit_discount',
            'strong' => false,
            'value' => $creditFeeAmount,
            'base_value' => $baseCreditFeeAmount,
            'label' => 'Wallet Credit Discount',
        ];

        $parent->addTotal(
            new \Magento\Framework\DataObject($creditDiscountTotal),
            'wallet_credit_discount'
        );

        return $this;
    }
}
