<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenu
 */


declare(strict_types = 1);

namespace Amasty\MegaMenu\Model\Menu;

use Amasty\MegaMenu\Api\Data\Menu\ItemInterface;
use Amasty\MegaMenu\Model\DataProvider\GetItemContentData;
use Amasty\MegaMenu\Model\OptionSource\SubcategoriesPosition;
use Amasty\MegaMenu\Model\OptionSource\SubmenuType;
use Magento\Catalog\Model\Category;
use Magento\Store\Model\Store;

class Subcategory
{
    const TOP_LEVEL = 2;

    /**
     * @var GetItemContentData
     */
    private $getItemContentData;

    public function __construct(
        GetItemContentData $getItemContentData
    ) {
        $this->getItemContentData = $getItemContentData;
    }

    public function isShowSubcategories(Category $category): bool
    {
        $level = $category->getLevel();
        $entityId = (int) $category->getEntityId();
        $storeId = (int) $category->getStoreId() ?? Store::DEFAULT_STORE_ID;

        $submenuType = $this->getItemContentData->execute(
            ItemInterface::SUBMENU_TYPE,
            $entityId,
            $storeId
        );
        $subcategoriesPosition = $this->getItemContentData->execute(
            ItemInterface::SUBCATEGORIES_POSITION,
            $entityId,
            $storeId
        );

        return $level == self::TOP_LEVEL && $submenuType == SubmenuType::WITH_CONTENT
            || $level > self::TOP_LEVEL && $subcategoriesPosition != SubcategoriesPosition::NOT_SHOW;
    }
}
