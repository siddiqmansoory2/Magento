<?php

namespace Dolphin\Walletrewardpoints\Model;

use Dolphin\Walletrewardpoints\Api\WalletRewardPointsTransactionInterface;
use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Dolphin\Walletrewardpoints\Helper\Transaction as TransactionHelper;
use Dolphin\Walletrewardpoints\Model\Config\Source\WalletStoreConfigValues;
use Dolphin\Walletrewardpoints\Model\InviteFriendFactory;
use Dolphin\Walletrewardpoints\Model\Sales\Total\CreditDiscount;
use Dolphin\Walletrewardpoints\Model\Subscriber;
use Dolphin\Walletrewardpoints\Model\Transaction as TransactionModel;
use Dolphin\Walletrewardpoints\Model\Withdraw;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Io\File as IoFiles;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\View\Asset\Repository as AssetRepo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;

class WalletRewardPointsTransaction implements WalletRewardPointsTransactionInterface
{
    private $scopeConfig;

    protected $customerRegistry;
    protected $customer;

    public function __construct(
        WalletStoreConfigValues $sourceData,
        ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutSession,
        CatalogSession $catalogSession,
        CustomerModel $customerModel,
        IoFiles $files,
        DirectoryList $directoryList,
        AssetRepo $assetRepo,
        ProductModel $product,
        QuoteFactory $quote,
        CartRepositoryInterface $cartRep,
        Subscriber $subscriberFactory,
        TransactionModel $transModel,
        Withdraw $withdraw,
        StateInterface $stateInterface,
        CreditDiscount $creditDiscountModel,
        TransportBuilder $transportBuilder,
        DataHelper $dataHelper,
        TransactionHelper $transactionHelper,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManagerInterface,
        InviteFriendFactory $inviteFriendFactory,
        CustomerRegistry $customerRegistry,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        Session $customer
    ) {
        $this->sourceData = $sourceData;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->priceHelper = $priceHelper;
        $this->catalogSession = $catalogSession;
        $this->customerModel = $customerModel;
        $this->file = $files;
        $this->directoryList = $directoryList;
        $this->assetRepo = $assetRepo;
        $this->product = $product;
        $this->quote = $quote;
        $this->cartRep = $cartRep;
        $this->subscriberFactory = $subscriberFactory;
        $this->transModel = $transModel;
        $this->withdraw = $withdraw;
        $this->stateInterface = $stateInterface;
        $this->creditDiscountModel = $creditDiscountModel;
        $this->transportBuilder = $transportBuilder;
        $this->dataHelper = $dataHelper;
        $this->transactionHelper = $transactionHelper;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManagerInterface;
        $this->inviteFriendFactory = $inviteFriendFactory;
        $this->customerRegistry = $customerRegistry;
        $this->customer = $customer;
    }

