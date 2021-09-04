<?php

namespace Magedelight\OneStepCheckout\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magedelight\OneStepCheckout\Api\RegistrationInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magedelight\OneStepCheckout\Helper\Data as OscHelper;

/**
 * Class Registration
 * @package Magedelight\OneStepCheckout\Model
 */
class Registration implements RegistrationInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMask;

    /**
     * @var EncryptorInterface
     */
    private $encrypt;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var CustomerExtract
     */
    private $customerExtract;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OscHelper
     */
    private $helper;

    /**
     * Registration constructor.
     * @param QuoteIdMaskFactory $quoteIdMask
     * @param LoggerInterface $logger
     * @param QuoteFactory $quoteFactory
     * @param EncryptorInterface $encrypt
     * @param CustomerExtract $customerExtract
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $accountManagement
     * @param EventManagerInterface $eventManager
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMask,
        LoggerInterface $logger,
        QuoteFactory $quoteFactory,
        EncryptorInterface $encrypt,
        CustomerExtract $customerExtract,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManagement,
        EventManagerInterface $eventManager,
        OrderRepositoryInterface $orderRepository,
        OscHelper $helper
    ) {
        $this->quoteIdMask = $quoteIdMask;
        $this->logger = $logger;
        $this->quoteFactory = $quoteFactory;
        $this->encrypt = $encrypt;
        $this->customerExtract = $customerExtract;
        $this->storeManager = $storeManager;
        $this->accountManagement = $accountManagement;
        $this->eventManager = $eventManager;
        $this->orderRepository = $orderRepository;
        $this->helper = $helper;
    }

    /**
     * @param OrderInterface $order
     * @return bool|\Magento\Customer\Api\Data\CustomerInterface|Customer
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function createUser($order)
    {
//        if($this->helper->isRegistrationEnabled()) {
            /** @var Customer $customerData */
            $customerData = $this->customerExtract->extract($order);
            $this->setCustomerInformation($customerData, $order);
        if (!$customerData->getId()
                && $this->accountManagement->isEmailAvailable($customerData->getEmail())
            ) {
            $passwordQuote = $this->getPasswordQuote($order->getQuoteId());
            /** @var Customer $account */
            $account = $this->accountManagement->createAccountWithPasswordHash(
                $customerData,
                $passwordQuote
            );

            $this->eventManager->dispatch(
                'customer_register_success',
                ['customer' => $account, 'md_osc_onestepcheckout_register' => true]
            );

            $order->setCustomerId($account->getId());
            $order->setCustomerIsGuest(0);
            $this->orderRepository->save($order);
            $this->deleteHashToken($order);

            return $account;
        }
//        }
    }

    private function generatePassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890@$';
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;
        $length = 8;
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

    /**
     * @param string $cartId
     * @param string $token
     * @return boolean
     */
    public function saveHashToken($cartId, $token)
    {
        if (!$token && $this->helper->isAutoRegistrationEnabled()) {
            $token = $this->generatePassword();
        }
        if ($token) {
            try {
                /** @var QuoteIdMask $quoteIdMask */
                $quoteIdMask = $this->quoteIdMask->create()->load($cartId, 'masked_id');
                $quote =  $this->quoteFactory->create()->load($quoteIdMask->getQuoteId());
                $hasToken = $this->createHashToken($token);
                $quote->setData('mdosc_registration_token', $hasToken)->save();
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }
        return true;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return bool|void
     */
    public function deleteHashToken($order)
    {
        if ($order) {
            try {
                $quote = $this->quoteFactory->create()->load($order->getQuoteId());
                $quote->setData('mdosc_registration_token', '')->save();
            } catch (\Exception $exception) {
                return true;
            }
        }

        return true;
    }

    /**
     * @param $token
     * @return string
     */
    private function createHashToken($token)
    {
        return $this->encrypt->getHash($token, true);
    }

    /**
     * @param Customer $customerData
     * @param OrderInterface $order
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function setCustomerInformation(Customer $customerData, OrderInterface $order)
    {
        if (!$customerData->getStoreId()) {
            if ($customerData->getWebsiteId()) {
                $storeId = $this->storeManager->getWebsite($customerData->getWebsiteId())->getDefaultStore()->getId();
            } else {
                $this->storeManager->setCurrentStore(null);
                $storeId = $this->storeManager->getStore()->getId();
            }
            $customerData->setStoreId($storeId);
        }
        if (!$customerData->getWebsiteId()) {
            $websiteId = $this->storeManager->getStore($customerData->getStoreId())->getWebsiteId();
            $customerData->setWebsiteId($websiteId);
        }
        if (!$customerData->getTaxvat()) {
            $customerData->setTaxvat($order->getShippingAddress()->getVatId());
        }
    }

    /**
     * @param $quoteId
     * @return mixed
     */
    private function getPasswordQuote($quoteId)
    {
        try {
            $quote =  $this->quoteFactory->create()->load($quoteId);
            $quoteToken = $quote->getData('mdosc_registration_token');
        } catch (NoSuchEntityException $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $quoteToken;
    }
}
