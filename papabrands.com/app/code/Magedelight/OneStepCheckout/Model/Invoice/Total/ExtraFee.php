<?php

namespace Magedelight\OneStepCheckout\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

/**
 * Class ExtraFee
 * @package Magedelight\OneStepCheckout\Model\Invoice\Total
 */
class ExtraFee extends AbstractTotal
{

    public function collect(
        Invoice $invoice
    ) {
        $order = $invoice->getOrder();
        $invoice->setMdoscExtraFee(0);
        $invoice->setBaseMdoscExtraFee(0);
        $amount = $order->getMdoscExtraFee();
        $invoice->setMdoscExtraFee($amount);
        $amount = $order->getBaseMdoscExtraFee();
        $invoice->setBaseMdoscExtraFee($amount);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getMdoscExtraFee());
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getMdoscExtraFee());
        return $this;
    }
}
