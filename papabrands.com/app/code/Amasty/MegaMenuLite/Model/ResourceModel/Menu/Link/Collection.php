<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


namespace Amasty\MegaMenuLite\Model\ResourceModel\Menu\Link;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Amasty\MegaMenuLite\Api\Data\Menu\LinkInterface;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_setIdFieldName(LinkInterface::ENTITY_ID);
        $this->_init(
            \Amasty\MegaMenuLite\Model\Menu\Link::class,
            \Amasty\MegaMenuLite\Model\ResourceModel\Menu\Link::class
        );
    }
}
