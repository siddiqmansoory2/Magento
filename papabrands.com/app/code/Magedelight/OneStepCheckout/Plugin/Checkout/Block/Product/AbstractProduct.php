<?php

namespace Magedelight\OneStepCheckout\Plugin\Checkout\Block\Product;

class AbstractProduct
{

    /**
     * @var \Magedelight\OneStepCheckout\Helper\Data
     */
    private $oscHelper;

    /**
     * @param \Magedelight\OneStepCheckout\Helper\Data $oscHelper
     */
    public function __construct(
        \Magedelight\OneStepCheckout\Helper\Data $oscHelper
    ) {
        $this->oscHelper = $oscHelper;
    }

    public function isRedirectToCartEnabled()
    {
        return $this->_scopeConfig->getValue(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function afterisRedirectToCartEnabled(\Magento\Catalog\Block\Product\AbstractProduct $subject, $result)
    {
        if ($this->oscHelper->allowRedirectCheckoutAfterProductAddToCart()) {
            return true;
        } else {
            return $result;
        }
    }
}
