<?php

namespace Magedelight\OneStepCheckout\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var CreateDeliveryDateTable
     */
    private $createDeliveryDateTable;

    /**
     * @var CreateExtraFeeColumn
     */
    private $createExtraFeeColumn;

    /**
     * @var CreateRegisterTokenColumn
     */
    private $createRegisterTokenColumn;

    /**
     * UpgradeSchema constructor.
     * @param CreateDeliveryDateTable $createDeliveryDateTable
     * @param CreateExtraFeeColumn $createExtraFeeColumn
     * @param CreateRegisterTokenColumn $createRegisterTokenColumn
     */
    public function __construct(
        CreateDeliveryDateTable $createDeliveryDateTable,
        CreateExtraFeeColumn $createExtraFeeColumn,
        CreateRegisterTokenColumn $createRegisterTokenColumn
    ) {
        $this->createDeliveryDateTable = $createDeliveryDateTable;
        $this->createExtraFeeColumn = $createExtraFeeColumn;
        $this->createRegisterTokenColumn = $createRegisterTokenColumn;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->createDeliveryDateTable->execute($setup);
            $this->createExtraFeeColumn->execute($setup);
            $this->createRegisterTokenColumn->execute($setup);
        }

        $setup->endSetup();
    }
}
