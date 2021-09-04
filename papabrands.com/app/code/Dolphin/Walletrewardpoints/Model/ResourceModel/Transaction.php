<?php

namespace Dolphin\Walletrewardpoints\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Transaction extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('dolphin_customer_wallet_transaction_history', 'transaction_id');
    }
}
