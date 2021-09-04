<?php
declare (strict_types = 1);

namespace Dolphin\Walletrewardpoints\Model\Resolver\DataProvider;

class GetTransactionalHistory
{
    protected $_transFactory;
    protected $_storeManager;
    protected $_walletDataHelper;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Dolphin\Walletrewardpoints\Model\TransactionFactory $transFactory,
        \Dolphin\Walletrewardpoints\Helper\Data $walletDataHelper
    ) {
        $this->_transFactory = $transFactory;
        $this->_storeManager = $storeManager;
        $this->_walletDataHelper = $walletDataHelper;
    }

    public function getTransactionalHistory($pageSize, $currentPage)
    {
        try {
            $collection = [];
            $customerId = $this->_walletDataHelper->getCustomerIdFromSession();
            if ($customerId) {
                $collection = $this->_transFactory->create()->getCollection();
                $collection->addFieldToFilter('customer_id', $customerId);
                $collection->setOrder('transaction_id', 'desc');
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $collection;
    }
}
