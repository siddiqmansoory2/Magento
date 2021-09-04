<?php

namespace Dolphin\Walletrewardpoints\Helper;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Newsletter extends AbstractHelper
{
    /**
     * [__construct Get model object]
     * @param Context         $context         [description]
     * @param DataHelper            $dataHelper      [description]
     * @param CustomerFactory $customerFactory [description]
     */
    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        CustomerFactory $customerFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->customerFactory = $customerFactory;
        parent::__construct($context);
    }

    /**
     * [signUpForNewsletter Newsletter subscription while create an account]
     * @param  [int] $customer_id [Customer Id]
     */
    public function signUpForNewsletter($customer_id)
    {
        $enable_reward = $this->dataHelper->getEnableReward();
        $allowNewsletterSub = $this->dataHelper->getEnableNewsletterSub();
        $newsletterSubConfirm = $this->dataHelper->getNewsletterSubConfirm();
        $newsletterRewardPoint = $this->dataHelper->getNewsletterSubRewardPoint();
        $maxAllowCredit = $this->dataHelper->getCustomerMaxCredit();
        $oneRewardPointCost = $this->dataHelper->getOneRewardPointCost();
        if ($maxAllowCredit == '') {
            $maxAllowCredit = 0;
        }
        if ($enable_reward && !$newsletterSubConfirm && $allowNewsletterSub) {
            $customer = $this->customerFactory->create()->load($customer_id);
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
