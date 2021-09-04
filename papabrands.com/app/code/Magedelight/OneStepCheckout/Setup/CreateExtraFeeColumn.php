<?php
namespace Magedelight\OneStepCheckout\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class CreateExtraFeeColumn
 * @package Magedelight\OneStepCheckout\Setup
 */
class CreateExtraFeeColumn
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
        $tableArray = ['quote','sales_order','sales_invoice','sales_creditmemo'];
        foreach ($tableArray as $table) {
            if ($table == 'quote') {
                $setup->getConnection()->addColumn(
                    $setup->getTable($table),
                    'mdosc_extra_fee_checked',
                    [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => false,
                        'comment' => 'Extra Fee Checked',
                    ]
                );
            }
            $setup->getConnection()->addColumn(
                $setup->getTable($table),
                'mdosc_extra_fee',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '12,4',
                    'default' => '0.0000',
                    'comment' => 'Extra Fee',
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable($table),
                'base_mdosc_extra_fee',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '12,4',
                    'default' => '0.0000',
                    'comment' => 'Base Extra Fee',
                ]
            );
        }
        return $setup;
    }
}
