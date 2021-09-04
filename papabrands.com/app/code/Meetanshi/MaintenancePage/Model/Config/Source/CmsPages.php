<?php

namespace Meetanshi\MaintenancePage\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Cms\Model\Page;

class CmsPages implements ArrayInterface
{
    private $cmsPageCollectionFactory;

    public function __construct(
        CollectionFactory $cmsPageCollectionFactory
    ) {
        $this->cmsPageCollectionFactory = $cmsPageCollectionFactory;
    }

    public function toOptionArray()
    {
        $res = [];
        $collection = $this->cmsPageCollectionFactory->create();
        $collection->addFieldToFilter('is_active', Page::STATUS_ENABLED);
        foreach ($collection as $page) {
            $data['value'] = $page->getData('identifier');
            $data['label'] = $page->getData('title');
            $res[] = $data;
        }
        $data['value'] = 'maintenancepage';
        $data['label'] = __('Maintenance Page');
        $res[] = $data;

        return $res;
    }
}
