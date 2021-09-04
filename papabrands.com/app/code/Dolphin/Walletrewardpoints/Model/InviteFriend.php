<?php

namespace Dolphin\Walletrewardpoints\Model;

use Dolphin\Walletrewardpoints\Model\ResourceModel\InviteFriend as InviteFriendResourceModel;
use Magento\Framework\Model\AbstractModel;

class InviteFriend extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(InviteFriendResourceModel::class);
    }
}
