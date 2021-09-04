<?php

namespace Dolphin\Walletrewardpoints\Observer\Order;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Magento\Catalog\Model\Session as CatalogSessin;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\OrderFactory;

class SuccessOrder implements ObserverInterface
{
    public function __construct(
        OrderFactory $orderFactory,
        CatalogSessin $catalogSession,
        CustomerSession $customerSession,
        DataHelper $dataHelper,
        CustomerFactory $customerFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->catalogSession = $catalogSession;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->dataHelper = $dataHelper;
    }

    public function execute(Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        if (!empty($orderIds)) {
            $orderId = $orderIds[0];
            $order = $this->orderFactory->create()->load($orderId);
            $customer_email = $this->customerSession->getCustomer()->getEmail();
            if ($customer_email != '') {
                if ($order->getStatus() == 'complete' || $order->getStatus() == 'pending' ||
                    $order->getStatus() == 'processing') {
                    $orderCreditDiscount = $order->getCreditBaseFeeAmount();
                    if ($orderCreditDiscount != '' && $orderCreditDiscount != 0 && $orderCreditDiscount != 'NULL') {
                        $customerId = $order->getCustomerId();
                        $transactionData = [];
                        $transactionData['order_id'] = $order->getIncrementId();
                        $transactionData["credit_get"] = 0;
                        $transactionData["credit_spent"] = abs($orderCreditDiscount);
                        $transTitle = "Use Credit(s) on Order";
                        $this->dataHelper->saveTransaction($customerId, $transTitle, $transactionData);
                        $customer = $this->customerFactory->create()->load($customerId);
                        $custWalletCredit = $customer->getWalletCredit();
                        $newWalletCredit = $custWalletCredit - abs($orderCreditDiscount);
                        $customer->setWalletCredit($newWalletCredit)->save();
                        $this->catalogSession->setApplyCredit(0);
                    }
                }
            }
        }
    }
}
