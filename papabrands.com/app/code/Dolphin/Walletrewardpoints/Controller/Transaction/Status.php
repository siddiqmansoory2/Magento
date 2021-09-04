<?php

namespace Dolphin\Walletrewardpoints\Controller\Transaction;

use Dolphin\Walletrewardpoints\Helper\Data;
use Dolphin\Walletrewardpoints\Model\Subscriber;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Status extends \Magento\Framework\App\Action\Action
{
    protected $customerRepository;
    protected $subscriber;

    /**
     * [__construct Initialize dependencies.]
     * @param Context     $context           [description]
     * @param Subscriber  $subscriber        [description]
     * @param JsonFactory $resultJsonFactory [description]
     * @param Data        $helperData        [description]
     */
    public function __construct(
        Context $context,
        Subscriber $subscriber,
        JsonFactory $resultJsonFactory,
        Data $helperData
    ) {
        $this->subscriber = $subscriber;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * [execute Get transaction subscriber status]
     * @return [type] [description]
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $customerId = $this->helperData->getCustomerIdFromSession();
        $data['status'] = false;
        if ($customerId) {
            $isSubscribedCustomer = $this->subscriber->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->getFirstItem();
            if ($isSubscribedCustomer->getData()) {
                if ($isSubscribedCustomer->getSubscriberStatus() == 1) {
                    $data['status'] = true;
                }
            }
        }
        return $resultJson->setData($data);
    }
}
