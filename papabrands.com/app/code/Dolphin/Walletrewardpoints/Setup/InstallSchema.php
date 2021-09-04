<?php

namespace Dolphin\Walletrewardpoints\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    // @codingStandardsIgnoreStart
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        // @codingStandardsIgnoreEnd
        $installer = $setup;
        $installer->startSetup();
        // dolphin_customer_wallet_transaction_history
        if (!$installer->tableExists('dolphin_customer_wallet_transaction_history')) {
            $tableName = $installer->getTable('dolphin_customer_wallet_transaction_history');
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'transaction_id',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                    ],
                    'Transaction Id'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_TEXT,
                    32,
                    [
                        'nullable' => true,
                        'default' => null,
                    ],
                    'Order Id'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => true,
                    ],
                    'Customer Id'
                )
                ->addColumn(
                    'trans_title',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                        'default' => null,
                    ],
                    'Transaction Title'
                )
                ->addColumn(
                    'reward_point',
                    Table::TYPE_DECIMAL,
                    '10,2',
                    [
                        'nullable' => true,
                        'default' => 0,
                    ],
                    'Reward Point'
                )
                ->addColumn(
                    'credit_get',
                    Table::TYPE_DECIMAL,
                    '10,2',
                    [
                        'nullable' => true,
                        'default' => 0,
                    ],
                    'Credit Get'
                )
                ->addColumn(
                    'credit_spent',
                    Table::TYPE_DECIMAL,
                    '10,2',
                    [
                        'nullable' => true,
                        'default' => 0,
                    ],
                    'Credit Spent'
                )
                ->addColumn(
                    'trans_date',
                    Table::TYPE_DATETIME,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Credit Transaction Date'
                )
                ->addIndex(
                    $installer->getIdxName('dolphin_customer_wallet_transaction_history', ['transaction_id']),
                    ['transaction_id']
                )
                ->setComment('Dolphin Customer Wallet Transaction History')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        if (!$installer->tableExists('dolphin_customer_withdraw_credit')) {
            $tableName = $installer->getTable('dolphin_customer_withdraw_credit');
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'withdraw_id',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                    ],
                    'Withdraw Id'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'Customer Id'
                )
                ->addColumn(
                    'credit',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => true,
                        'default' => null,
                    ],
                    'Credit'
                )
                ->addColumn(
                    'paypal_email',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                        'default' => null,
                    ],
                    'Paypal Email'
                )
                ->addColumn(
                    'reason',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                        'default' => null,
                    ],
                    'Reason'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'nullable' => true,
                        'default' => null,
                    ],
                    'Status'
                )
                ->addColumn(
                    'requested_date',
                    Table::TYPE_DATETIME,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Withdraw Requested Date'
                )
                ->addColumn(
                    'updated_date',
                    Table::TYPE_DATETIME,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Withdraw Updated Date'
                )
                ->addIndex(
                    $installer->getIdxName('dolphin_customer_withdraw_credit', ['withdraw_id']),
                    ['withdraw_id']
                )
                ->setComment('Dolphin Customer Withdraw Credit')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
            $installer->getConnection()
                ->addForeignKey(
                    $installer->getFkName(
                        $tableName,
                        'customer_id',
                        'customer_entity',
                        'entity_id'
                    ),
                    $tableName,
                    'customer_id',
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                );
        }
        if (!$installer->tableExists('dolphin_credit_sendtofriend')) {
            $tableName = $installer->getTable('dolphin_customer_invite_friend');
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'invite_id',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                    ],
                    'Invite Id'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'Customer Id'
                )
                ->addColumn(
                    'receiver_name',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                    ],
                    'Receiver Name'
                )
                ->addColumn(
                    'receiver_email',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                    ],
                    'Receiver Email'
                )
                ->addColumn(
                    'message',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Message'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Status'
                )
                ->addColumn(
                    'invite_date',
                    Table::TYPE_DATETIME,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Invite Date'
                )
                ->addIndex(
                    $installer->getIdxName('dolphin_customer_invite_friend', ['invite_id']),
                    ['invite_id']
                )
                ->setComment('Dolphin Customer Invite Friend')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
            $installer->getConnection()
                ->addForeignKey(
                    $installer->getFkName(
                        $tableName,
                        'customer_id',
                        'customer_entity',
                        'entity_id'
                    ),
                    $tableName,
                    'customer_id',
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                );
        }
        if (!$installer->tableExists('dolphin_credit_sendtofriend')) {
            $tableName = $installer->getTable('dolphin_credit_sendtofriend');
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                    ],
                    'Entity Id'
                )
                ->addColumn(
                    'friend_email',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                    ],
                    'Friend Email'
                )
                ->addColumn(
                    'credit',
                    Table::TYPE_DECIMAL,
                    '10,2',
                    [
                        'nullable' => true,
                        'default' => 0,
                    ],
                    'Credit Send'
                )
                ->addColumn(
                    'sender_name',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                    ],
                    'Sender Name'
                )
                ->addIndex(
                    $installer->getIdxName('dolphin_credit_sendtofriend', ['entity_id']),
                    ['entity_id']
                )
                ->setComment('Dolphin Credit Send to Friend')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        if (!$installer->tableExists('dolphin_transaction_subscriber')) {
            $tableName = $installer->getTable('dolphin_transaction_subscriber');
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'subscriber_id',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                    ],
                    'Subscriber Id'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    10,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                    ],
                    'Customer ID'
                )
                ->addColumn(
                    'subscriber_email',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => true,
                        'default' => null,
                    ],
                    'Subscriber Email'
                )
                ->addColumn(
                    'subscriber_status',
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'nullable' => true,
                        'default' => 0,
                        'unsigned' => false,
                    ],
                    'Subscriber Status'
                )
                ->addColumn(
                    'subscribe_date',
                    Table::TYPE_DATETIME,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Subscribe Date'
                )
                ->addIndex(
                    $installer->getIdxName($tableName, ['subscriber_id']),
                    ['subscriber_id']
                )
                ->setComment('Dolphin Transaction Subscriber')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
            $installer->getConnection()
                ->addForeignKey(
                    $installer->getFkName(
                        $tableName,
                        'customer_id',
                        'customer_entity',
                        'entity_id'
                    ),
                    $tableName,
                    'customer_id',
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                );
        }
        $installer->endSetup();
    }
}
