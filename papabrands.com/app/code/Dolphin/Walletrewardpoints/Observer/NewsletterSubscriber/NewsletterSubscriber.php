<?php

namespace Dolphin\Walletrewardpoints\Observer\NewsletterSubscriber;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class NewsletterSubscriber implements ObserverInterface
{
    /**
     * [__construct Initialize dependencies]
     * @param CustomerSession $customerSession [description]
     * @param CustomerFactory $customerFactory [description]
     * @param DataHelper      $dataHelper      [description]
     */
    public function __construct(
        CustomerSession $customerSession,
        CustomerFactory $customerFactory,
        DataHelper $dataHelper
    ) {
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->dataHelper = $dataHelper;
    }

    public function execute(Observer $observer)
    {
        $isSubscribed = $observer->getEvent()->getRequest()->getParam('is_subscribed');
        $customer_id = $this->customerSession->getCustomer()->getId();
        $customer = $this->customerFactory->create()->load($customer_id);
        $newsletterSubConfirm = $this->dataHelper->getNewsletterSubConfirm();
        $allowNewsletterSub = $this->dataHelper->getEnableNewsletterSub();
        $maxAllowCredit = $this->dataHelper->getCustomerMaxCredit();
        if ($maxAllowCredit == '') {
            $maxAllowCredit = 0;
        }
        $isEnableReward = $this->dataHelper->getEnableReward();
        if ($isEnableReward && $isSubscribed && !$newsletterSubConfirm &&
            $allowNewsletterSub && !$customer->getIsNewsletterSignup()) {
            $newsletterRewardPoint = $this->dataHelper->getNewsletterSubRewardPoint();
            $oneRewardPointCost = $this->dataHelper->getOneRewardPointCost();
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
