<?php

namespace Dolphin\Walletrewardpoints\Model;

use Dolphin\Walletrewardpoints\Model\ResourceModel\Transaction as TransactionResourceModel;
use Magento\Framework\Model\AbstractModel;

class Transaction extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(TransactionResourceModel::class);
    }
}
