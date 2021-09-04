<?php
declare (strict_types = 1);

namespace Dolphin\Walletrewardpoints\Model\Resolver;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Dolphin\Walletrewardpoints\Helper\Transaction as TransactionHelper;
use Dolphin\Walletrewardpoints\Model\InviteFriendFactory;
use Dolphin\Walletrewardpoints\Model\Sales\Total\CreditDiscount;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;

class SendWalletRewardCreditToFriend implements ResolverInterface
{
    protected $subscriber;
    protected $customerSession;

    public function __construct(
        StateInterface $stateInterface,
        CreditDiscount $creditDiscountModel,
        TransportBuilder $transportBuilder,
        DataHelper $dataHelper,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManagerInterface,
        InviteFriendFactory $inviteFriendFactory,
        TransactionHelper $transactionHelper,
        Session $customerSession
    ) {
        $this->stateInterface = $stateInterface;
        $this->creditDiscountModel = $creditDiscountModel;
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
        if (!is_int($args['credit']) || $args['credit'] <= 0) {
            throw new GraphQlInputException(__('Specify the credit Int value greater then zero.'));
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

        $walletCredit = $this->customerSession->getCustomer()->getWalletCredit();
        $allowToSendCredit = $this->dataHelper->getAllowSendCredit();
        $maxAllowCredit = $this->dataHelper->getCustomerMaxCredit();
        if ($maxAllowCredit == '') {
            $maxAllowCredit = 0;
        }
        if ($allowToSendCredit != 1) {
            $showMsg = (__('Sorry you can not allow to send credit to friend.'));
            return [
                'status' => "FAILED",
                'message' => $showMsg,
            ];
        }
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $friendEmail = $args['email'];
        $customer = $this->customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($friendEmail);
        $customerId = $customer->getId();
        $addCredit = $args['credit'];
        $addCredit = $this->creditDiscountModel->convertPrice($addCredit, 1);
        $friendWalletCredit = ($customerId) ? $customer->getWalletCredit() : 0;
        $totalCredit = $friendWalletCredit + $addCredit;

        if (($maxAllowCredit != 0) && ($totalCredit > $maxAllowCredit)) {
            $qty = $maxAllowCredit - $friendWalletCredit;
            $sendCredit = ($qty > 0) ? $qty : 0;
            $showMsg = (__('You can send maximum %1 credit.', $sendCredit));
            return [
                'status' => "FAILED",
                'message' => $showMsg,
            ];
        } else {
            $customer_id = (int) $context->getUserId();
            $senderCustomer = $this->customerFactory->create()->load($customer_id);
            $senderCredit = $senderCustomer->getWalletCredit() - $addCredit;
            $senderCustomer->setWalletCredit($senderCredit)->save();

            $transactionData = [];
            $transactionData["credit_get"] = 0;
            $transactionData["credit_spent"] = $addCredit;
            $custFullname = $this->customerSession->getCustomer()->getName();
            $receiverFullname = $args['firstname'] . " " . $args['lastname'];
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
                $url = $this->storeManager->getStore()->getUrl('customer/account/login');
            } else {
                $this->dataHelper->saveFriendCredit($friendEmail, $addCredit, $custFullname);
                $emailTemplate = $this->dataHelper->getGuestUserEmailTemplate();
                $url = $this->storeManager->getStore()->getUrl('customer/account/create');
            }

            // Send transaction email to customer start
            $isTemplateSubscriber = $this->transactionHelper->checkTemplateSubscription($customerId);
            if ($isTemplateSubscriber) {
                $currencySymbol = $this->dataHelper->getCurrencySymbol();
                $options = ['area' => "frontend", 'store' => $this->storeManager->getStore()->getId()];
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setStoreName($this->storeManager->getStore()->getFrontendName());
                $postObject->setSenderName($custFullname);
                $postObject->setRecipientName($receiverFullname);
                $postObject->setSenderMessage($args['message']);
                $postObject->setCredit($currencySymbol . number_format((float) $args['credit'], 2, '.', ''));
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
                    $showMsg = (__($e->getMessage()));
                    return [
                        'status' => "FAILED",
                        'message' => $showMsg,
                    ];
                }
            }
            // Send transaction email to customer end

            $showMsg = (__('Credit sent successfully to %1.', $receiverFullname));
            return [
                'status' => "SUCCESS",
                'message' => $showMsg,
            ];
        }

    }
}
