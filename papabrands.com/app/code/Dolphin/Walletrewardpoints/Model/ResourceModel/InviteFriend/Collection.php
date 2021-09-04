<?php

namespace Dolphin\Walletrewardpoints\Model\ResourceModel\InviteFriend;

use Dolphin\Walletrewardpoints\Model\InviteFriend as InviteFriendModel;
use Dolphin\Walletrewardpoints\Model\ResourceModel\InviteFriend as InviteFriendResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            InviteFriendModel::class,
            InviteFriendResourceModel::class
        );
    }
}
