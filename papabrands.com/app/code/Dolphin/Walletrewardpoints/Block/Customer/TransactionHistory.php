<?php

namespace Dolphin\Walletrewardpoints\Block\Customer;

use Dolphin\Walletrewardpoints\Helper\Data;
use Dolphin\Walletrewardpoints\Model\TransactionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Element\Template\Context;

class TransactionHistory extends \Magento\Framework\View\Element\Template
{
    protected $transactionCollection;

    public function __construct(
        Context $context,
        FormKey $formKey,
        Session $customerSession,
        TransactionFactory $transactionFactory,
        Data $helper
    ) {
        $this->formKey = $formKey;
        $this->customerSession = $customerSession;
        $this->transactionFactory = $transactionFactory;
        $this->helper = $helper;
        parent::__construct(
            $context
        );
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getTransactionHistory()
    {
        $customer_id = $this->helper->getCustomerIdFromSession();
        if (!$customer_id) {
            return false;
        }
        if (!$this->transactionCollection) {
            $this->transactionCollection = $this->transactionFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $customer_id)
                ->setOrder(
                    'trans_date',
                    'desc'
                );
        }
        return $this->transactionCollection;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getTransactionHistory()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'transaction.history.pager'
            )->setCollection(
                $this->getTransactionHistory()
            );
            $this->setChild('pager', $pager);
            $this->getTransactionHistory()->load();
        }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
