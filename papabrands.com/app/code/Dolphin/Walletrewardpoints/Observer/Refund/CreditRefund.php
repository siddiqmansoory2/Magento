<?php

namespace Dolphin\Walletrewardpoints\Observer\Refund;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\OrderFactory;

class CreditRefund implements ObserverInterface
{
    protected $scopeConfig;

    public function __construct(
        OrderFactory $orderFactory,
        CustomerFactory $customerFactory,
        DataHelper $dataHelper
    ) {
        $this->customerFactory = $customerFactory;
        $this->orderFactory = $orderFactory;
        $this->dataHelper = $dataHelper;
    }

    public function execute(Observer $observer)
    {
        $allowRefund = $this->dataHelper->getAllowRefundCredit();
        $isEnableWallet = $this->dataHelper->getIsEnableWalletExtension();
        if ($isEnableWallet && $allowRefund) {
            $result = $observer->getEvent()->getCreditmemo();
            $order = $this->orderFactory->create()->load($result->getData('order_id'));
            $customerId = $result->getData('customer_id');
            $creditDiscount = $result->getData('credit_base_fee_amount');
            $maxAllowCredit = $this->dataHelper->getCustomerMaxCredit();
            if ($maxAllowCredit == '') {
                $maxAllowCredit = 0;
            }
            $customer = $this->customerFactory->create()->load($customerId);
            $custWalletCredit = $customer->getWalletCredit();
            $customer = $this->customerFactory->create()->load($customerId);
            $totalWalletCredit = $custWalletCredit + abs($creditDiscount);
            $condition = ($maxAllowCredit != 0 && $totalWalletCredit > $maxAllowCredit);
            $totalEarnCredit = $condition ? $maxAllowCredit - $custWalletCredit : abs($creditDiscount);
            if ($totalEarnCredit > 0) {
                $transactionData = [];
                $transactionData["order_id"] = $order->getIncrementId();
                $transactionData["credit_get"] = $totalEarnCredit;
                $transactionData["credit_spent"] = 0;
                $transTitle = "Store Credit(s) for Return Product";
                $this->dataHelper->saveTransaction($customerId, $transTitle, $transactionData);
                $newWalletCredit = $custWalletCredit + $totalEarnCredit;
                $customer->setWalletCredit($newWalletCredit)->save();
            }
        }
    }
}
