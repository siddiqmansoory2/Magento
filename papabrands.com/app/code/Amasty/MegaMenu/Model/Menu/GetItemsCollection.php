<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenu
 */


declare(strict_types=1);

namespace Amasty\MegaMenu\Model\Menu;

use Amasty\MegaMenu\Model\ResourceModel\Menu\ResourceResolver;
use Amasty\MegaMenu\Api\Data\Menu\LinkInterface;
use Amasty\MegaMenuLite\Model\Menu\GetItemsCollection as GetItemsCollectionAliasLite;
use Amasty\MegaMenuLite\Model\ResourceModel\Menu\Item\Collection as ItemCollection;
use Amasty\MegaMenuLite\Model\ResourceModel\Menu\Item\CollectionFactory as ItemCollectionFactory;
use Magento\Framework\Data\Collection;
use Magento\Store\Model\Store;

class GetItemsCollection extends GetItemsCollectionAliasLite
{
    /**
     * @var ResourceResolver
     */
    private $resourceResolver;

    public function __construct(
        ItemCollectionFactory $collectionFactory,
        ResourceResolver $resourceResolver
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resourceResolver = $resourceResolver;
    }

    public function execute(int $storeId): ItemCollection
    {
        /** @var ItemCollection $collection */
        $collection = $this->collectionFactory->create();
        $collection->getSelect()->joinLeft(
            ['links' => $collection->getTable(LinkInterface::TABLE_NAME)],
            'main_table.entity_id = links.entity_id AND main_table.type = \'custom\'',
            ['link_type', 'page_id']
        );
        $collection->addFieldToFilter('store_id', [$storeId, Store::DEFAULT_STORE_ID]);
        $collection->addOrder('store_id', Collection::SORT_ORDER_ASC);

        $this->resourceResolver->joinLink($collection, 'links', 'url');

        return $collection;
    }
}
