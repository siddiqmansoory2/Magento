<?php

namespace Dolphin\Walletrewardpoints\Model\ResourceModel\SendCredittoFriend;

use Dolphin\Walletrewardpoints\Model\ResourceModel\SendCredittoFriend as SendCredittoFriendResourceModel;
use Dolphin\Walletrewardpoints\Model\SendCredittoFriend as SendCredittoFriendModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            SendCredittoFriendModel::class,
            SendCredittoFriendResourceModel::class
        );
    }
}
