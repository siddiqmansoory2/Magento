<?php

namespace Dolphin\Walletrewardpoints\Observer;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Dolphin\Walletrewardpoints\Helper\Newsletter as NewsletterHelper;
use Dolphin\Walletrewardpoints\Helper\Transaction as TransactionHelper;
use Dolphin\Walletrewardpoints\Model\InviteFriendFactory;
use Dolphin\Walletrewardpoints\Model\SendCredittoFriendFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class CustomerRegister implements ObserverInterface
{
    /**
     * [__construct Initialize dependencies]
     * @param ManagerInterface            $messageManager            [description]
     * @param SendCredittoFriendFactory   $sendCredittoFriendFactory [description]
     * @param CustomerFactory             $customerFactory           [description]
     * @param InviteFriendFactory         $inviteFriendFactory       [description]
     * @param DataHelper                  $dataHelper                [description]
     * @param TransactionHelper           $transactionHelper         [description]
     * @param CustomerRepositoryInterface $customerRepository        [description]
     * @param NewsletterHelper            $newsletterHelper          [description]
     */
    public function __construct(
        ManagerInterface $messageManager,
        SendCredittoFriendFactory $sendCredittoFriendFactory,
        CustomerFactory $customerFactory,
        InviteFriendFactory $inviteFriendFactory,
        DataHelper $dataHelper,
        TransactionHelper $transactionHelper,
        CustomerRepositoryInterface $customerRepository,
        NewsletterHelper $newsletterHelper
    ) {
        $this->messageManager = $messageManager;
        $this->sendCredittoFriendFactory = $sendCredittoFriendFactory;
        $this->customerFactory = $customerFactory;
        $this->inviteFriendFactory = $inviteFriendFactory;
        $this->dataHelper = $dataHelper;
        $this->transactionHelper = $transactionHelper;
        $this->customerRepository = $customerRepository;
        $this->newsletterHelper = $newsletterHelper;
    }

    public function execute(Observer $observer)
    {
        $enable_reward = $this->dataHelper->getEnableReward();
        $customer_id = $observer->getEvent()->getCustomer()->getId();
        $custFirstName = $observer->getEvent()->getCustomer()->getFirstname();
        $custLastName = $observer->getEvent()->getCustomer()->getLastname();
        $customerName = $custFirstName . ' ' . $custLastName;
        if ($enable_reward) {
            // Sign Up for Newsletter while create an account Start
            $custData = $this->customerRepository->getById($customer_id);
            $extensionAttributes = $custData->getExtensionAttributes();
            $isSubscribed = $extensionAttributes->getIsSubscribed();
            if ($isSubscribed) {
                $this->newsletterHelper->signUpForNewsletter($customer_id);
            }
            // Sign Up for Newsletter while create an account End
        }

        $customer_email = $observer->getEvent()->getCustomer()->getEmail();
        $allowEarnNewAccount = $this->dataHelper->getEnableOnCreateAccount();
        $newAccEarnRewardPoint = $this->dataHelper->getCustomerRegiRewardPoint();
        $allowToSendCredit = $this->dataHelper->getAllowSendCredit();
        $maxAllowCredit = $this->dataHelper->getCustomerMaxCredit();
        $earnInviteFriRegi = $this->dataHelper->getEarnInvitedFriendRegi();

        if ($maxAllowCredit == '') {
            $maxAllowCredit = 0;
        }
        $sendCredittoFriend = 0;
        // Get credit if someone give you credit before your registration
        if ($allowToSendCredit == 1) {
            $customer = $this->customerFactory->create()->load($customer_id);
            $sendCredittoFriendColl = $this->sendCredittoFriendFactory->create()
                ->getCollection()
                ->addFieldToFilter('friend_email', $customer_email);
            if ($sendCredittoFriendColl->getData()) {
                $sendbyFriendname = '';
                foreach ($sendCredittoFriendColl as $sendtoFriend) {
                    $sendCredittoFriend += $sendtoFriend->getCredit();
                    $earnCredit = $sendtoFriend->getCredit();
                    $sendtofriendpoint = $sendtoFriend->getCredit();
                    $sendbyFriendname = $sendtoFriend->getSenderName();
                    $custTotalWalletCredit = $customer->getWalletCredit();
                    $totalCreditAmount = $custTotalWalletCredit + $earnCredit;
                    $condition = ($maxAllowCredit != 0 && $totalCreditAmount > $maxAllowCredit);
                    $newCredit = $condition ? $maxAllowCredit - $custTotalWalletCredit : $earnCredit;
                    if ($newCredit > 0) {
                        $newtotalCreditAmount = $custTotalWalletCredit + $newCredit;
                        $customer->setWalletCredit($newtotalCreditAmount)->save();
                        $transactionData = [];
                        $transactionData["credit_get"] = $sendtofriendpoint;
                        $transactionData["credit_spent"] = 0;
                        $transTitle = "Get Credit From " . $sendbyFriendname;
                        $this->dataHelper->saveTransaction($customer_id, $transTitle, $transactionData);
                    }
                    $this->deleteSendCredittoFriend($sendtoFriend);
                }
            }
        }
        // Earn Reward Point(s) while customer create an account
        if ($enable_reward && $allowEarnNewAccount == 1) {
            if ($newAccEarnRewardPoint > 0 && $newAccEarnRewardPoint != '') {
                $customernew = $this->customerFactory->create()->load($customer_id);
                $walletCredit = $customernew->getWalletCredit();
                $oneRewardPointCost = $this->dataHelper->getOneRewardPointCost();
                $addWalletCredit = $newAccEarnRewardPoint / $oneRewardPointCost;
                $totalWalletCredit = $walletCredit + $addWalletCredit;
                $condition = ($maxAllowCredit != 0 && $totalWalletCredit > $maxAllowCredit);
                $getNewCredit = $condition ? $maxAllowCredit - $walletCredit : $addWalletCredit;
                if ($getNewCredit > 0) {
                    $transactionData = [];
                    $transactionData['reward_point'] = $newAccEarnRewardPoint;
                    $transactionData["credit_get"] = $getNewCredit;
                    $transactionData["credit_spent"] = 0;
                    $transTitle = "Earn Reward Point(s) by Create a New Account";
                    $this->dataHelper->saveTransaction($customer_id, $transTitle, $transactionData);
                    $newWalletCredit = $walletCredit + $getNewCredit;
                    $customernew->setWalletCredit($newWalletCredit)->save();
                }
                $totalEarnCredit = $getNewCredit + $sendCredittoFriend;
                $this->messageManager->addSuccess(
                    __(
                        'Congratulations! You have received %1 Credit.',
                        $totalEarnCredit
                    )
                );
            }
        }
        // Inviter get Reward Point(s) by invite friend registration
        if ($enable_reward && $earnInviteFriRegi == 1) {
            $custTotalInviteRegi = 0;
            $inviteFriRegiRewardPoint = $this->dataHelper->getInvitedFriendRegiRewardPoint();
            $configInviteLimit = $this->dataHelper->getInviteFriendRegiLimit();
            $inviteFriendColl = $this->inviteFriendFactory->create()->getCollection()
                ->addFieldToFilter('receiver_email', $customer_email)
                ->setOrder(
                    'invite_date',
                    'desc'
                );
            $inviteFriendData = $inviteFriendColl->getFirstItem()->getData();
            if ($inviteFriendData) {
                $customerId = $inviteFriendData['customer_id'];
                $configInviteLimit = ($configInviteLimit == '') ? 0 : $configInviteLimit;
                $alreadyRegisterd = $this->inviteFriendFactory->create()->getCollection()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('status', 1);
                $custTotalInviteRegi = count($alreadyRegisterd->getData());
                $customer = $this->customerFactory->create()->load($customerId);
                $custWalletCredit = $customer->getWalletCredit();
                $oneRewardPointCost = $this->dataHelper->getOneRewardPointCost();
                $addWalletCredit = $inviteFriRegiRewardPoint / $oneRewardPointCost;
                $totalWalletCredit = $custWalletCredit + $addWalletCredit;

                $condition = ($maxAllowCredit != 0 && $totalWalletCredit > $maxAllowCredit);
                $inviNewAccEarnCredit = $condition ? $maxAllowCredit - $custWalletCredit : $addWalletCredit;
                if ($inviNewAccEarnCredit > 0 && ($configInviteLimit == 0 ||
                    ($configInviteLimit != 0 && $custTotalInviteRegi < $configInviteLimit))) {
                    $model = $this->inviteFriendFactory->create()
                        ->load($inviteFriendData['invite_id']);
                    $model->setStatus(1)->save();

                    $transactionData = [];
                    $transactionData['reward_point'] = $inviteFriRegiRewardPoint;
                    $transactionData["credit_get"] = $inviNewAccEarnCredit;
                    $transactionData["credit_spent"] = 0;
                    $transTitle = "Get Reward Point(s) by Invite Friend " .
                        $inviteFriendData['receiver_name'] . " registered";
                    $this->dataHelper->saveTransaction($customerId, $transTitle, $transactionData);
                    $newWalletCredit = $custWalletCredit + $inviNewAccEarnCredit;
                    $customer->setWalletCredit($newWalletCredit)->save();
                }
            }
        }

        // Transaction subscribe while create an account
        $this->transactionHelper->transactionSubscriberSave(
            $customer_id,
            $customerName,
            $customer_email
        );
    }

    /**
     * [deleteSendCredittoFriend Delete send credit data after invite friend registration]
     * @param  [type] $sendtoFriend [description]
     * @return [type]               [description]
     */
    private function deleteSendCredittoFriend($sendtoFriend)
    {
        $sendtoFriend->delete();
    }
}
