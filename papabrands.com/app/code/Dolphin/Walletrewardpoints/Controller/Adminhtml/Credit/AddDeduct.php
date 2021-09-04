<?php

namespace Dolphin\Walletrewardpoints\Controller\Adminhtml\Credit;

use Dolphin\Walletrewardpoints\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AddDeduct extends Action
{
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        CustomerFactory $customerFactory,
        Data $helper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->customerFactory = $customerFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();
        $customer = $this->customerFactory->create()->load($data['customer_id']);
        $customerData = $customer->getData();
        $customer_id = $data["customer_id"];
        $creditAmount = $data["credit"];
        $customerWalletCredit = $customerData["wallet_credit"];

        try {
            $totalCredit = $customer->getWalletCredit() + $creditAmount;
            if ($totalCredit < 0 || $creditAmount == 0) {
                $notice_text = "Your deduct amount is must lesser than your current wallet credit.";
                if ($creditAmount == 0) {
                    $notice_text = "Please enter a valid number in this field.";
                }
                $this->messageManager->addNotice(
                    __(
                        $notice_text
                    )
                );
                return $resultRedirect->setPath('customer/index/edit/id/' . $customer_id . '/');
            }
            $customerMaxCredit = $this->helper->getCustomerMaxCredit();
            if ($customerMaxCredit && $totalCredit > $customerMaxCredit) {
                $this->messageManager->addNotice(
                    __(
                        'Customer maximum credit limit is %1.',
                        $customerMaxCredit
                    )
                );
                return $resultRedirect->setPath('customer/index/edit/id/' . $customer_id . '/');
            }
            $customer->setWalletCredit($totalCredit)->save();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $transactionData = [];
        if ($creditAmount > 0) {
            $transactionData["credit_get"] = $creditAmount;
        } else {
            $transactionData["credit_spent"] = abs($creditAmount);
        }
        $transTitle = "Updated credit(s) by Admin";
        $this->helper->saveTransaction($customer_id, $transTitle, $transactionData);
        if ($creditAmount < 0) {
            $this->messageManager->addSuccess(
                __(
                    'Credit amount(s) deducted successfully.'
                )
            );
        } else {
            $this->messageManager->addSuccess(
                __(
                    'Credit amount(s) added successfully.'
                )
            );
        }
        return $resultRedirect->setPath('customer/index/edit/id/' . $customer_id . '/');
    }
}
