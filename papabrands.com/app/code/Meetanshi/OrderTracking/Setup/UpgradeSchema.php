<?php

namespace Meetanshi\OrderTracking\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    protected $resourceConfig;

    public function __construct(
        ConfigInterface $resourceConfig)
    {
        $this->resourceConfig = $resourceConfig;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $installer = $setup;
            $installer->startSetup();

            if (version_compare($context->getVersion(), '1.0.4', '<')) {

                $table = $installer->getConnection()->newTable($installer->getTable('meetanshi_custom_carrier'))
                    ->addColumn(
                        'id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        11,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'Primary key'
                    )
                    ->addColumn(
                        'title',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        null,
                        ['nullable' => false],
                        'Carrier Title'
                    )
                    ->addColumn(
                        'url',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        null,
                        ['nullable' => false],
                        'Carrier Url'
                    )
                    ->addColumn(
                        'active',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        20,
                        ['unsigned' => true, 'nullable' => false],
                        'active'
                    )
                    ->addIndex(
                        $installer->getIdxName(
                            'meetanshi_custom_carrier',
                            [
                                'title',
                                'url'
                            ],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                        ),
                        [
                            'title',
                            'url'
                        ],
                        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT]);
                $installer->getConnection()->createTable($table);
            }
            $setup->endSetup();

        } catch (\Exception $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info($e->getMessage());
        }
    }
}
