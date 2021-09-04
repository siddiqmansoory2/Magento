<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


namespace Amasty\MegaMenuLite\Api\Data\Menu;

interface LinkInterface
{
    const TABLE_NAME = 'amasty_menu_link';

    const PERSIST_NAME = 'amasty_megamenu_link';

    const ENTITY_ID = 'entity_id';
    const LINK = 'link';
    const TYPE = 'link_type';

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return \Amasty\MegaMenuLite\Api\Data\Menu\LinkInterface
     */
    public function setEntityId($entityId);

    /**
     * @return string
     */
    public function getLink();

    /**
     * @param string $link
     *
     * @return self
     */
    public function setLink($link);

    /**
     * @return mixed
     */
    public function getLinkType();

    /**
     * @param int $linkType
     * @return void
     */
    public function setLinkType(int $linkType);
}
