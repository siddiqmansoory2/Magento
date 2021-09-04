<?php
declare (strict_types = 1);

namespace Dolphin\Walletrewardpoints\Model\Resolver\DataProvider;

class GetWalletRewardEmailSubscription
{
    protected $_subscriberFactory;
    protected $_storeManager;
    protected $_walletDataHelper;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Dolphin\Walletrewardpoints\Model\SubscriberFactory $subscriberFactory,
        \Dolphin\Walletrewardpoints\Helper\Data $walletDataHelper
    ) {
        $this->_subscriberFactory = $subscriberFactory;
        $this->_storeManager = $storeManager;
        $this->_walletDataHelper = $walletDataHelper;
    }

    public function getSubscriptionStatus()
    {
        try {
            $collection = [];
            $customerId = $this->_walletDataHelper->getCustomerIdFromSession();
            if ($customerId) {
                $collection = $this->_subscriberFactory->create()->getCollection();
                $collection->addFieldToFilter('customer_id', $customerId);
                if (count($collection)) {
                    foreach ($collection as $emailKey => $emailValue) {
                        $collection = $this->_subscriberFactory->create();
                        $collection->load($emailValue->getSubscriberId());
                        break;
                    }
                }
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $collection;
    }
}
