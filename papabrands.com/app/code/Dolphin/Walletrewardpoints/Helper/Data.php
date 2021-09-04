<?php

namespace Dolphin\Walletrewardpoints\Helper;

use Dolphin\Walletrewardpoints\Model\Sales\Total\CreditDiscountFactory;
use Dolphin\Walletrewardpoints\Model\SendCredittoFriendFactory;
use Dolphin\Walletrewardpoints\Model\TransactionFactory;
use Dolphin\Walletrewardpoints\Model\Withdraw;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const ENABLE_WALLET_EXTENSION = 'walletreward/wallet/enable';

    const ALLOW_TO_BUY_CREDIT = 'walletreward/wallet/credit_usages/buy_credit';
    const MAX_ALLOWED_CREDIT_FOR_CUSTOMER = 'walletreward/wallet/credit_usages/max_credit_for_customer';
    const USE_CREDIT_WITH_COUPON = 'walletreward/wallet/credit_usages/credit_with_coupons';

    const ALLOW_TO_WITHDRAWAL = 'walletreward/wallet/withdraw/allow_withdrawal';
    const MINIMUN_WITHDRAW_CREDIT = 'walletreward/wallet/withdraw/min_withdraw';
    const WITHDRAW_SENDER_NAME = 'walletreward/wallet/withdraw/withdraw_email_sender';
    const WITHDRAW_EMAIL_TEMPLATE = 'walletreward/wallet/withdraw/withdraw_email_template';

    const ALLOW_SEND_CREDIT = 'walletreward/wallet/sendtofriend/allow_send_credit';
    const SEND_TO_FRIEND_EMAIL_SENDER_NAME = 'walletreward/wallet/sendtofriend/stf_email_sender';
    const SEND_TO_FRIEND_EMAIL_TEMPLATE = 'walletreward/wallet/sendtofriend/sendtofriend_emailtemp';
    const UNREGISTER_FRIEND_EMAIL_TEMPLATE = 'walletreward/wallet/sendtofriend/sendto_unregisterfriend_emailtemp';

    const ALLOW_USE_MAX_CREDIT_PER_ORDER = 'walletreward/wallet/order/max_credit_per_order';
    const ALLOW_MAX_CREDIT_PER_ORDER = 'walletreward/wallet/order/allow_max_credit_per_order';
    const PERCENTAGE_OF_ORDER_SUBTOTAL = 'walletreward/wallet/order/percentage_of_order_subtotal';

    const ALLOW_REFUND_CREDIT = 'walletreward/wallet/refund/refund_credit';

    const ENABLE_REWARD = 'walletreward/reward/enable_reward';
    const ONE_REWARD_POINT_COST = 'walletreward/reward/one_point_cost';

    const ENABLE_ON_CREATE_ACCOUNT = 'walletreward/reward/earn_reward/customer_registration/enable_on_create_account';
    const CUSTOMER_REGI_REWARD_POINT = 'walletreward/reward/earn_reward/customer_registration/ca_reward_point';

    const ENABLE_CREATING_ORDER = 'walletreward/reward/earn_reward/creating_order/enable_create_order';
    const MIN_ORDERED_QTY = 'walletreward/reward/earn_reward/creating_order/min_order_qty';
    const MIN_ORDER_TOTAL = 'walletreward/reward/earn_reward/creating_order/min_order_total';
    const CREATING_ORDER_EARN_TYPE = 'walletreward/reward/earn_reward/creating_order/earn_type';
    const CREATING_ORDER_REWARD_POINT = 'walletreward/reward/earn_reward/creating_order/co_reward_point';
    const MAX_REWARD_PER_ORDER = 'walletreward/reward/earn_reward/creating_order/max_reward_per_order';
    const CREATING_ORDER_MAX_ORDER = 'walletreward/reward/earn_reward/creating_order/co_max_order';
    const REWARD_POINT_MESSAGE_ON_PRODUCT = 'walletreward/reward/earn_reward/creating_order/reward_message';

    const ENABLE_NEWSLETTER_SUBSCRIBERS =
        'walletreward/reward/earn_reward/newsletter_subscribers/enable_newsletter_subscribers';
    const NEWSLETTER_SUBSCRIBERS_REWARD_POINT =
        'walletreward/reward/earn_reward/newsletter_subscribers/nl_reward_point';

    const ENABLE_CUSTOMER_REVIEW = 'walletreward/reward/earn_reward/customer_review/enable_customer_review';
    const CUSTOMER_REVIEW_REWARD_POINT = 'walletreward/reward/earn_reward/customer_review/cr_reward_point';
    const MAX_CUSTOMER_REVIEW_LIMIT = 'walletreward/reward/earn_reward/customer_review/cr_max_review';

    const EARN_INVITED_FRIEND_REGI = 'walletreward/reward/earn_reward/invited_friend_registration/enable_ifr';
    const INVITED_FRIEND_REGI_LIMIT = 'walletreward/reward/earn_reward/invited_friend_registration/ifr_limit';
    const INVITED_FRIEND_REGI_REWARD_POINT =
        'walletreward/reward/earn_reward/invited_friend_registration/ifr_reward_point';
    const INVITE_FRIEND_EMAIL_SENDER_NAME =
        'walletreward/reward/earn_reward/invited_friend_registration/inv_email_sender';
    const INVITE_FRIEND_EMAIL_TEMPLATE =
        'walletreward/reward/earn_reward/invited_friend_registration/invitefriendemailtemp';

    const CREATE_ORDER_BY_INVITE_FRIEND =
        'walletreward/reward/earn_reward/invited_friend_registration/creating_order_by_iv/enable_coif';
    const INVITE_ORDER_EARN_TYPE =
        'walletreward/reward/earn_reward/invited_friend_registration/creating_order_by_iv/coiv_earn_type';
    const INVITE_ORDER_REWARD_POINT =
        'walletreward/reward/earn_reward/invited_friend_registration/creating_order_by_iv/coif_reward_point';
    const INVITE_MAX_ORDER_LIMIT =
        'walletreward/reward/earn_reward/invited_friend_registration/creating_order_by_iv/coif_max_order';

    const NEWSLETTER_SUBSCRIPTION_CONFIRM = 'newsletter/subscription/confirm';

    const TRANSACTION_SUBSCRIBE_EMAIL_SENDER = 'walletreward/transaction/sub_email_sender';
    const TRANSACTION_SUBSCRIBE = 'walletreward/transaction/transaction_subscribe';
    const TRANSACTION_UNSUBSCRIBE_EMAIL_SENDER = 'walletreward/transaction/unsub_email_sender';
    const TRANSACTION_UNSUBSCRIBE = 'walletreward/transaction/transaction_unsubscribe';

    private $store_id;
    private $storeManager;

    /**
     * [__construct Initialize dependencies]
     * @param Context                   $context                   [description]
     * @param Withdraw                  $withdraw                  [description]
     * @param SendCredittoFriendFactory $sendCredittoFriendFactory [description]
     * @param SessionFactory            $sessionFactory            [description]
     * @param UrlInterface              $urlInterface              [description]
     * @param TransactionFactory        $transactionFactory        [description]
     * @param StoreManagerInterface     $storeManager              [description]
     * @param CustomerFactory           $customerFactory           [description]
     * @param CurrencyFactory           $currencyFactory           [description]
     * @param Http                      $response                  [description]
     * @param CreditDiscountFactory     $creditDiscountModel       [description]
     */
    public function __construct(
        Context $context,
        Withdraw $withdraw,
        SendCredittoFriendFactory $sendCredittoFriendFactory,
        SessionFactory $sessionFactory,
        UrlInterface $urlInterface,
        TransactionFactory $transactionFactory,
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        CurrencyFactory $currencyFactory,
        Http $response,
        CreditDiscountFactory $creditDiscountModel
    ) {
        $this->withdraw = $withdraw;
        $this->sessionFactory = $sessionFactory;
        $this->urlInterface = $urlInterface;
        $this->sendCredittoFriend = $sendCredittoFriendFactory;
        $this->transactionFactory = $transactionFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->currencyFactory = $currencyFactory;
        $this->response = $response;
        $this->creditDiscountModel = $creditDiscountModel;
        parent::__construct($context);
    }

    /**
     * [checkForLogin Check is logged-in]
     */
    public function checkForLogin()
    {
        $this->response->setNoCacheHeaders();
        $customerSession = $this->sessionFactory->create();
        if (!$customerSession->isLoggedIn()) {
            $customerSession->setAfterAuthUrl($this->urlInterface->getCurrentUrl());
            $customerSession->authenticate();
        } else {
            if (!$this->getIsEnableWalletExtension()) {
                $this->response->setRedirect($this->urlInterface->getUrl('customer/account'));
            }
        }
    }

    /**
     * [getIsLoggedIn Is customer logged in]
     * @return [boolean] [0/1]
     */
    public function getIsLoggedIn()
    {
        $customerSession = $this->sessionFactory->create();
        if ($customerSession->isLoggedIn()) {
            return 1;
        }
        return 0;
    }

    /**
     * [getCustomerIdFromSession Get customer id from session]
     * @return [int] [customer id]
     */
    public function getCustomerIdFromSession()
    {
        $customerSession = $this->sessionFactory->create();
        if ($customerSession->isLoggedIn()) {
            return $customerSession->getCustomer()->getId();
        }
    }

    /**
     * [getCustomerNameFromSession Get customer name from session]
     * @return [varchar] [customer name]
     */
    public function getCustomerNameFromSession()
    {
        $customerSession = $this->sessionFactory->create();
        if ($customerSession->isLoggedIn()) {
            return $customerSession->getCustomer()->getName();
        }
    }

    /**
     * [getCustomerEmailFromSession Get customer Email from session]
     * @return [varchar] [customer email]
     */
    public function getCustomerEmailFromSession()
    {
        $customerSession = $this->sessionFactory->create();
        if ($customerSession->isLoggedIn()) {
            return $customerSession->getCustomer()->getEmail();
        }
    }

    /**
     * [saveTransaction Save credit transaction of logged in customer]
     * @param  [type] $custId          [description]
     * @param  [type] $transTitle      [description]
     * @param  [type] $transactionData [description]
     */
    public function saveTransaction($custId, $transTitle, $transactionData)
    {
        $transaction = $this->transactionFactory->create();
        $transactionData['customer_id'] = $custId;
        $transactionData["trans_title"] = $transTitle;
        $trans_date = date("Y-m-d H:i:s");
        $transactionData["trans_date"] = $trans_date;
        $transaction->setData($transactionData);
        $transaction->save();
    }

    /**
     * [saveFriendCredit Save friend details if not register and customer send credit]
     * @param  [type] $friendEmail  [description]
     * @param  [type] $addCredit    [description]
     * @param  [type] $custFullname [description]
     * @return [type]               [description]
     */
    public function saveFriendCredit($friendEmail, $addCredit, $custFullname)
    {
        $sendCredittoFriend = $this->sendCredittoFriend->create();
        $friendData["friend_email"] = $friendEmail;
        $friendData["credit"] = $addCredit;
        $friendData["sender_name"] = $custFullname;
        $sendCredittoFriend->setData($friendData);
        $sendCredittoFriend->save();
    }

    /**
     * [getStoreId Get store id]
     * @return [int] [Store id]
     */
    public function getStoreId()
    {
        if ($this->store_id === null) {
            $this->store_id = $this->storeManager->getStore()->getId();
        }
        return $this->store_id;
    }

    /**
     * [getIsEnableWalletExtension Get is wallet extension enable]
     * @return [boolean] [0/1]
     */
    public function getIsEnableWalletExtension()
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_WALLET_EXTENSION,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getMinWithdrawCredit Get minimum withdraw value form system config]
     * @return [type] [description]
     */
    public function getMinWithdrawCredit()
    {
        return $this->scopeConfig->getValue(
            self::MINIMUN_WITHDRAW_CREDIT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getAllowToBuyCredit Get allow to buy credit for customer]
     * @return [boolean] [0/1]
     */
    public function getAllowToBuyCredit()
    {
        return $this->scopeConfig->getValue(
            self::ALLOW_TO_BUY_CREDIT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getAllowToBuyCredit Get allow to withdraw credit for customer]
     * @return [boolean] [0/1]
     */
    public function getAllowToWithdrawal()
    {
        return $this->scopeConfig->getValue(
            self::ALLOW_TO_WITHDRAWAL,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getWithdrawalSenderName Get withdraw email sender name]
     * @return [type] [description]
     */
    public function getWithdrawalSenderName()
    {
        return $this->scopeConfig->getValue(
            self::WITHDRAW_SENDER_NAME,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getWithdrawSenderDetails Get email sender name and email]
     * @return [type] [description]
     */
    public function getWithdrawSenderDetails()
    {
        $name = $this->getWithdrawalSenderName();
        $sender['name'] = $this->scopeConfig->getValue(
            'trans_email/ident_' . $name . '/name',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $sender['email'] = $this->scopeConfig->getValue(
            'trans_email/ident_' . $name . '/email',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        return $sender;
    }

    /**
     * [getWithdrawalEmailTemplate Get withdraw email template]
     * @return [type] [description]
     */
    public function getWithdrawalEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::WITHDRAW_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getWalletCredit Get customer wallet credit]
     * @param  [int] $customer_id [Customer Id]
     * @return [decimal]              [Return current wallet credit]
     */
    public function getWalletCredit($customer_id)
    {
        $custWalletCredit = $this->customerFactory->create()->load($customer_id)->getWalletCredit();
        $custWalletCredit = $this->creditDiscountModel->create()->convertPrice($custWalletCredit, 0);
        return number_format((float) $custWalletCredit, 2, '.', '');
    }

    /**
     * [getCurrencySymbol Get current store currency symbol]
     * @return [type] [symbol of currency]
     */
    public function getCurrencySymbol()
    {
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->currencyFactory->create()->load($currencyCode);
        return $currency->getCurrencySymbol();
    }

    /**
     * [getCustomerMaxCredit Get customer max credit limit from system config]
     * @return [int] [Max wallet credit]
     */
    public function getCustomerMaxCredit()
    {
        return $this->scopeConfig->getValue(
            self::MAX_ALLOWED_CREDIT_FOR_CUSTOMER,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getUseCreditWithCoupon Use credit with coupon]
     * @return [boolean] [0/1]
     */
    public function getUseCreditWithCoupon()
    {
        return $this->scopeConfig->getValue(
            self::USE_CREDIT_WITH_COUPON,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getInviteFriendEmailTemplate invite friend email template]
     * @return [type] [description]
     */
    public function getInviteFriendEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::INVITE_FRIEND_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getInviteFriendSenderDetails Get invite friend sender details]
     * @return [array] [Sender Name and email]
     */
    public function getInviteFriendSenderDetails()
    {
        $name = $this->getInviteFriendEmailSenderName();
        $sender['name'] = $this->scopeConfig->getValue(
            'trans_email/ident_' . $name . '/name',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $sender['email'] = $this->scopeConfig->getValue(
            'trans_email/ident_' . $name . '/email',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        return $sender;
    }

    /**
     * [getInviteFriendEmailSenderName Get email sender name]
     * @return [type] [description]
     */
    public function getInviteFriendEmailSenderName()
    {
        return $this->scopeConfig->getValue(
            self::INVITE_FRIEND_EMAIL_SENDER_NAME,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getRegisteredFriEmailTemplate Send credit to friend email template]
     * @return [type] [description]
     */
    public function getRegisteredFriEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::SEND_TO_FRIEND_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getSendToFriSenderDetails Get email sender details]
     * @return [array] [Sender Name and email]
     */
    public function getSendToFriSenderDetails()
    {
        $name = $this->getSendToFriEmailSenderName();
        $sender['name'] = $this->scopeConfig->getValue(
            'trans_email/ident_' . $name . '/name',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $sender['email'] = $this->scopeConfig->getValue(
            'trans_email/ident_' . $name . '/email',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        return $sender;
    }

    /**
     * [getSendToFriEmailSenderName Get email sender name]
     * @return [type] [description]
     */
    public function getSendToFriEmailSenderName()
    {
        return $this->scopeConfig->getValue(
            self::SEND_TO_FRIEND_EMAIL_SENDER_NAME,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getGuestUserEmailTemplate Guest User email template while send credit]
     * @return [type] [description]
     */
    public function getGuestUserEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::UNREGISTER_FRIEND_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getAllowSendCredit Get allow to send credit to friend]
     * @return [type] [description]
     */
    public function getAllowSendCredit()
    {
        return $this->scopeConfig->getValue(
            self::ALLOW_SEND_CREDIT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getAllowUseMaxCreditOrder Get allow to use maximum credit]
     * @return [boolean] [0/1]
     */
    public function getAllowUseMaxCreditOrder()
    {
        return $this->scopeConfig->getValue(
            self::ALLOW_USE_MAX_CREDIT_PER_ORDER,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getMaxAllowCreditOrder Get maximum allow to use credit]
     * @return [text] [description]
     */
    public function getMaxAllowCreditOrder()
    {
        return $this->scopeConfig->getValue(
            self::ALLOW_MAX_CREDIT_PER_ORDER,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getPerceOfOrderSubtotal Use max percentage of order subtotal credit]
     * @return [text] [description]
     */
    public function getPerceOfOrderSubtotal()
    {
        return $this->scopeConfig->getValue(
            self::PERCENTAGE_OF_ORDER_SUBTOTAL,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getAllowRefundCredit Get refund credit allow or not]
     * @return [boolean] [0/1]
     */
    public function getAllowRefundCredit()
    {
        return $this->scopeConfig->getValue(
            self::ALLOW_REFUND_CREDIT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getAdminAllowRefundCredit Get refund credit allow or not]
     * @return [boolean] [0/1]
     */
    public function getAdminAllowRefundCredit($storeId)
    {
        return $this->scopeConfig->getValue(
            self::ALLOW_REFUND_CREDIT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * [getEnableReward Get reward module enable]
     * @return [type] [description]
     */
    public function getEnableReward()
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_REWARD,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getEnableOnCreateAccount Get is enable to earn reward when create account]
     * @return [type] [description]
     */
    public function getEnableOnCreateAccount()
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_ON_CREATE_ACCOUNT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getCustomerRegiRewardPoint Get reward when customer registration]
     * @return [type] [description]
     */
    public function getCustomerRegiRewardPoint()
    {
        return $this->scopeConfig->getValue(
            self::CUSTOMER_REGI_REWARD_POINT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getOneRewardPointCost Get one reward point cost from configuration]
     * @return [type] [description]
     */
    public function getOneRewardPointCost()
    {
        return $this->scopeConfig->getValue(
            self::ONE_REWARD_POINT_COST,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getEarnInvitedFriendRegi earn if invited friend registration]
     * @return [type] [description]
     */
    public function getEarnInvitedFriendRegi()
    {
        return $this->scopeConfig->getValue(
            self::EARN_INVITED_FRIEND_REGI,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getInvitedFriendRegiRewardPoint Get reward point]
     * @return [type] [description]
     */
    public function getInvitedFriendRegiRewardPoint()
    {
        return $this->scopeConfig->getValue(
            self::INVITED_FRIEND_REGI_REWARD_POINT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getInviteFriendRegiLimit Invite friend registration limit to get reward]
     * @return [type] [description]
     */
    public function getInviteFriendRegiLimit()
    {
        return $this->scopeConfig->getValue(
            self::INVITED_FRIEND_REGI_LIMIT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getCreateOrderByInviteFriend Create order by invited friend enable]
     * @return [type] [description]
     */
    public function getCreateOrderByInviteFriend()
    {
        return $this->scopeConfig->getValue(
            self::CREATE_ORDER_BY_INVITE_FRIEND,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getInviteMaxOrderLimit Invite friend max order limit to get reward to inviter]
     * @return [type] [description]
     */
    public function getInviteMaxOrderLimit()
    {
        return $this->scopeConfig->getValue(
            self::INVITE_MAX_ORDER_LIMIT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getInviteOrderRewardPoint Get reward point to inviter by invite friend order]
     * @return [type] [description]
     */
    public function getInviteOrderRewardPoint()
    {
        return $this->scopeConfig->getValue(
            self::INVITE_ORDER_REWARD_POINT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getInviteOrderEarnType Earn type while invite friend place an order]
     * @return [type] [description]
     */
    public function getInviteOrderEarnType()
    {
        return $this->scopeConfig->getValue(
            self::INVITE_ORDER_EARN_TYPE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getEnableCreatingOrder Enable on creating order]
     * @return [boolean] [0/1]
     */
    public function getEnableCreatingOrder()
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_CREATING_ORDER,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getMinOrderTotal Get min order total to get reward]
     * @return [boolean] [0/1]
     */
    public function getMinOrderTotal()
    {
        return $this->scopeConfig->getValue(
            self::MIN_ORDER_TOTAL,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getCreatingOrderEarnType Get earn type of creating order]
     * @return [boolean] [0/1]
     */
    public function getCreatingOrderEarnType()
    {
        return $this->scopeConfig->getValue(
            self::CREATING_ORDER_EARN_TYPE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getCreatingOrderRewardPoint Reward point while creating order]
     * @return [boolean] [0/1]
     */
    public function getCreatingOrderRewardPoint()
    {
        return $this->scopeConfig->getValue(
            self::CREATING_ORDER_REWARD_POINT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getCreatingOrderMaxOrder Max. no. of order to earn reward]
     * @return [boolean] [0/1]
     */
    public function getCreatingOrderMaxOrder()
    {
        return $this->scopeConfig->getValue(
            self::CREATING_ORDER_MAX_ORDER,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getDisplayRewardPointOnProduct Display reward point message]
     * @return [boolean] [0/1]
     */
    public function getDisplayRewardPointOnProduct()
    {
        return $this->scopeConfig->getValue(
            self::REWARD_POINT_MESSAGE_ON_PRODUCT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getMaxRewardPerOrder Get maximum allow reward while creating order]
     * @return [boolean] [0/1]
     */
    public function getMaxRewardPerOrder()
    {
        return $this->scopeConfig->getValue(
            self::MAX_REWARD_PER_ORDER,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getMinOrderedQty Get minimum ordered qty to get reward]
     * @return [boolean] [0/1]
     */
    public function getMinOrderedQty()
    {
        return $this->scopeConfig->getValue(
            self::MIN_ORDERED_QTY,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getEnableNewsletterSub get enable newsletter subscription]
     * @return [boolean] [0/1]
     */
    public function getEnableNewsletterSub()
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_NEWSLETTER_SUBSCRIBERS,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getNewsletterSubRewardPoint Reward point while newsletter subscription]
     * @return [boolean] [0/1]
     */
    public function getNewsletterSubRewardPoint()
    {
        return $this->scopeConfig->getValue(
            self::NEWSLETTER_SUBSCRIBERS_REWARD_POINT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getNewsletterSubConfirm Enable newsletter subsription confirm]
     * @return [boolean] [0/1]
     */
    public function getNewsletterSubConfirm()
    {
        return $this->scopeConfig->getValue(
            self::NEWSLETTER_SUBSCRIPTION_CONFIRM,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getEnableCustomerReview Enable get reward after customer review config]
     * @return [boolean] [0/1]
     */
    public function getEnableCustomerReview()
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_CUSTOMER_REVIEW,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getCustomerReviewRewardPoint Reward point get after customer review]
     * @return [int] [reward point]
     */
    public function getCustomerReviewRewardPoint()
    {
        return $this->scopeConfig->getValue(
            self::CUSTOMER_REVIEW_REWARD_POINT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getMaxCustomerReviewLimit Max customer review limit to get reward]
     * @return [int] [review limit]
     */
    public function getMaxCustomerReviewLimit()
    {
        return $this->scopeConfig->getValue(
            self::MAX_CUSTOMER_REVIEW_LIMIT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getTransactionSubEmailTemplate Transaction subscription email template]
     * @return [type] [description]
     */
    public function getTransactionSubEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::TRANSACTION_SUBSCRIBE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getSubSenderDetails Get subscribe sender details]
     * @return [array] [sender name and email]
     */
    public function getSubSenderDetails()
    {
        $name = $this->getSubEmailSenderName();
        $sender['name'] = $this->scopeConfig->getValue(
            'trans_email/ident_' . $name . '/name',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $sender['email'] = $this->scopeConfig->getValue(
            'trans_email/ident_' . $name . '/email',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        return $sender;
    }

    /**
     * [getSubEmailSenderName Get email sender name]
     * @return [type] [description]
     */
    private function getSubEmailSenderName()
    {
        return $this->scopeConfig->getValue(
            self::TRANSACTION_SUBSCRIBE_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getTransactionUnsubEmailTemplate Transaction unsubscription email template]
     * @return [type] [description]
     */
    public function getTransactionUnsubEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::TRANSACTION_UNSUBSCRIBE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * [getUnsubSenderDetails Get unsubscribe sender details]
     * @return [array] [sender name and email]
     */
    public function getUnsubSenderDetails()
    {
        $name = $this->getUnsubEmailSenderName();
        $sender['name'] = $this->scopeConfig->getValue(
            'trans_email/ident_' . $name . '/name',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $sender['email'] = $this->scopeConfig->getValue(
            'trans_email/ident_' . $name . '/email',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        return $sender;
    }

    /**
     * [getUnsubEmailSenderName Get email sender name]
     * @return [text] [description]
     */
    private function getUnsubEmailSenderName()
    {
        return $this->scopeConfig->getValue(
            self::TRANSACTION_UNSUBSCRIBE_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }
}
