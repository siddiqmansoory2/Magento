<?php

namespace Meetanshi\MaintenancePage\Controller\Index;

use Meetanshi\MaintenancePage\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    private $helper;

    protected $pageFactory;

    public function __construct(
        Data $helper,
        Context $context,
        PageFactory $pageFactory
    ) {
        $this->helper = $helper;
        $this->pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->pageFactory->create(false, ['isIsolated' => true]);
        $pageConfig = $page->getConfig();
        $pageConfig->setPageLayout($this->helper->getPageLayout());
        $pageConfig->getTitle()->set($this->helper->getPageTitle());
        return $page;
    }
}
