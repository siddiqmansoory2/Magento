<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


declare(strict_types=1);

namespace Amasty\MegaMenuLite\Model\ResourceModel\Menu\Item\Position;

use Amasty\MegaMenuLite\Api\Data\Menu\ItemInterface;
use Amasty\MegaMenuLite\Api\Data\Menu\LinkInterface;
use Amasty\MegaMenuLite\Model\Menu\Item\Position;
use Amasty\MegaMenuLite\Model\ResourceModel\Menu\Item\Position as PositionResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_setIdFieldName(PositionResource::ID);
        $this->_init(
            Position::class,
            PositionResource::class
        );
    }

    /**
     * @param int $storeId
     * @return Collection
     */
    public function getSortedCollection(int $storeId)
    {
        return $this->addFieldToFilter(PositionResource::STORE_VIEW, $storeId)
            ->addOrder(PositionResource::POSITION, 'asc');
    }

    public function joinLinkTable(): void
    {
        $this->getSelect()->joinLeft(
            ['links' => $this->getTable(LinkInterface::TABLE_NAME)],
            sprintf(
                'main_table.%s = "%s" AND main_table.%s = links.%s',
                ItemInterface::TYPE,
                ItemInterface::CUSTOM_TYPE,
                ItemInterface::ENTITY_ID,
                LinkInterface::ENTITY_ID
            ),
            [LinkInterface::TYPE]
        );
    }

    public function addLinkTypeFilter(array $values): void
    {
        $this->getSelect()->where(
            sprintf(
                'coalesce(%s, 0) IN (%s)',
                LinkInterface::TYPE,
                implode(', ', $values)
            )
        );
    }
}
