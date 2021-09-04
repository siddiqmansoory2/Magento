<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


declare(strict_types=1);

namespace Amasty\MegaMenuLite\Model\Menu;

use Amasty\MegaMenuLite\Api\Data\Menu\LinkInterface;
use Magento\Framework\Model\AbstractModel;

class Link extends AbstractModel implements LinkInterface
{
    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\MegaMenuLite\Model\ResourceModel\Menu\Link::class);
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->_getData(LinkInterface::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($entityId)
    {
        $this->setData(LinkInterface::ENTITY_ID, $entityId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLink()
    {
        return $this->_getData(LinkInterface::LINK);
    }

    /**
     * @inheritdoc
     */
    public function setLink($link)
    {
        $this->setData(LinkInterface::LINK, $link);

        return $this;
    }

    public function getLinkType(): int
    {
        return (int) $this->_getData(LinkInterface::TYPE);
    }

    public function setLinkType(int $linkType): void
    {
        $this->setData(LinkInterface::TYPE, $linkType);
    }
}
