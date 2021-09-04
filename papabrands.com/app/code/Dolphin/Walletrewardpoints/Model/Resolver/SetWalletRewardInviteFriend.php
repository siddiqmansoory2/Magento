<?php
declare (strict_types = 1);

namespace Dolphin\Walletrewardpoints\Model\Resolver;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Dolphin\Walletrewardpoints\Helper\Transaction as TransactionHelper;
use Dolphin\Walletrewardpoints\Model\InviteFriendFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;

class SetWalletRewardInviteFriend implements ResolverInterface
{
    protected $subscriber;
    protected $customerSession;

    public function __construct(
        TransportBuilder $transportBuilder,
        DataHelper $dataHelper,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManagerInterface,
        InviteFriendFactory $inviteFriendFactory,
        TransactionHelper $transactionHelper,
        Session $customerSession
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->dataHelper = $dataHelper;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManagerInterface;
        $this->inviteFriendFactory = $inviteFriendFactory;
        $this->transactionHelper = $transactionHelper;
        $this->customerSession = $customerSession;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        if (empty($args['firstname'])) {
            throw new GraphQlInputException(__('Specify the firstname String value.'));
        }
        if (empty($args['lastname'])) {
            throw new GraphQlInputException(__('Specify the lastname String value.'));
        }
        if (empty($args['email'])) {
            throw new GraphQlInputException(__('Specify the email String value.'));
        }
        if ((!empty($args['email'])) && (!filter_var($args['email'], FILTER_VALIDATE_EMAIL))) {
            throw new GraphQlInputException(__('Email is a not valid email address.'));
        }
        if (empty($args['message'])) {
            throw new GraphQlInputException(__('Specify the message String value.'));
        }
        $receiverEmail = $args['email'];
        $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
        $customerId = (int) $context->getUserId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($args['email']);
        $inviteFriendModel = $this->inviteFriendFactory->create();
        $isInvited = $inviteFriendModel->getCollection()
            ->addFieldToFilter('receiver_email', $args['email'])
            ->addFieldToFilter('customer_id', $customerId);

        if ($customer->getData()) {
            $showMsg = $args['email'] . " is already registered.";
            return [
                'status' => "FAILED",
                'message' => $showMsg,
            ];
        } elseif ($isInvited->getData()) {
            $showMsg = (__($args['email'] . " is already invited."));
            return [
                'status' => "FAILED",
                'message' => $showMsg,
            ];
        } else {
            try {
                $currentDate = date("Y-m-d H:i:s");
                $inviteFriendData = [];
                $inviteFriendData['customer_id'] = $customerId;
                $receiverFullname = $args['firstname'] . " " . $args['lastname'];
                $inviteFriendData['receiver_name'] = $receiverFullname;
                $inviteFriendData['receiver_email'] = $args['email'];
                $inviteFriendData['message'] = $args['message'];
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
                    $url = $this->storeManager->getStore()->getUrl('customer/account/create');
                    $senderName = $this->customerSession->getCustomer()->getName();
                    $postObject = new \Magento\Framework\DataObject();
                    $postObject->setStoreName($this->storeManager->getStore()->getFrontendName());
                    $postObject->setSenderName($senderName);
                    $postObject->setRecipientName($receiverFullname);
                    $postObject->setSenderMessage($args['message']);
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
                $showMsg = (__("Invitation sent successfully to %1 (%2).", $receiverFullname, $receiverEmail));
                return [
                    'status' => "SUCCESS",
                    'message' => $showMsg,
                ];
            } catch (AuthenticationException $e) {
                throw new GraphQlAuthenticationException(__($e->getMessage()), $e);
            }
        }

    }
}
