<?php

namespace Magedelight\OneStepCheckout\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class CreateDeliveryDateTable
 * @package Magedelight\OneStepCheckout\Setup
 */
class CreateDeliveryDateTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->createTable($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('quote'),
            'md_osc_delivery_date',
            [
                'type' => Table::TYPE_DATE,
                'nullable' => false,
                'comment' => 'Delivery Date',
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('quote'),
            'md_osc_delivery_time',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Delivery Time',
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('quote'),
            'md_osc_delivery_comment',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Delivery Comment',
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'md_osc_delivery_date',
            [
                'type' => Table::TYPE_DATE,
                'nullable' => false,
                'comment' => 'Delivery Date',
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'md_osc_delivery_time',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Delivery Time',
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'md_osc_delivery_comment',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Delivery Comment',
            ]
        );

        return $setup;
    }
}
