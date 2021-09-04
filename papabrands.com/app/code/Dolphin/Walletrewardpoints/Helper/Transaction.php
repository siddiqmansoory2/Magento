<?php

namespace Dolphin\Walletrewardpoints\Helper;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Dolphin\Walletrewardpoints\Model\Subscriber;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;

class Transaction extends AbstractHelper
{
    /**
     * [__construct Initialize dependencies]
     * @param Context               $context           [description]
     * @param DataHelper            $dataHelper        [description]
     * @param Subscriber            $subscriber        [description]
     * @param StateInterface        $inlineTranslation [description]
     * @param TransportBuilder      $transportBuilder  [description]
     * @param StoreManagerInterface $storeManager      [description]
     */
    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        Subscriber $subscriber,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager
    ) {
        $this->dataHelper = $dataHelper;
        $this->customerEmail = $dataHelper->getCustomerEmailFromSession();
        $this->customerName = $dataHelper->getCustomerNameFromSession();
        $this->subscriber = $subscriber;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * [checkTemplateSubscription Check for customer transaction subscription]
     * @param  [int] $customerId [Customer id]
     * @return [boolean]             [0/1]
     */
    public function checkTemplateSubscription($customerId)
    {
        $subscriberFlag = 1;
        $subscriber = $this->subscriber->getCollection()
            ->addFieldToFilter('customer_id', $customerId)->getFirstItem();
        if ($subscriber->getData()) {
            if ($subscriber->getSubscriberStatus() == 0) {
                $subscriberFlag = 0;
            }
        }

        return $subscriberFlag;
    }

    /**
     * [transactionSubscriberSave Transaction subscribe save]
     * @param  [int] $customerId    [customer id]
     * @param  [varchar] $customerName    [Customer Name]
     * @param  [varchar] $customerEmail [customer email]
     */
    public function transactionSubscriberSave($customerId, $customerName, $customerEmail)
    {
        $this->customerEmail = $customerEmail;
        $this->customerName = $customerName;
        $date = date("Y-m-d H:i:s");
        $subscribeData = [
            'customer_id' => $customerId,
            'subscriber_email' => $customerEmail,
            'subscriber_status' => 1,
            'subscribe_date' => $date,
        ];
        $this->subscriber->transactionSubscribeSave($subscribeData);
        // Send subscription email to customer
        $this->transactionSubscription();
    }

    /**
     * [transactionSubscription Send transaction subscription email]
     */
    public function transactionSubscription()
    {
        $subscribeTemplate = $this->dataHelper->getTransactionSubEmailTemplate();
        $senderData = $this->dataHelper->getSubSenderDetails();
        $this->sendEmail($subscribeTemplate, $senderData);
    }

    /**
     * [transactionUnsubscription Send transaction unsubscription email]
     */
    public function transactionUnsubscription()
    {
        $unsubscribeTemplate = $this->dataHelper->getTransactionUnsubEmailTemplate();
        $senderData = $this->dataHelper->getUnsubSenderDetails();
        $this->sendEmail($unsubscribeTemplate, $senderData);
    }

    /**
     * [sendEmail Send email to customer]
     * @param  [text] $emailTemplate [Email template]
     * @param  [array] $senderData    [Email Sender Details]
     */
    private function sendEmail($emailTemplate, $senderData)
    {
        try {
            $options = ['area' => "frontend", 'store' => $this->storeManager->getStore()->getId()];
            $postObject = new \Magento\Framework\DataObject();
            $from = ["name" => $senderData['name'], "email" => $senderData['email']];
            $this->inlineTranslation->suspend();
            $templateVariables = ['data' => $postObject];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions($options)
                ->setTemplateVars($templateVariables)
                ->setFrom($from)
                ->addTo($this->customerEmail, $this->customerName)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    public function withdrawStatusChangeEmail($customerName, $customerEmail, $newStatus)
    {
        try {
            $withdrawRequestEmailTemplate = $this->dataHelper->getWithdrawalEmailTemplate();
            $senderData = $this->dataHelper->getWithdrawSenderDetails();
            $options = ['area' => "frontend", 'store' => $this->storeManager->getStore()->getId()];
            $postObject = new \Magento\Framework\DataObject();
            $postObject->setStatus($newStatus);
            $from = ["name" => $senderData['name'], "email" => $senderData['email']];
            $this->inlineTranslation->suspend();
            $templateVariables = ['data' => $postObject];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($withdrawRequestEmailTemplate)
                ->setTemplateOptions($options)
                ->setTemplateVars($templateVariables)
                ->setFrom($from)
                ->addTo($customerEmail, $customerName)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
}
