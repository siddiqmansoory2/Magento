<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenu
 */


namespace Amasty\MegaMenu\Model\Repository;

use Amasty\MegaMenuLite\Api\ItemRepositoryInterface;
use Amasty\MegaMenu\Model\Menu\ItemFactory;
use Amasty\MegaMenu\Model\ResourceModel\Menu\Item as ItemResource;
use Amasty\MegaMenuLite\Model\ResourceModel\Menu\Item\CollectionFactory;
use Amasty\MegaMenuLite\Model\Repository\ItemRepository as ItemRepositoryLite;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;

class ItemRepository extends ItemRepositoryLite implements ItemRepositoryInterface
{
    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        ItemFactory $itemFactory,
        ItemResource $itemResource,
        CollectionFactory $itemCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->itemFactory = $itemFactory;
        $this->itemResource = $itemResource;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }
}
