<?php
declare (strict_types = 1);

namespace Dolphin\Walletrewardpoints\Model\Resolver\DataProvider;

class GetWalletRewardInviteFriendHistory
{
    protected $_inviteFriendFactory;
    protected $_storeManager;
    protected $_walletDataHelper;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Dolphin\Walletrewardpoints\Model\InviteFriendFactory $inviteFriendFactory,
        \Dolphin\Walletrewardpoints\Helper\Data $walletDataHelper
    ) {
        $this->_inviteFriendFactory = $inviteFriendFactory;
        $this->_storeManager = $storeManager;
        $this->_walletDataHelper = $walletDataHelper;
    }

    public function getInviteFriend($pageSize, $currentPage)
    {
        try {
            $collection = [];
            $customerId = $this->_walletDataHelper->getCustomerIdFromSession();
            if ($customerId) {
                $collection = $this->_inviteFriendFactory->create()->getCollection();
                $collection->addFieldToFilter('customer_id', $customerId);
                $collection->setOrder('invite_id', 'desc');
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $collection;
    }
}
