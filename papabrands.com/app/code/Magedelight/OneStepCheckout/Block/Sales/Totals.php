<?php

namespace Magedelight\OneStepCheckout\Block\Sales;

use Magento\Directory\Model\Currency;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magedelight\OneStepCheckout\Helper\Data;

/**
 * Class Totals
 * @package Magedelight\OneStepCheckout\Block\Sales
 */
class Totals extends Template
{

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @param Context $context
     * @param Data $helper
     * @param Currency $currency
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        Currency $currency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->currency = $currency;
    }

    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    public function getCurrencySymbol()
    {
        return $this->currency->getCurrencySymbol();
    }

    public function initTotals()
    {
        $this->getParentBlock();
        $this->getOrder();
        $this->getSource();
        if ($this->getSource()->getMdoscExtraFee() <= 0) {
            return $this;
        }
        $total = new DataObject(
            [
                'code' => 'mdosc_extra_fee',
                'value' => $this->getSource()->getMdoscExtraFee(),
                'label' => $this->helper->getExtraFeeLabel(),
            ]
        );
        $this->getParentBlock()->addTotalBefore($total, 'grand_total');

        return $this;
    }
}
