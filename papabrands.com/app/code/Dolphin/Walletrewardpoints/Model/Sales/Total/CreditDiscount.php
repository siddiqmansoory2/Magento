<?php

namespace Dolphin\Walletrewardpoints\Model\Sales\Total;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Quote\Model\QuoteValidator;
use Magento\Quote\Model\Quote\Address\Total as QuoteAddressTotal;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Store\Model\StoreManagerInterface;

class CreditDiscount extends AbstractTotal
{
    protected $quoteValidator = null;

    public function __construct(
        QuoteValidator $quoteValidator,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        CatalogSession $catalogSession,
        DataHelper $dataHelper
    ) {
        $this->quoteValidator = $quoteValidator;
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->catalogSession = $catalogSession;
        $this->dataHelper = $dataHelper;
    }

    public function collect(
        QuoteModel $quote,
        ShippingAssignmentInterface $shippingAssignment,
        QuoteAddressTotal $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $isEnable = $this->dataHelper->getIsEnableWalletExtension();
        $applyCredit = $this->catalogSession->getApplyCredit();
        $coupon_code = $quote->getCouponCode();
        $allowWithCoupon = $this->dataHelper->getUseCreditWithCoupon();
        $basebalance = 0;
        if (($coupon_code && $allowWithCoupon == 0) || $applyCredit == 0 || !$isEnable) {
            $creditBalance = 0;
            $this->catalogSession->setApplyCredit(0);
        } else {
            $creditBalance = $applyCredit;
            $basebalance = $this->convertPrice($creditBalance, 1);
        }
        if (abs($creditBalance) > 0) {
            if ($quote->getBillingAddress()->getData('address_type') == 'billing') {
                if ($total->getSubtotal()) {
                    $total->setTotalAmount('creditdiscount', $creditBalance);
                    $total->setBaseTotalAmount('creditdiscount', $basebalance);
                }
            }
        }
        $quote->setCreditFeeAmount($creditBalance);
        $quote->setCreditBaseFeeAmount($basebalance);

        return $this;
    }

    protected function clearValues(Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }

    public function fetch(QuoteModel $quote, QuoteAddressTotal $total)
    {
        if ($quote->getData('address_type') == 'shipping') {
            return $this;
        }
        $applyCredit = $this->catalogSession->getApplyCredit();
        $coupon_code = $quote->getCouponCode();
        $allowWithCoupon = $this->dataHelper->getUseCreditWithCoupon();

        if (!$applyCredit) {
            $applyCredit = 0;
        }
        if ($coupon_code && $allowWithCoupon == 0) {
            $applyCredit = 0;
            $this->catalogSession->setApplyCredit(0);
        }
        $description = (string) $total->getDiscountDescription() ?: '';
        return [
            'code' => 'creditdiscount',
            'title' => strlen($description) ? __('Discount (%1)', $description) : __('Discount'),
            'value' => $applyCredit,
        ];
    }

    public function convertPrice($amountValue, $chargetype)
    {
        $currentCurrency = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $baseCurrency = $this->storeManager->getStore()->getBaseCurrency()->getCode();
        if ($currentCurrency != $baseCurrency) {
            $rate = $chargetype == 0 ? $rate = $this->currencyFactory->create()->load($baseCurrency)
                ->getAnyRate($currentCurrency) : $rate = $this->currencyFactory
                ->create()->load($currentCurrency)->getAnyRate($baseCurrency);
            $amountValue = (float) $amountValue * $rate;
        }
        return $amountValue;
    }
}
