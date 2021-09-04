<?php

namespace Dolphin\Walletrewardpoints\Model;

use Dolphin\Walletrewardpoints\Model\ResourceModel\Withdraw as WithdrawResourceModel;
use Magento\Framework\Model\AbstractModel;

class Withdraw extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(WithdrawResourceModel::class);
    }
}
