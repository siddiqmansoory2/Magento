<?php

namespace Dolphin\Walletrewardpoints\Controller\Customer;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Dolphin\Walletrewardpoints\Helper\Transaction as TransactionHelper;
use Dolphin\Walletrewardpoints\Model\InviteFriendFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;

class InviteFriendSubmit extends Action
{
    protected $resultPageFactory;
    protected $customerSession;

    public function __construct(
        Context $context,
        InviteFriendFactory $inviteFriendFactory,
        CustomerSession $customerSession,
        Escaper $escaper,
        DataHelper $dataHelper,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManagerInterface,
        TransactionHelper $transactionHelper,
        TransportBuilder $transportBuilder
    ) {
        $this->inviteFriendFactory = $inviteFriendFactory;
        $this->customerSession = $customerSession;
        $this->escaper = $escaper;
        $this->dataHelper = $dataHelper;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManagerInterface;
        $this->transactionHelper = $transactionHelper;
        $this->transportBuilder = $transportBuilder;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPost();
        $inviteFriendModel = $this->inviteFriendFactory->create();
        $receiverEmail = $data['receiver-email'];
        $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($receiverEmail);
        $customerId = $this->customerSession->getCustomer()->getId();

        $isInvited = $inviteFriendModel->getCollection()
            ->addFieldToFilter('receiver_email', $receiverEmail)
            ->addFieldToFilter('customer_id', $customerId);
        if ($customer->getData()) {
            $this->messageManager->addError(
                __($receiverEmail . ' is already registered.')
            );
        } elseif ($isInvited->getData()) {
            $this->messageManager->addError(
                __($receiverEmail . ' is already invited.')
            );
        } else {
            $currentDate = date("Y-m-d H:i:s");
            $inviteFriendData = [];
            $inviteFriendData['customer_id'] = $customerId;
            $receiverFullname = $data['receiver-firstname'] . " " . $data['receiver-lastname'];
            $inviteFriendData['receiver_name'] = $receiverFullname;
            $inviteFriendData['receiver_email'] = $receiverEmail;
            $inviteFriendData['message'] = $data['message'];
            $inviteFriendData['status'] = 0;
            $inviteFriendData['invite_date'] = $currentDate;
            $inviteFriendModel->setData($inviteFriendData);
            $inviteFriendModel->save();

            // Send email to invite friend start
            $isTemplateSubscriber = $this->transactionHelper->checkTemplateSubscription($customerId);
            if ($isTemplateSubscriber) {
                $inviteFriendEmailTemplate = $this->dataHelper->getInviteFriendEmailTemplate();
                $allowEarnNewAccount = $this->dataHelper->getEnableOnCreateAccount();
                $newAccEarnRewardPoint = $this->dataHelper->getCustomerRegiRewardPoint();
                $rewardPoints = ($allowEarnNewAccount) ? $newAccEarnRewardPoint : "";
                $rewardMessage = ' You will get ' . $rewardPoints . ' Reward Point(s) which you can use ';
                $rewardMessage .= 'while purchase any products on our website and get Reward Point(s) discount.';
                $rewardMessage = ($rewardPoints != "") ? $rewardMessage : '';
                $options = ['area' => "frontend", 'store' => $this->storeManager->getStore()->getId()];
                $url = $this->_url->getUrl('customer/account/create');
                $senderName = $this->customerSession->getCustomer()->getName();
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setStoreName($this->storeManager->getStore()->getFrontendName());
                $postObject->setSenderName($senderName);
                $postObject->setRecipientName($receiverFullname);
                $postObject->setSenderMessage($data['message']);
                $postObject->setRegisterUrl($url);
                $postObject->setRewardpointsMessage($rewardMessage);

                $senderData = $this->dataHelper->getInviteFriendSenderDetails();
                $from = ["name" => $senderData['name'], "email" => $senderData['email']];
                try {
                    $templateVariables = ['data' => $postObject];
                    $transport = $this->transportBuilder
                        ->setTemplateIdentifier($inviteFriendEmailTemplate)
                        ->setTemplateOptions($options)
                        ->setTemplateVars($templateVariables)
                        ->setFrom($from)
                        ->addTo($receiverEmail, $receiverFullname)
                        ->getTransport();
                    $transport->sendMessage();
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
            // Send email to invite friend end

            $this->messageManager->addSuccess(
                __(
                    'Invitation sent successfully to %1 (%2).',
                    $receiverFullname,
                    $receiverEmail
                )
            );
        }
        return $resultRedirect->setPath('walletrewardpoints/customer/invitefriend');
    }
}
