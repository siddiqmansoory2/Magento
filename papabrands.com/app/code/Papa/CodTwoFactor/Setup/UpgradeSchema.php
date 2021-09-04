<?php
namespace Papa\CodTwoFactor\Setup;

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
                $setup->getTable('quote'),
                'otp',
                'otp_token',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'comment'  => 'OTP Token'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $connection = $setup->getConnection();
            
            $connection->addColumn(
                $setup->getTable('mgz_faq_question'),
                'customer_orderid',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'comment'  => 'Customer Order ID'
                ]
            );
        }
    }
}