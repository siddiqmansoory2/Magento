<?php

namespace Dolphin\Walletrewardpoints\Observer\Review;

use Dolphin\Walletrewardpoints\Helper\Data;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewFactory;
use Magento\Review\Model\Review;

class ReviewSaveAfter implements ObserverInterface
{
    /**
     * [__construct Get model object]
     * @param Data            $helper          [description]
     * @param CustomerFactory $customerFactory [description]
     * @param ReviewFactory   $reviewFactory   [description]
     */
    public function __construct(
        Data $helper,
        CustomerFactory $customerFactory,
        ReviewFactory $reviewFactory
    ) {
        $this->customerFactory = $customerFactory;
        $this->helper = $helper;
        $this->reviewFactory = $reviewFactory;
    }

    public function execute(Observer $observer)
    {
        $earnFlag = 1;
        $review = $observer->getDataObject();
        $oldStatus = $review->getOrigData('status_id');
        $newStatus = $review->getData('status_id');
        $customer_id = $review->getData('customer_id');
        $approved = Review::STATUS_APPROVED;
        $enableCustomerReview = $this->helper->getEnableCustomerReview();
        $enableReward = $this->helper->getEnableReward();
        if ($enableReward && $enableCustomerReview && $oldStatus != $newStatus &&
            $newStatus == $approved && $customer_id != "") {
            $maxReviewLimit = $this->helper->getMaxCustomerReviewLimit();
            if ($maxReviewLimit != '') {
                $reviews = $this->reviewFactory->create()
                    ->addStatusFilter($approved)
                    ->addCustomerFilter($customer_id);
                if ($reviews->getSize() > $maxReviewLimit) {
                    $earnFlag = 0;
                }
            }
            $reviewRewardPoint = $this->helper->getCustomerReviewRewardPoint();
            $maxAllowCredit = $this->helper->getCustomerMaxCredit();
            $oneRewardPointCost = $this->helper->getOneRewardPointCost();
            if ($maxAllowCredit == '') {
                $maxAllowCredit = 0;
            }
            $customer = $this->customerFactory->create()->load($customer_id);
            $custWalletCredit = $customer->getWalletCredit();
            $addWalletCredit = $reviewRewardPoint / $oneRewardPointCost;
            $totalWalletCredit = $custWalletCredit + $addWalletCredit;
            $condition = ($maxAllowCredit != 0 && $totalWalletCredit > $maxAllowCredit);
            $totalEarnCredit = $condition ? $maxAllowCredit - $custWalletCredit : $addWalletCredit;
            if ($totalEarnCredit > 0 && $earnFlag == 1) {
                $transactionData = [];
                $transactionData['reward_point'] = $reviewRewardPoint;
                $transactionData["credit_get"] = $totalEarnCredit;
                $transactionData["credit_spent"] = 0;
                $transTitle = "Earn Reward Point(s) by Product Review";
                $this->helper->saveTransaction($customer_id, $transTitle, $transactionData);
                $newWalletCredit = $custWalletCredit + $totalEarnCredit;
                $customer->setWalletCredit($newWalletCredit)->save();
            }
        }
    }
}
