<?php

namespace Dolphin\Walletrewardpoints\Observer\Order;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Dolphin\Walletrewardpoints\Model\InviteFriendFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\OrderFactory;

class SaveOrderAfter implements ObserverInterface
{
    /**
     * [__construct Initialise Dependencies]
     * @param OrderFactory        $orderFactory        [description]
     * @param CustomerFactory     $customerFactory     [description]
     * @param DataHelper          $dataHelper          [description]
     * @param InviteFriendFactory $inviteFriendFactory [description]
     */
    public function __construct(
        OrderFactory $orderFactory,
        CustomerFactory $customerFactory,
        DataHelper $dataHelper,
        InviteFriendFactory $inviteFriendFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->customerFactory = $customerFactory;
        $this->dataHelper = $dataHelper;
        $this->inviteFriendFactory = $inviteFriendFactory;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getStatus() == 'complete' || $order->getStatus() == 'pending' ||
            $order->getStatus() == 'processing') {
            $customerId = $order->getCustomerId();
            $customerEmail = $order->getCustomerEmail();
            $items = $order->getAllItems();
            $rewardPointsFlag = 1;
            // Create reward points product if not exists while by credit
            foreach ($items as $item) {
                if ($item->getSku() == 'rewardpoints') {
                    $rewardPointsFlag = 0;
                    $customer = $this->loadCustomer($customerId);
                    $earnCredit = $item->getQtyOrdered();
                    $walletcredit = $customer->getWalletCredit() + $earnCredit;
                    $customer->setWalletCredit($walletcredit)->save();
                    $transactionData = [];
                    $transactionData["credit_get"] = $earnCredit;
                    $transactionData["credit_spent"] = 0;
                    $transTitle = "Buy Credit(s) from Store.";
                    $this->dataHelper->saveTransaction($customerId, $transTitle, $transactionData);
                }
            }
            $enableReward = $this->dataHelper->getEnableReward();
            $maxAllowCredit = $this->dataHelper->getCustomerMaxCredit();
            $oneRewardPointCost = $this->dataHelper->getOneRewardPointCost();
            $enableCreatingOrder = $this->dataHelper->getEnableCreatingOrder();
            $minOrderQty = $this->dataHelper->getMinOrderedQty();
            $minOrderTotal = $this->dataHelper->getMinOrderTotal();
            $orderTotalQty = $order->getTotalQtyOrdered();
            $orderSubtotal = $order->getSubtotal();
            if ($maxAllowCredit == '') {
                $maxAllowCredit = 0;
            }
            $minOrderQtyFlag = 1;
            $minOrderTotalFlag = 1;
            if ($minOrderQty || $minOrderTotal) {
                if ($minOrderQty && $orderTotalQty < $minOrderQty) {
                    $minOrderQtyFlag = 0;
                }
                if ($minOrderTotal && $orderSubtotal < $minOrderTotal) {
                    $minOrderTotalFlag = 0;
                }
            }
            // Earn credit by place an order by logged in customer
            if ($enableReward && $rewardPointsFlag && $customerId && $enableCreatingOrder &&
                $minOrderQtyFlag && $minOrderTotalFlag) {
                $creatingOrderMaxLimit = $this->dataHelper->getCreatingOrderMaxOrder();
                $orderEarnType = $this->dataHelper->getCreatingOrderEarnType();
                $orderRewardPoint = $this->dataHelper->getCreatingOrderRewardPoint();
                $customer = $this->customerFactory->create()->load($customerId);
                $custOrders = $this->orderFactory->create()->getCollection()
                    ->addFieldToFilter('customer_id', $customerId);
                $totalOrderCount = $custOrders->getSize();
                $COLimitFlag = 1;
                if ($creatingOrderMaxLimit && $totalOrderCount >= $creatingOrderMaxLimit) {
                    $COLimitFlag = 0;
                }
                if ($COLimitFlag) {
                    $custWalletCredit = $customer->getWalletCredit();
                    $totalRewardPoints = $orderRewardPoint;
                    if ($orderEarnType == 1) {
                        $maxRewardPerOrder = $this->dataHelper->getMaxRewardPerOrder();
                        $perceRewardPoint = ($orderSubtotal * $orderRewardPoint) / 100;
                        $totalRewardPoints = $perceRewardPoint;
                        if ($maxRewardPerOrder && $totalRewardPoints > $maxRewardPerOrder) {
                            $totalRewardPoints = $maxRewardPerOrder;
                        }
                    }
                    $addWalletCredit = $totalRewardPoints / $oneRewardPointCost;
                    $totalWalletCredit = $custWalletCredit + $addWalletCredit;
                    $condition = ($maxAllowCredit != 0 && $totalWalletCredit > $maxAllowCredit);
                    $orderEarnCredit = $condition ? $maxAllowCredit - $custWalletCredit : $addWalletCredit;
                    $transactionData = [];
                    $transactionData['order_id'] = $order->getIncrementId();
                    $transactionData['reward_point'] = $totalRewardPoints;
                    $transactionData["credit_get"] = $orderEarnCredit;
                    $transactionData["credit_spent"] = 0;
                    $transTitle = "Earn Reward Point(s) on Order";
                    $this->dataHelper->saveTransaction($customerId, $transTitle, $transactionData);
                    $newWalletCredit = $custWalletCredit + $orderEarnCredit;
                    $customer->setWalletCredit($newWalletCredit)->save();
                }
            }

            // Inviter Earn credit after invited friend placed an order
            $createOrderByInviteFriend = $this->dataHelper->getCreateOrderByInviteFriend();
            if ($enableReward && $rewardPointsFlag && $customerId && $createOrderByInviteFriend) {
                $inviteOrderMaxLimit = $this->dataHelper->getInviteMaxOrderLimit();
                $invOrderEarnType = $this->dataHelper->getInviteOrderEarnType();
                $invOrdRewardPoint = $this->dataHelper->getInviteOrderRewardPoint();
                $customerOrders = $this->orderFactory->create()->getCollection()
                    ->addFieldToFilter('customer_id', $customerId);
                $totalOrderCount = $customerOrders->getSize();
                $inviteFriendColl = $this->inviteFriendFactory->create()->getCollection()
                    ->addFieldToFilter('receiver_email', $customerEmail)
                    ->addFieldToFilter('status', 1);
                $inviteFriendData = $inviteFriendColl->getFirstItem()->getData();
                $orderLimitFlag = 1;
                if ($inviteOrderMaxLimit && $totalOrderCount >= $inviteOrderMaxLimit) {
                    $orderLimitFlag = 0;
                }
                if ($orderLimitFlag && !empty($inviteFriendData)) {
                    $inviterCustId = $inviteFriendData['customer_id'];
                    $inviterCustomer = $this->loadCustomer($inviterCustId);
                    $custWalletCredit = $inviterCustomer->getWalletCredit();
                    $perceRewardPoint = ($orderSubtotal * $invOrdRewardPoint) / 100;
                    $totalRewardPoints = ($invOrderEarnType == 1) ? $perceRewardPoint : $invOrdRewardPoint;
                    $addWalletCredit = $totalRewardPoints / $oneRewardPointCost;
                    $totalWalletCredit = $custWalletCredit + $addWalletCredit;
                    $condition = ($maxAllowCredit != 0 && $totalWalletCredit > $maxAllowCredit);
                    $inviNewAccEarnCredit = $condition ? $maxAllowCredit - $custWalletCredit : $addWalletCredit;
                    if ($inviNewAccEarnCredit > 0) {
                        $transactionData = [];
                        $transactionData['reward_point'] = $totalRewardPoints;
                        $transactionData["credit_get"] = $inviNewAccEarnCredit;
                        $transactionData["credit_spent"] = 0;
                        $transTitle = "Get Reward Point(s) by Invite Friend " .
                            $inviteFriendData['receiver_name'] . " Placed an Order";
                        $this->dataHelper->saveTransaction($inviterCustId, $transTitle, $transactionData);
                        $newWalletCredit = $custWalletCredit + $inviNewAccEarnCredit;
                        $inviterCustomer->setWalletCredit($newWalletCredit)->save();
                    }
                }
            }
        }
    }

    /**
     * [loadCustomer load customer object by customer id]
     * @param  [int] $customerId [customer id]
     * @return [object]             [customer data]
     */
    private function loadCustomer($customerId)
    {
        return $this->customerFactory->create()->load($customerId);
    }
}
