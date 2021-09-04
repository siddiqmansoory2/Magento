<?php

namespace Dolphin\Walletrewardpoints\Model\Sales\Total\Pdf;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;

class CreditDiscount extends DefaultTotal
{
    public function getTotalsForDisplay()
    {
        parent::getTotalsForDisplay();
        $creditDiscount = $this->getSource()->getCreditFeeAmount();
        $total = [];
        if ($creditDiscount != 0) {
            $charge = $this->getOrder()->formatPriceTxt($creditDiscount);
            $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
            $total[] = [
                'amount' => $charge,
                'label' => 'Wallet Credit Discount',
                'font_size' => $fontSize,
            ];
        }

        return $total;
    }
}
