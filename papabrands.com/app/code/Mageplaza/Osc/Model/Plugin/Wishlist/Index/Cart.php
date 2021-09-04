<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Osc\Model\Plugin\Wishlist\Index;

use Magento\Checkout\Model\Cart as ModelCart;
use Magento\Framework\UrlInterface;
use Mageplaza\Osc\Helper\Data;

/**
 * Class Cart
 * @package Mageplaza\Osc\Model\Plugin\Wishlist\Index
 */
class Cart
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var ModelCart
     */
    private $cart;

    /**
     * Cart constructor.
     *
     * @param Data $helper
     * @param UrlInterface $url
     * @param ModelCart $cart
     */
    public function __construct(Data $helper, UrlInterface $url, ModelCart $cart)
    {
        $this->helper = $helper;
        $this->url = $url;
        $this->cart = $cart;
    }

    /**
     * @param \Magento\Wishlist\Controller\Index\Cart $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterExecute(\Magento\Wishlist\Controller\Index\Cart $subject, $result)
    {
        if (!$this->helper->isRedirectToOneStepCheckout() || $this->cart->getQuote()->getHasError()) {
            return $result;
        }

        $redirectUrl = $this->url->getUrl($this->helper->getOscRoute());
        if ($subject->getRequest()->isAjax()) {
            $result->setData(['backUrl' => $redirectUrl]);
        } else {
            $result->setUrl($redirectUrl);
        }

        return $result;
    }
}
