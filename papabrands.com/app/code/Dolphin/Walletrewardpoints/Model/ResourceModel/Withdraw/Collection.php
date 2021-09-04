<?php

namespace Dolphin\Walletrewardpoints\Model\ResourceModel\Withdraw;

use Dolphin\Walletrewardpoints\Model\ResourceModel\Withdraw as WithdrawResourceModel;
use Dolphin\Walletrewardpoints\Model\Withdraw as WithdrawModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            WithdrawModel::class,
            WithdrawResourceModel::class
        );
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $customer_entity = $this->getTable('customer_entity');
        $this->getSelect()->joinLeft(
            ['customer' => $customer_entity],
            'main_table.customer_id = customer.entity_id',
            [
                'customer_fullname' => 'CONCAT(customer.firstname,\' \', customer.lastname)',
                'customer.email',
            ]
        );
    }
}
