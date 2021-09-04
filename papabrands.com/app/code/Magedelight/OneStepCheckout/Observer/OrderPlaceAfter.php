<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OneStepCheckout
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\OneStepCheckout\Observer;

class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;



    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $comment = $this->_checkoutSession->getData('onestepcheckout_order_comments', true);
        $deliveryDate = $this->_checkoutSession->getData('md_osc_delivery_date', true);
        $deliveryTime = $this->_checkoutSession->getData('md_osc_delivery_time', true);
        $deliveryComment = $this->_checkoutSession->getData('md_osc_delivery_comment', true);

        if ($comment) {
            $order->addStatusHistoryComment($comment);
        }
        if($deliveryDate) {
            $order->setData('md_osc_delivery_date', $deliveryDate);
        }
        if($deliveryTime) {
            $order->setData('md_osc_delivery_time', $deliveryTime);
        }
        if($deliveryComment) {
            $order->setData('md_osc_delivery_comment', $deliveryComment);
        }
    }
}
