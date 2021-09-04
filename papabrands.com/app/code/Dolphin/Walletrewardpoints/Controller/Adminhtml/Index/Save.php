<?php

namespace Dolphin\Walletrewardpoints\Controller\Adminhtml\Index;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Dolphin\Walletrewardpoints\Helper\Transaction;
use Dolphin\Walletrewardpoints\Model\Withdraw;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Magento\Customer\Model\CustomerFactory;

class Save extends \Magento\Backend\App\Action
{
    protected $withdraw;
    protected $adminsession;

    public function __construct(
        Action\Context $context,
        Withdraw $withdraw,
        Session $adminsession,
        Transaction $transactionHelper,
        CustomerFactory $customerFactory,
        DataHelper $dataHelper
    ) {
        parent::__construct($context);
        $this->withdraw = $withdraw;
        $this->adminsession = $adminsession;
        $this->transactionHelper = $transactionHelper;
        $this->customerFactory = $customerFactory;
        $this->dataHelper = $dataHelper;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $withdraw_id = $this->getRequest()->getParam('withdraw_id');
            $sendEmailFlag = 0;
            if ($withdraw_id) {
                $this->withdraw->load($withdraw_id);
                if ($this->withdraw->getStatus() != $data['status']) {
                    $sendEmailFlag = 1;
                }
            }
            $data['updated_date'] = date("Y-m-d H:i:s");
            $this->withdraw->setData($data);

            try {
                $this->withdraw->save();
                if ($sendEmailFlag) {
                    $customerName = $this->withdraw->getCustomerFullname();
                    $customerEmail = $this->withdraw->getEmail();
                    if ($data['status'] == 0) {
                        $status = 'Pending';
                    } elseif ($data['status'] == 1) {
                        $status = 'Approved';
                        $customerId = $this->withdraw->getCustomerId();
                        $customerData = $this->customerFactory->create()->load($customerId);
                        $withdrawCredit = $this->withdraw->getCredit();
                        $custWalletCredit = $customerData->getWalletCredit() - $withdrawCredit;
                        if ($custWalletCredit < 0) {
                            $custWalletCredit = 0;
                        }
                        $customerData->setWalletCredit($custWalletCredit)->save();
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
                $this->messageManager->addSuccess(__('Withdraw request updated successfully.'));
                $this->adminsession->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    if ($this->getRequest()->getParam('back') == 'add') {
                        return $resultRedirect->setPath('*/*/add');
                    } else {
                        return $resultRedirect->setPath(
                            '*/*/edit',
                            [
                                'withdraw_id' => $this->withdraw->getWithdrawId(),
                                '_current' => true,
                            ]
                        );
                    }
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['withdraw_id' => $this->getRequest()->getParam('withdraw_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}
