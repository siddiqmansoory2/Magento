<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenu
 */


namespace Amasty\MegaMenu\Api\Data\Menu;

use Amasty\MegaMenuLite\Api\Data\Menu\LinkInterface as LinkInterfaceLite;

interface LinkInterface extends LinkInterfaceLite
{
    const PAGE_ID = 'page_id';

    /**
     * @return mixed
     */
    public function getPageId();

    /**
     * @param int $pageId
     *
     * @return void
     */
    public function setPageId(int $pageId);
}
