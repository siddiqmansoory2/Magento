<?php

namespace Dolphin\Walletrewardpoints\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InviteFriend extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('dolphin_customer_invite_friend', 'invite_id');
    }
}
