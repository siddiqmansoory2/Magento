<?php

namespace Dolphin\Walletrewardpoints\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SendCredittoFriend extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('dolphin_credit_sendtofriend', 'entity_id');
    }
}
