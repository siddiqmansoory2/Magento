<?php
namespace Magevalue\Bigin\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
 
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $connection = $setup->getConnection();
            
            $connection->changeColumn(
                $setup->getTable('magevalue_bigin_deal'),
                'bigin_customer_id',
                'bigin_customer_id',
                [
                    'type'     => Table::TYPE_BIGINT,
                    'length'   => 255,
                    'nullable' => true,
                    'default' => null,
                    'comment'  => 'Bigin Customer ID'
                ]
            );

            $connection->changeColumn(
                $setup->getTable('magevalue_bigin_deal'),
                'bigin_deal_id',
                'bigin_deal_id',
                [
                    'type'     => Table::TYPE_BIGINT,
                    'length'   => 255,
                    'nullable' => true,
                    'default' => null,
                    'comment'  => 'Bigin Deal ID'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('magevalue_bigin_deal'),
                'isdealupdated',
                [
                    'type'     => Table::TYPE_INTEGER,
                    'length'   => 10,
                    'nullable' => true,
                    'default' => 0,
                    'comment'  => 'Is Deal Updated'
                ]
            );
        }
    }
}