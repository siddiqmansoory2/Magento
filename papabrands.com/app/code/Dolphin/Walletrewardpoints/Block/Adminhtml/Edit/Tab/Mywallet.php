<?php

namespace Dolphin\Walletrewardpoints\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Customer\Model\Customer;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Registry;

class Mywallet extends \Magento\Framework\View\Element\Template
{
    protected $coreRegistry;

    public function __construct(
        Context $context,
        Registry $registry,
        Customer $customer,
        FormKey $formKey,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->customer = $customer;
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    public function getCustomerId()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    public function getCustomerWalletCredit($id)
    {
        return $this->customer->load($id)->getWalletCredit();
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
