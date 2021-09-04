<?php
namespace Papa\Placeorder\Setup;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup,ModuleContextInterface $context)
    { 
		$setup->startSetup();
		
		$setup->getConnection()->addColumn(
			$setup->getTable('sales_invoice'),
			'bluedart_status',
			[
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'length' => 255,
				'nullable' => true,
				'comment' => 'Invoice Status'
			]
		);

		$setup->getConnection()->addColumn( 
		   $setup->getTable('sales_invoice_grid'), 
		   'bluedart_status', 
		   [
			   'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 
			   'length' => 255, 
			   'nullable' => true, 
			   'comment' => 'Invoice Status' 
		   ]
	   );
	   
	   $setup->endSetup();
    }
}