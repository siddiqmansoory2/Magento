<?php
declare (strict_types = 1);

namespace Dolphin\Walletrewardpoints\Model\Resolver\DataProvider;

class GetWithdrawCreditHistory
{
    protected $_withdrawFactory;
    protected $_storeManager;
    protected $_walletDataHelper;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Dolphin\Walletrewardpoints\Model\WithdrawFactory $withdrawFactory,
        \Dolphin\Walletrewardpoints\Helper\Data $walletDataHelper
    ) {
        $this->_withdrawFactory = $withdrawFactory;
        $this->_storeManager = $storeManager;
        $this->_walletDataHelper = $walletDataHelper;
    }

    public function getWithdrawCredit($pageSize, $currentPage)
    {
        try {
            $collection = [];
            $customerId = $this->_walletDataHelper->getCustomerIdFromSession();
            if ($customerId) {
                $collection = $this->_withdrawFactory->create()->getCollection();
                $collection->addFieldToFilter('customer_id', $customerId);
                $collection->setOrder('withdraw_id', 'desc');
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $collection;
    }
}
