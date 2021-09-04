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
namespace Magedelight\OneStepCheckout\Controller\Order;

class SaveAdditionalInfo extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magedelight\OneStepCheckout\Helper\Data
     */
    protected $oscHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magedelight\OneStepCheckout\Helper\Data $oscHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->jsonHelper = $jsonHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->checkoutSession = $checkoutSession;
        $this->oscHelper = $oscHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function execute()
    {
        $additionalData = $this->dataObjectFactory->create([
            'data' => $this->jsonHelper->jsonDecode($this->getRequest()->getContent()),
        ]);
        $quote = $this->checkoutSession->getQuote();

        if (!$quote->isVirtual()) {
            if ($quote->getShippingAddress()->getShippingMethod() != 'storepickup_storepickup') {
                if ($this->oscHelper->isDeliveryDateEnabled()) {
                    if ($this->oscHelper->isDeliveryDateRequired() && $additionalData->getData('md_osc_delivery_date') == '0000-00-00') {
                        throw new \Exception(__('Invalid Delivery Date'));
                    }
                }
            }
        }
        $this->checkoutSession->setData(
            'onestepcheckout_newsletter',
            $additionalData->getData('onestepcheckout_newsletter')
        );
        $this->checkoutSession->setData(
            'onestepcheckout_order_comments',
            $additionalData->getData('onestepcheckout_order_comments')
        );
        $this->checkoutSession->setData(
            'md_osc_delivery_date',
            $additionalData->getData('md_osc_delivery_date')
        );
        $this->checkoutSession->setData(
            'md_osc_delivery_time',
            $additionalData->getData('md_osc_delivery_time')
        );
        $this->checkoutSession->setData(
            'md_osc_delivery_comment',
            $additionalData->getData('md_osc_delivery_comment')
        );
    }
}
