<?php
namespace Papa\Placeorder\Setup;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
 
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        
		$setup->startSetup();

        if (version_compare($context->getVersion(), '0.2.0', '<')) {
			$tableName = $setup->getTable('sales_invoice');
			if ($setup->getConnection()->isTableExists($tableName) == true) {

				$columns = [
					'bluedart_status' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'nullable' => true,
						'comment' => 'bluedart_status',
					],
				];
				
				$connection = $setup->getConnection();
				foreach ($columns as $name => $definition) {
					$connection->addColumn($tableName, $name, $definition);
				}		 
			}
			
			$tableName_2 = $setup->getTable('sales_invoice_grid');
			if ($setup->getConnection()->isTableExists($tableName_2) == true) {

				$columns = [
					'bluedart_status' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'nullable' => true,
						'comment' => 'bluedart_status',
					],
				];
				
				$connection = $setup->getConnection();
				foreach ($columns as $name => $definition) {
					$connection->addColumn($tableName_2, $name, $definition);
				}		 
			}
			
			
		}
		
		$setup->endSetup();
		
    }
}