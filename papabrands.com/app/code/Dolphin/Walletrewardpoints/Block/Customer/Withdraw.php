<?php

namespace Dolphin\Walletrewardpoints\Block\Customer;

use Dolphin\Walletrewardpoints\Helper\Data;
use Dolphin\Walletrewardpoints\Model\WithdrawFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Element\Template\Context;

class Withdraw extends \Magento\Framework\View\Element\Template
{
    protected $withdrawCollection;

    public function __construct(
        Context $context,
        FormKey $formKey,
        Session $customerSession,
        WithdrawFactory $withdrawFactory,
        Data $helper
    ) {
        $this->formKey = $formKey;
        $this->customerSession = $customerSession;
        $this->withdrawFactory = $withdrawFactory;
        $this->helper = $helper;
        parent::__construct(
            $context
        );
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getWithdrawRequest()
    {
        $customer_id = $this->helper->getCustomerIdFromSession();
        if (!$customer_id) {
            return false;
        }
        if (!$this->withdrawCollection) {
            $this->withdrawCollection = $this->withdrawFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $customer_id)
                ->setOrder(
                    'requested_date',
                    'desc'
                );
        }
        return $this->withdrawCollection;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getWithdrawRequest()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'withdraw.request.pager'
            )->setCollection(
                $this->getWithdrawRequest()
            );
            $this->setChild('pager', $pager);
            $this->getWithdrawRequest()->load();
        }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
