<?php

namespace Dolphin\Walletrewardpoints\Model;

use Magento\Framework\UrlInterface;

class SystemConfigComment implements \Magento\Config\Model\Config\CommentInterface
{
    protected $urlInterface;

    public function __construct(
        UrlInterface $urlInterface
    ) {
        $this->urlInterface = $urlInterface;
    }

    public function getCommentText($elementValue)
    {
        $url = $this->urlInterface->getUrl('adminhtml/system_config/edit/section/payment');

        return 'If set \'No\', then allow unlimited credit(s). And require to enable <a href="' .
            $url . '#row_payment_us_free" target="_blank">Zero Subtotal Checkout</a>
            payment method for Zero Subtotal order.';
    }
}
