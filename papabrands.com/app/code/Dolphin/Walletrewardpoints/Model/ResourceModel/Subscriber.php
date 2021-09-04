<?php

namespace Dolphin\Walletrewardpoints\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Subscriber extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('dolphin_transaction_subscriber', 'subscriber_id');
    }
}
