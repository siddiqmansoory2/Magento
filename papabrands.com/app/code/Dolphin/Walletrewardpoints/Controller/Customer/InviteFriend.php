<?php

namespace Dolphin\Walletrewardpoints\Controller\Customer;

class InviteFriend extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
