<?php

namespace Dolphin\Walletrewardpoints\Model;

use Dolphin\Walletrewardpoints\Model\ResourceModel\SendCredittoFriend as SendCredittoFriendResourceModel;
use Magento\Framework\Model\AbstractModel;

class SendCredittoFriend extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(SendCredittoFriendResourceModel::class);
    }
}
