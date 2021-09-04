<?php

namespace Dolphin\Walletrewardpoints\Block\Customer;

use Dolphin\Walletrewardpoints\Helper\Data;
use Dolphin\Walletrewardpoints\Model\TransactionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Element\Template\Context;

class SendCredittoFriend extends \Magento\Framework\View\Element\Template
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
        $this->_isScopePrivate = true;
        parent::__construct(
            $context
        );
    }

    /**
     * [getFormKey Form key for send credit to friend form]
     * @return [type] [description]
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
