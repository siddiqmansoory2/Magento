<?php

namespace LitExtension\MigrationToMagento\Controller\Adminhtml\migration;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Index Action*
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
