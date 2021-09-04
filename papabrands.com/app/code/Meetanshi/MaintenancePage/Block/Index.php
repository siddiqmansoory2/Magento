<?php

namespace Meetanshi\MaintenancePage\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Meetanshi\MaintenancePage\Helper\Data;
use Meetanshi\MaintenancePage\Model\Config\Source\PageLayout;
use Meetanshi\MaintenancePage\Model\Config\Source\BackgroundType;
use Magento\Store\Model\StoreManagerInterface;

class Index extends Template
{
    private $helper;

    private $pageLayout;

    private $backgroundType;

    private $storeManager;

    public function __construct(
        Context $context,
        Data $helperData,
        PageLayout $pageLayout,
        BackgroundType $backgroundType,
        StoreManagerInterface $storeManager
    ) {
        $this->helper = $helperData;
        $this->pageLayout = $pageLayout;
        $this->backgroundType = $backgroundType;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getHelper()
    {
        return $this->helper;
    }

    public function getPageLayoutModel()
    {
        return $this->pageLayout;
    }

    public function getBackgroundTypeModel()
    {
        return $this->backgroundType;
    }

    public function getStoreUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }
    public function getFormActionUrl()
    {
        return $this->getUrl('newsletter/subscriber/new', ['_secure' => true]);
    }
}