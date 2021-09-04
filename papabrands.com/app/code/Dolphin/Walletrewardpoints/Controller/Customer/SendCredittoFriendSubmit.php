<?php

namespace Dolphin\Walletrewardpoints\Controller\Customer;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Dolphin\Walletrewardpoints\Helper\Transaction as TransactionHelper;
use Dolphin\Walletrewardpoints\Model\Sales\Total\CreditDiscount;
use Dolphin\Walletrewardpoints\Model\TransactionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;

class SendCredittoFriendSubmit extends Action
{
    /**
     * [__construct Initialize dependencies]
     * @param Context               $context               [description]
     * @param TransactionFactory    $transactionFactory    [description]
     * @param CustomerSession       $customerSession       [description]
     * @param DataHelper            $dataHelper            [description]
     * @param TransactionHelper     $transactionHelper     [description]
     * @param StoreManagerInterface $storeManagerInterface [description]
     * @param TransportBuilder      $transportBuilder      [description]
     * @param StateInterface        $stateInterface        [description]
     * @param CustomerFactory       $customerFactory      [description]
     * @param CreditDiscount        $creditDiscountModel   [description]
     */
    public function __construct(
        Context $context,
        TransactionFactory $transactionFactory,
        CustomerSession $customerSession,
        DataHelper $dataHelper,
        TransactionHelper $transactionHelper,
        StoreManagerInterface $storeManagerInterface,
        TransportBuilder $transportBuilder,
        StateInterface $stateInterface,
        CustomerFactory $customerFactory,
        CreditDiscount $creditDiscountModel
    ) {
        $this->customerSession = $customerSession;
        $this->transaction = $transactionFactory;
        $this->dataHelper = $dataHelper;
        $this->transactionHelper = $transactionHelper;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->transportBuilder = $transportBuilder;
        $this->stateInterface = $stateInterface;
        $this->customerFactory = $customerFactory;
        $this->creditDiscountModel = $creditDiscountModel;
        parent::__construct($context);
    }

    protected function _getSession()
    {
        return $this->customerSession;
    }

    public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    public function execute()
    {
        $walletCredit = $this->customerSession->getCustomer()->getWalletCredit();
        $resultRedirect = $this->resultRedirectFactory->create();
        $allowToSendCredit = $this->dataHelper->getAllowSendCredit();
        $maxAllowCredit = $this->dataHelper->getCustomerMaxCredit();
        if ($maxAllowCredit == '') {
            $maxAllowCredit = 0;
        }
        if ($allowToSendCredit != 1) {
            $this->messageManager->addError(
                __(
                    'Sorry you can not allow to send credit to friend.'
                )
            );
            return $resultRedirect->setPath('walletrewardpoints/customer/transaction');
        }
        $data = $this->getRequest()->getPost();
        $websiteId = $this->storeManagerInterface->getStore()->getWebsiteId();
        $friendEmail = $data['friend-email'];
        $customer = $this->customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($friendEmail);
        $customerId = $customer->getId();
        $addCredit = $data['credit'];
        $addCredit = $this->creditDiscountModel->convertPrice($addCredit, 1);
        $friendWalletCredit = ($customerId) ? $customer->getWalletCredit() : 0;
        $totalCredit = $friendWalletCredit + $addCredit;

        if (($maxAllowCredit != 0) && ($totalCredit > $maxAllowCredit)) {
            $qty = $maxAllowCredit - $friendWalletCredit;
            $sendCredit = ($qty > 0) ? $qty : 0;
            $this->messageManager->addNotice(
                __(
                    'You can send maximum %1 credit.',
                    $sendCredit
                )
            );
            return $resultRedirect->setPath('walletrewardpoints/customer/sendcredittofriend');
        } else {
            $customer_id = $data['customer_id'];
            $senderCustomer = $this->customerFactory->create()->load($customer_id);

            $senderCredit = $senderCustomer->getWalletCredit() - $addCredit;
            $senderCustomer->setWalletCredit($senderCredit)->save();

            $transactionData = [];
            $transactionData["credit_get"] = 0;
            $transactionData["credit_spent"] = $addCredit;
            $custFullname = $this->customerSession->getCustomer()->getName();
            $receiverFullname = $data['firstname'] . " " . $data['lastname'];
            $transTitle = "Send Credit to " . $receiverFullname . ' (' . $friendEmail . ')';
            $this->dataHelper->saveTransaction($customer_id, $transTitle, $transactionData);

            if ($customerId) {
                $friendCredit = $customer->getWalletCredit() + $addCredit;
                $customer->setWalletCredit($friendCredit)->save();

                $transactionData = [];
                $transactionData["credit_get"] = $addCredit;
                $transactionData["credit_spent"] = 0;
                $custEmail = $this->customerSession->getCustomer()->getEmail();
                $transTitle = "Get Credit from " . $custFullname . ' (' . $custEmail . ')';
                $this->dataHelper->saveTransaction($customerId, $transTitle, $transactionData);
                $emailTemplate = $this->dataHelper->getRegisteredFriEmailTemplate();
                $url = $this->_url->getUrl('customer/account/login');
            } else {
                $this->dataHelper->saveFriendCredit($friendEmail, $addCredit, $custFullname);
                $emailTemplate = $this->dataHelper->getGuestUserEmailTemplate();
                $url = $this->_url->getUrl('customer/account/create');
            }

            // Send transaction email to customer start
            $isTemplateSubscriber = $this->transactionHelper->checkTemplateSubscription($customerId);
            if ($isTemplateSubscriber) {
                $currencySymbol = $this->dataHelper->getCurrencySymbol();
                $options = ['area' => "frontend", 'store' => $this->storeManagerInterface->getStore()->getId()];
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setStoreName($this->storeManagerInterface->getStore()->getFrontendName());
                $postObject->setSenderName($custFullname);
                $postObject->setRecipientName($receiverFullname);
                $postObject->setSenderMessage($data['message']);
                $postObject->setCredit($currencySymbol . number_format((float) $data['credit'], 2, '.', ''));
                $postObject->setUrl($url);
                $senderData = $this->dataHelper->getSendToFriSenderDetails();
                $from = ["name" => $senderData['name'], "email" => $senderData['email']];

                try {
                    $this->stateInterface->suspend();
                    $templateVariables = ['data' => $postObject];
                    $transport = $this->transportBuilder
                        ->setTemplateIdentifier($emailTemplate)
                        ->setTemplateOptions($options)
                        ->setTemplateVars($templateVariables)
                        ->setFrom($from)
                        ->addTo($friendEmail, $receiverFullname)
                        ->getTransport();
                    $transport->sendMessage();
                    $this->stateInterface->resume();
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
            // Send transaction email to customer end

            $this->messageManager->addSuccess(
                __(
                    'Credit sent successfully to %1.',
                    $receiverFullname
                )
            );
            return $resultRedirect->setPath('walletrewardpoints/customer/transaction');
        }
    }
}
