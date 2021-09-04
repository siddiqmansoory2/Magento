<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


declare(strict_types=1);

namespace Amasty\MegaMenuLite\Model\Menu;

use Amasty\MegaMenuLite\Api\Data\Menu\LinkInterface;
use Amasty\MegaMenuLite\Model\ResourceModel\Menu\Item\Collection as ItemCollection;
use Amasty\MegaMenuLite\Model\ResourceModel\Menu\Item\CollectionFactory as ItemCollectionFactory;
use Magento\Framework\Data\Collection;
use Magento\Store\Model\Store;

class GetItemsCollection
{
    /**
     * @var ItemCollectionFactory
     */
    protected $collectionFactory;

    public function __construct(ItemCollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function execute(int $storeId): ItemCollection
    {
        /** @var ItemCollection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('store_id', [$storeId, Store::DEFAULT_STORE_ID]);
        $collection->addOrder('store_id', Collection::SORT_ORDER_ASC);
        $collection->getSelect()->joinLeft(
            ['links' => $collection->getTable(LinkInterface::TABLE_NAME)],
            'main_table.entity_id = links.entity_id AND main_table.type = \'custom\'',
            ['url' => LinkInterface::LINK, LinkInterface::TYPE]
        );

        return $collection;
    }
}
