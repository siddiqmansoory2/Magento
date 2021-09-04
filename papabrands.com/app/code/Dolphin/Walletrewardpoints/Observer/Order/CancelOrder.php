<?php

namespace Dolphin\Walletrewardpoints\Observer\Order;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\OrderFactory;

class CancelOrder implements ObserverInterface
{
    public function __construct(
        DataHelper $dataHelper,
        OrderFactory $orderFactory,
        CustomerFactory $customerFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->orderFactory = $orderFactory;
        $this->customerFactory = $customerFactory;
    }

    public function execute(Observer $observer)
    {
        $orderResult = $observer->getEvent()->getOrder();
        $order = $this->orderFactory->create()->load($orderResult->getData('entity_id'));
        $maxAllowCredit = $this->dataHelper->getCustomerMaxCredit();
        if ($maxAllowCredit == '') {
            $maxAllowCredit = 0;
        }
        $customerId = $order->getCustomerId();
        $customer = $this->customerFactory->create()->load($customerId);
        $customerEmail = $customer->getEmail();
        $custWalletCredit = $customer->getWalletCredit();
        $creditDiscount = $order->getCreditBaseFeeAmount();
        if ($creditDiscount != 0) {
            $totalWalletCredit = $custWalletCredit + abs($creditDiscount);
            $condition = ($maxAllowCredit != 0 && $totalWalletCredit >= $maxAllowCredit);
            $cancelOrderCredit = $condition ? $maxAllowCredit - $custWalletCredit : abs($creditDiscount);
            $orderStatus = $orderResult->getData('status');
            if ($customerEmail != '') {
                if ($cancelOrderCredit > 0 && $orderStatus == 'canceled') {
                    $transactionData = [];
                    $transactionData["credit_get"] = $cancelOrderCredit;
                    $transactionData["credit_spent"] = 0;
                    $transTitle = "Store Credit(s) from Canceled Order";
                    $this->dataHelper->saveTransaction($customerId, $transTitle, $transactionData);
                    $newWalletCredit = $custWalletCredit + $cancelOrderCredit;
                    $customer->setWalletCredit($newWalletCredit)->save();
                }
            }
        }
    }
}
