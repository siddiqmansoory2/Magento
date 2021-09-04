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

class OrderSaveAfter implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    
    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var \Magedelight\OneStepCheckout\Model\DeliveryDate
     */
    protected $deliveryModel;

    /**
     * @var \Magedelight\OneStepCheckout\Helper\Data
     */
    protected $oscHelper;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param array $data
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magedelight\OneStepCheckout\Helper\Data $oscHelper,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->subscriberFactory = $subscriberFactory;
        $this->oscHelper = $oscHelper;
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
        $newsletter = $this->checkoutSession->getData('onestepcheckout_newsletter', true);
        if ($newsletter) {
            $this->saveSubscriber($order);
        }
    }
    
    private function getSubscriberEmail($order)
    {
        if ($order->getShippingAddress()) {
            return $order->getShippingAddress()->getEmail();
        } elseif ($order->getBillingAddress()) {
            return $order->getBillingAddress()->getEmail();
        }
        return false;
    }
    
    private function saveSubscriber($order)
    {
        if ($email = $this->getSubscriberEmail($order)) {
            $subscriberModel = $this->subscriberFactory->create()->loadByEmail($email);
            if (!$subscriberModel->getId()) {
                try {
                    $this->subscriberFactory->create()->subscribe($email);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->logger->notice($e->getMessage());
                } catch (\Exception $e) {
                    $this->logger->notice($e->getMessage());
                }

            } elseif ($subscriberModel->getData('subscriber_status') != 1) {
                $subscriberModel->setData('subscriber_status', 1);
                try {
                    $subscriberModel->save();
                } catch (\Exception $e) {
                    $this->logger->notice($e->getMessage());
                }
            }
        }
    }
}
