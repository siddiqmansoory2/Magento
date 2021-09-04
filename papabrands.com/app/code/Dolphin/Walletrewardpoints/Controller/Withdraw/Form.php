<?php

namespace Dolphin\Walletrewardpoints\Controller\Withdraw;

class Form extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
