<?php

namespace Dolphin\Walletrewardpoints\Model\ResourceModel\Transaction;

use Dolphin\Walletrewardpoints\Model\ResourceModel\Transaction as TransactionResourceModel;
use Dolphin\Walletrewardpoints\Model\Transaction as TransactionModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            TransactionModel::class,
            TransactionResourceModel::class
        );
    }

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager);
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $customer_id = $this->request->getParam('customer_id');
        if ($customer_id) {
            $this->getSelect()->orWhere("main_table.customer_id = '" . $customer_id . "'");
        }
        $customer_entity = $this->getTable('customer_entity');
        $this->getSelect()->joinLeft(
            ['customer' => $customer_entity],
            'main_table.customer_id = customer.entity_id',
            [
                'customer_fullname' => 'CONCAT(customer.firstname,\' \', customer.lastname)',
            ]
        );
    }
}
