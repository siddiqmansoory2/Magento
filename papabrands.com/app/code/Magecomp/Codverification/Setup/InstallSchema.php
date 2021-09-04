<?php
namespace Magecomp\Codverification\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
		
		//Quote Tabel Field Added
		$connection->addColumn(
			$setup->getTable('quote'),
			'codverification',
			[
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
				'nullable' => false, 
				'default' => '0',
				'comment' => 'COD Status'
			]
		);
		$connection->addColumn($setup->getTable('quote'),
			'otp',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'length' => '10',
				'comment' => 'OTP'
			]
		);
    }
}