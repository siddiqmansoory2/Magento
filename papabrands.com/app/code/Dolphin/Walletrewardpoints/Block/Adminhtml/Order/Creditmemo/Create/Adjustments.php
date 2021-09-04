<?php

namespace Dolphin\Walletrewardpoints\Block\Adminhtml\Order\Creditmemo\Create;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Model\Config;

class Adjustments extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Adjustments
{
    public function __construct(
        Context $context,
        Config $taxConfig,
        PriceCurrencyInterface $priceCurrency,
        DataHelper $dataHelper,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $taxConfig, $priceCurrency, $data);
    }

    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_source = $parent->getSource();
        if ($this->dataHelper->getAdminAllowRefundCredit($this->_source->getStoreId())) {
            $total = new \Magento\Framework\DataObject(
                [
                    'code' => 'wallet_credit_discount',
                    'block_name' => $this->getNameInLayout(),
                ]
            );
        } else {
            $creditFeeAmount = $this->_source->getCreditFeeAmount();
            $baseCreditFeeAmount = $this->_source->getCreditBaseFeeAmount();
            $total = new \Magento\Framework\DataObject(
                [
                    'code' => 'wallet_credit_discount',
                    'strong' => false,
                    'value' => $creditFeeAmount,
                    'base_value' => $baseCreditFeeAmount,
                    'label' => 'Wallet Credit Discount',
                ]
            );
        }
        $parent->removeTotal('wallet_credit_discount');
        $parent->addTotal($total);

        return $this;
    }

    public function getCreditBaseFeeAmount()
    {
        $source = $this->getSource();
        $creditDiscount = abs($source->getCreditBaseFeeAmount());

        return $this->priceCurrency->round($creditDiscount) * 1;
    }

    public function getCreditFeeLabel()
    {
        return __('Refund Wallet Credit Discount');
    }
}
