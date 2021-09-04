<?php

namespace Dolphin\Walletrewardpoints\Controller\Transaction;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Dolphin\Walletrewardpoints\Helper\Transaction as TransactionHelper;
use Dolphin\Walletrewardpoints\Model\Subscriber;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Save extends \Magento\Framework\App\Action\Action
{
    protected $subscriber;
    protected $resultJsonFactory;

    /**
     * [__construct Initialize dependencies]
     * @param Context     $context           [description]
     * @param Subscriber  $subscriber        [description]
     * @param JsonFactory $resultJsonFactory [description]
     * @param DataHelper        $dataHelper        [description]
     */
    public function __construct(
        Context $context,
        Subscriber $subscriber,
        JsonFactory $resultJsonFactory,
        DataHelper $dataHelper,
        TransactionHelper $transactionHelper
    ) {
        $this->subscriber = $subscriber;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $dataHelper;
        $this->transactionHelper = $transactionHelper;
        parent::__construct($context);
    }

    /**
     * [execute Save transaction subscription]
     * @return [type] [description]
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $data['result'] = "fail";
        $customerId = $this->dataHelper->getCustomerIdFromSession();
        if ($customerId === null) {
            $this->messageManager->addError(__('Something went wrong while saving your subscription.'));
        } else {
            try {
                $isSubscribedCustomer = $this->subscriber->getCollection()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->getFirstItem();
                $isSubscribedParam = (Boolean) $this->getRequest()->getPost('is_subscribed');
                if (!$isSubscribedParam) {
                    $isSubscribedParam = 0;
                }
                if (!$isSubscribedCustomer->getData() ||
                    ($isSubscribedParam != $isSubscribedCustomer->getSubscriberStatus())) {
                    $date = date("Y-m-d H:i:s");
                    $customerEmail = $this->dataHelper->getCustomerEmailFromSession();
                    $subscribeData = [
                        'customer_id' => $customerId,
                        'subscriber_email' => $customerEmail,
                        'subscriber_status' => $isSubscribedParam,
                        'subscribe_date' => $date,
                    ];
                    if ($isSubscribedCustomer->getData()) {
                        $subscriber_id = $isSubscribedCustomer->getSubscriberId();
                        $subscribeData['subscriber_id'] = $subscriber_id;
                        unset($subscribeData['subscribe_date']);
                    }
                    $this->subscriber->transactionSubscribeSave($subscribeData);
                    if ($isSubscribedParam) {
                        $this->messageManager->addSuccess(__('We have saved your subscription.'));
                        // Send subscription email to customer
                        $this->transactionHelper->transactionSubscription();
                    } else {
                        $this->messageManager->addSuccess(__('We have removed your transaction subscription.'));
                        // Send unsubscription email to customer
                        $this->transactionHelper->transactionUnsubscription();
                    }
                } else {
                    $this->messageManager->addSuccess(__('We have updated your subscription.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while saving your subscription.'));
            }
        }
        $data['result'] = "success";

        return $resultJson->setData($data);
    }
}