    /**
     * POST Invite To Friend Save Customer Data
     * @param string[] $data
     * @return array $returnData
     */
    public function inviteToFriendSave($data)
    {
        $returnData = [];
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return "Email is invalid. Please enter valid email address.";
        }
        try {
            $customerId = $data['customer_id'];
            $customerModel = $this->customerRegistry->retrieve($data['customer_id']);
            $inviteFriendModel = $this->inviteFriendFactory->create();
            $receiverEmail = $data['email'];
            $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($websiteId);
            $customer->loadByEmail($receiverEmail);
            $isInvited = $inviteFriendModel->getCollection()
                ->addFieldToFilter('receiver_email', $receiverEmail)
                ->addFieldToFilter('customer_id', $customerId);
            if ($customer->getData()) {
                $showMsg = $receiverEmail . ' is already registered.';
                $returnData[] = [
                    'status' => "FAILED",
                    'message' => $showMsg,
                ];
                return $returnData;
            } elseif ($isInvited->getData()) {
                $showMsg = $receiverEmail . ' is already invited.';
                $returnData[] = [
                    'status' => "FAILED",
                    'message' => $showMsg,
                ];
                return $returnData;
            } else {
                $currentDate = date("Y-m-d H:i:s");
                $inviteFriendData = [];
                $inviteFriendData['customer_id'] = $customerId;
                $receiverFullname = $data['firstname'] . " " . $data['lastname'];
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
                    $url = $this->storeManager->getStore()->getUrl('customer/account/create');
                    $senderName = $customerModel->getFirstname() . " " . $customerModel->getLastname();
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
                        $showMsg = $e->getMessage();
                        $returnData[] = [
                            'status' => "FAILED",
                            'message' => $showMsg,
                        ];
                        return $returnData;
                    }
                }
                $showMsg = "Invitation sent successfully to " . $receiverFullname . " " . $receiverEmail . ".";
                $returnData[] = [
                    'status' => "SUCCESS",
                    'message' => $showMsg,
                ];
                return $returnData;
            }
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('The specified customer does not exist.'));
        } catch (Exception $e) {
            throw new LocalizedException(__('Something is wrong, Please try latter.'));
        }
    }

    /**
     * GET Invite To Friend Customer Data
     * @param int $customerid
     * @return \Dolphin\Walletrewardpoints\Model\InviteFriendFactory
     */
    public function inviteSendToFriendData($customerid)
    {
        try {
            $inviteFriendModel = $this->inviteFriendFactory->create();
            $isInvited = $inviteFriendModel->getCollection()
                ->addFieldToFilter('customer_id', $customerid);
            if (count($isInvited->getData())) {
                return $isInvited->getData();
            } else {
                $showMsg = (__("You have no invite friend."));
                $returnData[] = [
                    'status' => "FAILED",
                    'message' => $showMsg,
                ];
                return $returnData;
            }
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('The specified customer does not exist.'));
        } catch (Exception $e) {
            throw new LocalizedException(__('Something is wrong, Please try latter.'));
        }
    }

    /**
     * POST Invite To Friend Save Customer Data
     * @param string[] $data
     * @return array $returnData
     */
    public function sendCreditToFriend($data)
    {
        $returnData = [];
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return "Email is invalid. Please enter valid email address.";
        }

        try {
            $customerId = $data['customer_id'];
            $customerModel = $this->customerRegistry->retrieve($data['customer_id']);
            $walletCredit = $customerModel->getWalletCredit();
            $allowToSendCredit = $this->dataHelper->getAllowSendCredit();
            $maxAllowCredit = $this->dataHelper->getCustomerMaxCredit();
            if ($maxAllowCredit == '') {
                $maxAllowCredit = 0;
            }
            if ($allowToSendCredit != 1) {
                $showMsg = 'Sorry you can not allow to send credit to friend.';
                $returnData[] = [
                    'status' => "FAILED",
                    'message' => $showMsg,
                ];
                return $returnData;
            }
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $friendEmail = $data['email'];
            $customer = $this->customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($friendEmail);
            $customerId = $customer->getId();
            $addCredit = $data['credit'];
            $addCredit = $this->creditDiscountModel->convertPrice($addCredit, 1);
            $friendWalletCredit = ($customerId) ? $customer->getWalletCredit() : 0;
            $totalCredit = $friendWalletCredit + $addCredit;
            if (($maxAllowCredit != 0) && ($totalCredit > $maxAllowCredit)) {
                $qty = $maxAllowCredit - $friendWalletCredit;
                $sendCredit = ($qty > 0) ? $qty : 0;
                $showMsg = 'You can send maximum ' . $sendCredit . ' credit.';
                $returnData[] = [
                    'status' => "FAILED",
                    'message' => $showMsg,
                ];
                return $returnData;
            } else {
                $customer_id = $data['customer_id'];
                $senderCustomer = $this->customerFactory->create()->load($customer_id);
                $senderCredit = $senderCustomer->getWalletCredit() - $addCredit;
                if ($senderCredit <= 0) {
                    $showMsg = 'Insufficient balance into your wallet credit.';
                    $returnData[] = [
                        'status' => "FAILED",
                        'message' => $showMsg,
                    ];
                    return $returnData;
                }
                $senderCustomer->setWalletCredit($senderCredit)->save();
                $transactionData = [];
                $transactionData["credit_get"] = $addCredit;
                $transactionData["credit_spent"] = 0;
                $custFullname = $customerModel->getFirstname() . " " . $customerModel->getLastname();
                $receiverFullname = $data['firstname'] . " " . $data['lastname'];
                $transTitle = "Send Credit to " . $receiverFullname . ' (' . $friendEmail . ')';
                $this->dataHelper->saveTransaction($customer_id, $transTitle, $transactionData);
                if ($customerId) {
                    $friendCredit = $customer->getWalletCredit() + $addCredit;
                    $customer->setWalletCredit($friendCredit)->save();
                    $transactionData = [];
                    $transactionData["credit_get"] = $addCredit;
                    $transactionData["credit_spent"] = 0;
                    $custEmail = $customerModel->getEmail();
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
                        $showMsg = 'Something went wrong while sending email.';
                        $returnData[] = [
                            'status' => "FAILED",
                            'message' => $showMsg,
                        ];
                        return $returnData;
                    }
                }
                // Send transaction email to customer end
                $showMsg = 'Credit sent successfully to ' . $receiverFullname . '.';
                $returnData[] = [
                    'status' => "SUCCESS",
                    'message' => $showMsg,
                ];
                return $returnData;
            }
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('The specified customer does not exist.'));
        } catch (Exception $e) {
            throw new LocalizedException(__('Something is wrong, Please try latter.'));
        }
    }

    /**
     * POST Wallet Customer Credit Withdraw
     * @param string[] $data
     * @return array
     */
    public function withdrawCustomerCredit($data)
    {
        $returnData = [];
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return "Email is invalid. Please enter valid email address.";
        }
        try {
            $customerId = $data['customer_id'];
            $customerModel = $this->customerRegistry->retrieve($data['customer_id']);
            $walletCredit = $customerModel->getWalletCredit();
            $withdralCredit = $walletCredit - $data['credit'];
            if ($withdralCredit <= 0) {
                $showMsg = 'Insufficient balance into your wallet credit.';
                $returnData[] = [
                    'status' => "FAILED",
                    'message' => $showMsg,
                ];
                return $returnData;
            }
            $withdrawData['status'] = 0;
            $withdrawData['requested_date'] = date("Y-m-d H:i:s");
            $withdrawData['updated_date'] = date("Y-m-d H:i:s");
            $withdrawData['customer_id'] = $data['customer_id'];
            $withdrawData['paypal_email'] = $data['email'];
            $withdrawData['reason'] = $data['reason'];
            $withdrawData['credit'] = $this->creditDiscountModel->convertPrice($data['credit'], 1);
            $model = $this->withdraw->setData($withdrawData);
            $model->save();
            $showMsg = 'Your withdraw request submitted successfully. We will review your request and accept it as soon as possible.';
            $returnData[] = [
                'status' => "SUCCESS",
                'message' => $showMsg,
            ];
            return $returnData;
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('The specified customer does not exist.'));
        } catch (Exception $e) {
            throw new LocalizedException(__('Something is wrong, Please try latter.'));
        }
    }

    /**
     * GET Wallet Credit Show Withdraw
     * @param int $customerid
     * @return \Dolphin\Walletrewardpoints\Model\WithdrawFactory
     */
    public function showCustomerWithdraw($customerid)
    {
        try {
            $withdrawData = $this->withdraw->getCollection()
                ->addFieldToFilter('customer_id', $customerid);
            if (count($withdrawData->getData())) {
                return $withdrawData->getData();
            } else {
                $showMsg = (__("You have no withdraw history."));
                $returnData[] = [
                    'status' => "FAILED",
                    'message' => $showMsg,
                ];
                return $returnData;
            }
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('The specified customer does not exist.'));
        } catch (Exception $e) {
            throw new LocalizedException(__('Something is wrong, Please try latter.'));
        }
    }

    /**
     * GET Wallet Credit Show Withdraw
     * @param int $customerid
     * @return \Dolphin\Walletrewardpoints\Model\WithdrawFactory
     */
    public function showTransactionsHistory($customerid)
    {
        try {
            $transData = $this->transModel->getCollection()
                ->addFieldToFilter('customer_id', $customerid);
            if (count($transData->getData())) {
                return $transData->getData();
            } else {
                $showMsg = (__("You have no transaction history."));
                $returnData[] = [
                    'status' => "FAILED",
                    'message' => $showMsg,
                ];
                return $returnData;
            }
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('The specified customer does not exist.'));
        } catch (Exception $e) {
            throw new LocalizedException(__('Something is wrong, Please try latter.'));
        }
    }

    /**
     * POST Customer Wallet Reward Email Subscription
     * @param string[] $data
     * @return array
     */
    public function setWalletEmailSubscription($data)
    {
        $subscriptionStatus = 0;
        if (isset($data['subscription'])) {
            if ($data['subscription']) {
                $subscriptionStatus = 1;
            }
        }
        try {
            $customerId = $data['customer_id'];
            $isSubscribedParam = $subscriptionStatus;
            $customerModel = $this->customerRegistry->retrieve($customerId);
            $isSubscribedCustomer = $this->subscriberFactory->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->getFirstItem();
            if (!$isSubscribedCustomer->getData() || ($isSubscribedParam != $isSubscribedCustomer->getSubscriberStatus())) {
                $custEmail = $customerModel->getEmail();
                $date = date("Y-m-d H:i:s");
                $subscribeData = [
                    'customer_id' => $customerId,
                    'subscriber_email' => $custEmail,
                    'subscriber_status' => $subscriptionStatus,
                    'subscribe_date' => $date,
                ];
                if ($isSubscribedCustomer->getData()) {
                    $subscriber_id = $isSubscribedCustomer->getSubscriberId();
                    $subscribeData['subscriber_id'] = $subscriber_id;
                    unset($subscribeData['subscribe_date']);
                }
                $this->subscriberFactory->transactionSubscribeSave($subscribeData);
                if ($subscriptionStatus) {
                    // Send subscription email to customer
                    //$this->transactionHelper->transactionSubscription();
                    $showMsg = (__('We have saved your subscription.'));
                    $returnData[] = [
                        'status' => "SUBSCRIBED",
                        'message' => $showMsg,
                    ];
                    return $returnData;
                } else {
                    // Send unsubscription email to customer
                    //$this->transactionHelper->transactionUnsubscription();
                    $showMsg = (__('We have removed your transaction subscription.'));
                    $returnData[] = [
                        'status' => "UNSUBSCRIBED",
                        'message' => $showMsg,
                    ];
                    return $returnData;
                }
            } else {
                $showMsg = (__('We have updated your subscription.'));
                if ($subscriptionStatus) {
                    $status = "SUBSCRIBED";
                } else {
                    $status = "UNSUBSCRIBED";
                }
                $returnData[] = [
                    'status' => $status,
                    'message' => $showMsg,
                ];
                return $returnData;
            }
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('The specified customer does not exist.'));
        } catch (Exception $e) {
            throw new LocalizedException(__('Something is wrong, Please try latter.'));
        }
    }

    /**
     * GET Customer Wallet Reward Email Subscription Status
     * @param int $customerid
     * @return array
     */
    public function getWalletEmailSubscriptionStatus($customerid)
    {
        try {
            $customerModel = $this->customerRegistry->retrieve($customerid);
            $collection = $this->subscriberFactory->getCollection();
            $collection->addFieldToFilter('customer_id', $customerid);
            $emailStatus = "UNSUBSCRIBED";
            $customerEmail = $customerModel->getEmail();
            if (count($collection->getData())) {
                foreach ($collection as $key => $value) {
                    if ($value->getSubscriberStatus()) {
                        $emailStatus = "SUBSCRIBED";
                        break;
                    }
                }
            }
            $returnData[] = [
                'status' => $emailStatus,
                'email' => $customerEmail,
            ];
            return $returnData;
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('The specified customer does not exist.'));
        } catch (Exception $e) {
            throw new LocalizedException(__('Something is wrong, Please try latter.'));
        }
    }

    /**
     * POST Wallet Reward Buy Credit
     * @param string[] $data
     * @return array
     */
    public function WithdrawawCustomerCredit($data)
    {
        $data['result'] = "fail";
        $qty = $data["credit"];
        $cid = $data['customer_id'];
        try {
            $customernew = $this->customerModel->load($cid);
            $walletCredit = $customernew->getWalletCredit();
            $customerMaxCredit = $this->dataHelper->getCustomerMaxCredit();
            if ($customerMaxCredit && ($walletCredit + $qty) > $customerMaxCredit) {
                $showMsg = 'Your maximum credit limit is ' . $customerMaxCredit . '.';
                $returnData[] = [
                    'status' => "FAILED",
                    'message' => $showMsg,
                ];
                return $returnData;
            }
            $product_sku = 'rewardpoints';
            if (!$this->product->getIdBySku($product_sku)) {
                try {
                    $imagePath = $this->assetRepo->getUrl('Dolphin_Walletrewardpoints::images/coin.png');
                    $tmpDir = $this->directoryList->getPath('media') . DIRECTORY_SEPARATOR . 'tmp';
                    $this->file->checkAndCreateFolder($tmpDir);
                    $imgFileInfo = $this->file->getPathInfo($imagePath);
                    $imgBasename = $imgFileInfo['basename'];
                    $newFileName = $tmpDir . $imgBasename;
                    $this->file->read($imagePath, $newFileName);
                    $this->product
                        ->setWebsiteIds([1])
                        ->setAttributeSetId(4)
                        ->setTypeId('virtual')
                        ->setCreatedAt(strtotime('now'))
                        ->setSku('rewardpoints')
                        ->setName('Credit')
                        ->setStatus(1)
                        ->setTaxClassId(0)
                        ->setPrice(1)
                        ->setMetaTitle('Credit')
                        ->setMetaKeyword('Credit')
                        ->setMetaDescription('Credit')
                        ->setDescription('Credit')
                        ->setShortDescription('Credit')
                        ->setMediaGallery(['images' => [], 'values' => []])
                        ->addImageToMediaGallery(
                            $newFileName,
                            ['image', 'thumbnail', 'small_image'],
                            false,
                            false
                        )
                        ->setStockData([
                            'use_config_manage_stock' => 0,
                            'manage_stock' => 0,
                            'min_sale_qty' => 1,
                            'is_in_stock' => 1,
                            'qty' => 9999,
                        ]);
                    $this->product->save();
                    $this->file->rm($newFileName);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $showMsg = 'Something went wrong while creating credit product.';
                    $returnData[] = [
                        'status' => "FAILED",
                        'message' => $showMsg,
                    ];
                    return $returnData;
                }
            }
            $id = $this->product->getIdBySku($product_sku);
            if ($qty > 0) {
                try {
                    $product = $this->product->load($id);
                    $customerQuote = $this->cartRep->getActiveForCustomer($cid);
                    $quoteId = $customerQuote->getId();
                    $cart = $this->cartRep->getActive($quoteId);
                    $cart->addProduct($product, $qty);
                    $cart->save();
                    $this->cartRep->save($cart->collectTotals());
                    $showMsg = $qty . ' credit has been added into your cart.';
                    $returnData[] = [
                        'status' => "SUCCESS",
                        'message' => $showMsg,
                    ];
                    return $returnData;
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $showMsg = 'Something went wrong while adding product to cart.';
                    $returnData[] = [
                        'status' => "FAILED",
                        'message' => $showMsg,
                    ];
                    return $returnData;
                }
            } else {
                $showMsg = 'Please enter credit greater than 0.';
                $returnData[] = [
                    'status' => "FAILED",
                    'message' => $showMsg,
                ];
                return $returnData;
            }

        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('The specified customer does not exist.'));
        } catch (Exception $e) {
            throw new LocalizedException(__('Something is wrong, Please try latter.'));
        }
    }

    /**
     * PUT Apply Wallete Credit To Cart
     * @param int $cartId The cart ID.
     * @param int $credit wallet credit
     * @return array
     */
    public function applyWalleteCredit($cartId, $credit)
    {
        if (!is_int($credit) || $credit <= 0) {
            throw new LocalizedException(__('Specify the credit Int value greater then zero.'));
        }
        try {
            $allowCreditCoupon = $this->dataHelper->getMaxAllowCreditOrder();
            if (!is_int($credit) || $credit >= $allowCreditCoupon) {
                $allowCreditCoupon = $this->priceHelper->currency($allowCreditCoupon, true, false);
                $showMsg = "Please enter a value less than or equal to " . $allowCreditCoupon . ". Maximum redeemable credit(s) are " . $allowCreditCoupon . ".";
                $returnData[] = [
                    'status' => "FAILED",
                    'message' => $showMsg,
                ];
                return $returnData;
            }
            $inputCredit = $credit;
            $quote = $this->cartRep->getActive($cartId);
            $cartallitems = $quote->getAllItems();
            if (count($cartallitems)) {
                foreach ($cartallitems as $itemId => $item) {
                    if (($item->getSku() == 'rewardpoints') && ($item->getQty() > 0)) {
                        $showMsg = 'Credit is not applied on ' . $item->getName() . ' product.';
                        $returnData[] = [
                            'status' => "FAILED",
                            'message' => $showMsg,
                        ];
                        return $returnData;
                    }
                }
            }
            $allowWithCoupon = $this->dataHelper->getUseCreditWithCoupon();
            $couponCode = $this->checkoutSession->getQuote()->getCouponCode();
            $isCredit = $this->catalogSession->getApplyCredit();
            $isCredit = abs($isCredit);
            if ($isCredit) {
                $showMsg = 'Your credit(s) was already applied.';
                $returnData[] = [
                    'status' => "FAILED",
                    'message' => $showMsg,
                ];
                return $returnData;
            } else {
                if ($allowWithCoupon != 0 || ($allowWithCoupon == 0 && !$couponCode)) {
                    $this->catalogSession->setApplyCredit(-$inputCredit);
                    $quote->save();
                    $showMsg = 'Your credit(s) ' . $inputCredit . ' was successfully applied.';
                    $returnData[] = [
                        'status' => "SUCCESS",
                        'message' => $showMsg,
                    ];
                    return $returnData;
                } else {
                    $showMsg = 'Your credit(s) value is not allow.';
                    $returnData[] = [
                        'status' => "FAILED",
                        'message' => $showMsg,
                    ];
                    return $returnData;
                }
            }
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('The specified customer qoute does not exist.'));
        } catch (Exception $e) {
            throw new LocalizedException(__('Something is wrong, Please try latter.'));
        }
    }

    /**
     * DELETE Remove Wallete Credit From Cart
     * @param int $cartId The cart ID.
     * @return array
     */
    public function removeWalleteCredit($cartId)
    {
        $isCredit = $this->catalogSession->getApplyCredit();
        $isCredit = abs($isCredit);
        if ($isCredit) {
            $this->catalogSession->setApplyCredit(0);
            $showMsg = 'Your credit(s) was successfully canceled.';
            $returnData[] = [
                'status' => "SUCCESS",
                'message' => $showMsg,
            ];
            return $returnData;
        } else {
            $showMsg = 'There are no any credit apply found. Please apply credit first.';
            $returnData[] = [
                'status' => "FAILED",
                'message' => $showMsg,
            ];
            return $returnData;
        }
    }

    /**
     * GET Wallete Store Config Values
     * @return array
     */
    public function getStoreConfig()
    {
        $storeCode = $this->storeManager->getStore()->getCode();
        $storeConfigData = $this->sourceData->getStoreArray();
        $walletStoreConfigData = [];
        foreach ($storeConfigData as $ConfigDataKey => $ConfigDataValue) {
            try {
                $getWalletVal = $this->scopeConfig->getValue(
                    $ConfigDataValue,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
                    $storeCode
                );
            } catch (Exception $e) {
                continue;
            }
            $walletStoreConfigData[$ConfigDataKey] = $getWalletVal;
        }
        $getWalletVal = [];
        $getWalletVal[] = $walletStoreConfigData;
        return $getWalletVal;
    }
}
