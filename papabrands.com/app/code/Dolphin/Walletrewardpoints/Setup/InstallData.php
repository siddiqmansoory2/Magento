<?php

namespace Dolphin\Walletrewardpoints\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory as CustomerFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    protected $customerSetupFactory;

    private $attributeSetFactory;

    public function __construct(
        CustomerFactory $customerFactory,
        AttributeSetFactory $attributeSetFactory,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->customerFactory = $customerFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(Customer::ENTITY, 'wallet_credit', [
            'type' => 'decimal',
            'label' => 'Wallet Credit',
            'input' => 'text',
            'required' => false,
            'visible' => false,
            'source' => '',
            'backend' => '',
            'validate_rules' => '{"validate-number": true, "validate-greater-than-zero": true}',
            'user_defined' => false,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => true,
            'is_searchable_in_grid' => false,
            'position' => 1000,
            'default' => 0,
            'system' => 0,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'wallet_credit')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],
            ]);

        $attribute->save();
        $customerData = $this->customerFactory->create()->getCollection();
        foreach ($customerData as $customer) {
            $cust = $this->customerFactory->create()->load($customer->getId());
            if ($cust->getWalletCredit() == null) {
                $cust->setWalletCredit(0);
                $cust->getResource()->saveAttribute($cust, 'wallet_credit');
            }
        }
    }
}
