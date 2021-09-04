<?php

namespace Dolphin\Walletrewardpoints\Controller\Adminhtml\Index;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Dolphin\Walletrewardpoints\Helper\Transaction;
use Dolphin\Walletrewardpoints\Model\ResourceModel\Withdraw\CollectionFactory;
use Dolphin\Walletrewardpoints\Model\Withdraw;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassStatus extends \Magento\Backend\App\Action
{
    protected $filter;
    protected $collectionFactory;
    protected $Withdrawmodel;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Withdraw $Withdrawmodel,
        Transaction $transactionHelper,
        CustomerFactory $customerFactory,
        DataHelper $dataHelper
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->Withdrawmodel = $Withdrawmodel;
        $this->transactionHelper = $transactionHelper;
        $this->customerFactory = $customerFactory;
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $jobData = $this->collectionFactory->create();

        foreach ($jobData as $value) {
            $templateId[] = $value['withdraw_id'];
        }
        $parameterData = $this->getRequest()->getParams('status');
        $selectedAppsid = $this->getRequest()->getParams('status');
        if (array_key_exists("selected", $parameterData)) {
            $selectedAppsid = $parameterData['selected'];
        }
        if (array_key_exists("excluded", $parameterData)) {
            if ($parameterData['excluded'] == 'false') {
                $selectedAppsid = $templateId;
            } else {
                $selectedAppsid = array_diff($templateId, $parameterData['excluded']);
            }
        }
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('withdraw_id', ['in' => $selectedAppsid]);
        $status = 0;
        $model = [];
        foreach ($collection as $item) {
            $this->setStatus($item->getWithdrawId(), $this->getRequest()->getParam('status'));
            $status++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $status));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * [setStatus Change status and change updated date]
     * @param [type] $id    [description]
     * @param [type] $Param [description]
     */
    public function setStatus($id, $Param)
    {
        $item = $this->Withdrawmodel->load($id);
        if ($item->getStatus() != $Param) {
            $customerId = $this->Withdrawmodel->getCustomerId();
            $customer = $this->customerFactory->create()->load($customerId);
            $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
            $customerEmail = $customer->getEmail();
            if ($Param == 0) {
                $status = 'Pending';
            } elseif ($Param == 1) {
                $status = 'Approved';
                $withdrawCredit = $item->getCredit();
                $custWalletCredit = $customer->getWalletCredit() - $withdrawCredit;
                if ($custWalletCredit < 0) {
                    $custWalletCredit = 0;
                }
                $customer->setWalletCredit($custWalletCredit)->save();
                $transactionData = [];
                $transactionData["credit_get"] = 0;
                $transactionData["credit_spent"] = $withdrawCredit;
                $transTitle = "Withdraw Credit";
                $this->dataHelper->saveTransaction($customerId, $transTitle, $transactionData);
            } else {
                $status = 'Rejected';
            }
            $this->transactionHelper->withdrawStatusChangeEmail($customerName, $customerEmail, $status);
        }
        $item->setStatus($Param);
        $item->setUpdatedDate(date("Y-m-d H:i:s"));
        $item->save();
    }
}
