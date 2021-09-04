<?php

namespace Dolphin\Walletrewardpoints\Model\Sales\Total\Creditmemo;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class CreditDiscount extends AbstractTotal
{
    protected $dataHelper;

    /**
     * [__construct Initialise Dependencies]
     * @param DataHelper             $dataHelper    [description]
     * @param PriceCurrencyInterface $priceCurrency [description]
     */
    public function __construct(
        DataHelper $dataHelper,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->dataHelper = $dataHelper;
        $this->priceCurrency = $priceCurrency;
    }

    public function collect(Creditmemo $creditmemo)
    {
        $creditmemo->setAllowZeroGrandTotal(true);
        $grandTotal = abs($creditmemo->getGrandTotal());
        $baseGrandTotal = abs($creditmemo->getBaseGrandTotal());
        if ($baseGrandTotal != 0) {
            $order = $creditmemo->getOrder();
            $creditFeeAmount = abs($order->getCreditFeeAmount());
            $baseCreditFeeAmount = abs($order->getCreditBaseFeeAmount());
            $allowedAmount = $creditFeeAmount - abs($order->getCreditFeeAmountRefunded());
            $baseAllowedAmount = $baseCreditFeeAmount - abs($order->getCreditBaseFeeAmountRefunded());
            if ($baseAllowedAmount < 0) {
                $allowedAmount = 0;
                $baseAllowedAmount = 0;
            }
            $isAllowRefund = (bool) $this->dataHelper->getAdminAllowRefundCredit($order->getStoreId());
            if ($creditmemo->hasCreditBaseFeeAmount() && $isAllowRefund) {
                $desiredAmount = $this->priceCurrency->round(abs($creditmemo->getCreditBaseFeeAmount()));
                $baseShippingAmount = $order->getBaseShippingAmount();
                $minAllowedAmount = $baseAllowedAmount - $baseShippingAmount;
                $originalTotalAmount = $baseGrandTotal;
                if ($baseGrandTotal - $desiredAmount <= 0 || $baseGrandTotal < $minAllowedAmount) {
                    $minAllowedAmount = 0;
                }
                if ($this->priceCurrency->round($minAllowedAmount) - 0.01 < $desiredAmount) {
                    $ratio = 0;
                    if ($originalTotalAmount > 0) {
                        $ratio = $desiredAmount / $originalTotalAmount;
                    }
                    $allowedAmount = $grandTotal;
                    $baseAllowedAmount = $baseGrandTotal;
                    $creditDiscountAmount = $this->priceCurrency->round($allowedAmount * $ratio);
                    $baseCreditDiscountAmount = $this->priceCurrency->round($baseAllowedAmount * $ratio);
                } else {
                    $minAllowedAmount = $order->getBaseCurrency()->format(
                        $minAllowedAmount,
                        null,
                        false
                    );
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __(
                            'Minimum Wallet Credit Discount amount allowed to refund is: %1',
                            $minAllowedAmount
                        )
                    );
                }
            } else {
                $creditDiscountAmount = $allowedAmount;
                $baseCreditDiscountAmount = $baseAllowedAmount;
            }
            $creditDiscountAmount = -1.00 * abs($creditDiscountAmount);
            $baseCreditDiscountAmount = -1.00 * abs($baseCreditDiscountAmount);

            $creditmemo->setCreditFeeAmount($creditDiscountAmount);
            $creditmemo->setCreditBaseFeeAmount($baseCreditDiscountAmount);

            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditDiscountAmount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseCreditDiscountAmount);
        } else {
            return $this;
        }
    }
}
