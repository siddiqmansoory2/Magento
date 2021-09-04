<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


namespace Amasty\MegaMenuLite\Model\ResourceModel\Menu;

use Amasty\MegaMenuLite\Api\Data\Menu\ItemInterface;
use Amasty\MegaMenuLite\Api\Data\Menu\LinkInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Link extends AbstractDb
{
    /**
     * Initialize table nad PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(LinkInterface::TABLE_NAME, LinkInterface::ENTITY_ID);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $itemContentTable = $this->getTable(ItemInterface::TABLE_NAME);
        $select->joinInner(
            $itemContentTable,
            sprintf(
                '%s.entity_id = %s.entity_id AND store_id = 0 AND %s.type = "custom"',
                $itemContentTable,
                $this->getMainTable(),
                $itemContentTable
            )
        );

        return $select;
    }
}
