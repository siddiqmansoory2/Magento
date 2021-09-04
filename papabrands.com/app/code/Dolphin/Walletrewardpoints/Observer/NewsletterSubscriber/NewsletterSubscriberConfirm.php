<?php

namespace Dolphin\Walletrewardpoints\Observer\NewsletterSubscriber;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\SubscriberFactory;

class NewsletterSubscriberConfirm implements ObserverInterface
{
    /**
     * [__construct Initialise Dependencies]
     * @param CustomerSession   $customerSession   [description]
     * @param CustomerFactory   $customerFactory   [description]
     * @param SubscriberFactory $subscriberFactory [description]
     * @param DataHelper        $dataHelper        [description]
     */
    public function __construct(
        CustomerSession $customerSession,
        CustomerFactory $customerFactory,
        SubscriberFactory $subscriberFactory,
        DataHelper $dataHelper
    ) {
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->dataHelper = $dataHelper;
    }

    public function execute(Observer $observer)
    {
        $data = $observer->getEvent()->getRequest()->getParams();
        $newsletterSubscriber = $this->subscriberFactory->create()
            ->getCollection()
            ->addFieldToFilter('subscriber_id', $data['id'])
            ->getFirstItem()
            ->getData();
        $isEnableReward = $this->dataHelper->getEnableReward();
        if ($isEnableReward && $newsletterSubscriber) {
            $customer_id = $this->customerSession->getCustomer()->getId();
            $customer = $this->customerFactory->create()
                ->load($newsletterSubscriber['customer_id']);
            $allowNewsletterSub = $this->dataHelper->getEnableNewsletterSub();
            $newsletterRewardPoint = $this->dataHelper->getNewsletterSubRewardPoint();
            $maxAllowCredit = $this->dataHelper->getCustomerMaxCredit();
            $oneRewardPointCost = $this->dataHelper->getOneRewardPointCost();
            if ($maxAllowCredit == '') {
                $maxAllowCredit = 0;
            }
            if ($allowNewsletterSub && !$customer->getIsNewsletterSignup()) {
                $custWalletCredit = $customer->getWalletCredit();
                $addWalletCredit = $newsletterRewardPoint / $oneRewardPointCost;
                $totalWalletCredit = $custWalletCredit + $addWalletCredit;
                $condition = ($maxAllowCredit != 0 && $totalWalletCredit > $maxAllowCredit);
                $totalEarnCredit = $condition ? $maxAllowCredit - $custWalletCredit : $addWalletCredit;
                if ($totalEarnCredit > 0) {
                    $transactionData = [];
                    $transactionData['reward_point'] = $newsletterRewardPoint;
                    $transactionData["credit_get"] = $totalEarnCredit;
                    $transactionData["credit_spent"] = 0;
                    $transTitle = "Get Reward Point(s) by Newsletter Subscription";
                    $this->dataHelper->saveTransaction($customer_id, $transTitle, $transactionData);
                    $newWalletCredit = $custWalletCredit + $totalEarnCredit;
                    $customer->setWalletCredit($newWalletCredit)->save();
                }
            }
        }
    }
}
