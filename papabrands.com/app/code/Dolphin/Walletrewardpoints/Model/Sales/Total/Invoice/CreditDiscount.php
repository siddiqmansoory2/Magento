<?php

namespace Dolphin\Walletrewardpoints\Model\Sales\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class CreditDiscount extends AbstractTotal
{
    public function collect(Invoice $invoice)
    {
        $invoice->setCreditFeeAmount(0);
        $invoice->setCreditBaseFeeAmount(0);
        $creditFeeAmount = $invoice->getOrder()->getCreditFeeAmount();
        $baseCreditFeeAmount = $invoice->getOrder()->getCreditBaseFeeAmount();

        if ($creditFeeAmount != 0) {
            foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
                if ((double) $previousInvoice->getCreditFeeAmount() && !$previousInvoice->isCanceled()) {
                    return $this;
                }
            }
            $invoice->setCreditFeeAmount($creditFeeAmount);
            $invoice->setCreditBaseFeeAmount($baseCreditFeeAmount);

            $invoice->setGrandTotal($invoice->getGrandTotal() + $creditFeeAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseCreditFeeAmount);
        }
        return $this;
    }
}
