<?php

namespace Dolphin\Walletrewardpoints\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Withdraw extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('dolphin_customer_withdraw_credit', 'withdraw_id');
    }
}
