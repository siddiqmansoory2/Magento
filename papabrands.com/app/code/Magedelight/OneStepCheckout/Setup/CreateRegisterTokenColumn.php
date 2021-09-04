<?php

namespace Magedelight\OneStepCheckout\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class CreateRegisterTokenColumn
 * @package Magedelight\OneStepCheckout\Setup
 */
class CreateRegisterTokenColumn
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
        $tableArray = ['quote'];
        foreach ($tableArray as $table) {
            $setup->getConnection()->addColumn(
                $setup->getTable($table),
                'mdosc_registration_token',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => '128',
                    'comment' => 'Registartion Token For the Guest User',
                ]
            );
        }
        return $setup;
    }
}
