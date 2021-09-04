<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenu
 */


declare(strict_types = 1);

namespace Amasty\MegaMenu\Model\Menu;

use Amasty\MegaMenu\Api\Data\Menu\ItemInterface;
use Amasty\MegaMenuLite\Model\Menu\Item as ItemLite;
use Magento\Framework\DataObject\IdentityInterface;

class Item extends ItemLite implements ItemInterface, IdentityInterface
{
    const CACHE_TAG = 'amasty_mega_menu';

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\MegaMenu\Model\ResourceModel\Menu\Item::class);
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        $content = $this->_getData(ItemInterface::CONTENT);
        if ($this->getType() === self::CATEGORY_TYPE && $content === null) {
            $content = '{{child_categories_content}}';
        }

        return $content;
    }

    /**
     * @inheritdoc
     */
    public function setContent($content)
    {
        $this->setData(ItemInterface::CONTENT, $content);
    }

    /**
     * @inheritdoc
     */
    public function getWidth()
    {
        return $this->_getData(ItemInterface::WIDTH);
    }

    /**
     * @inheritdoc
     */
    public function setWidth($width)
    {
        $this->setData(ItemInterface::WIDTH, $width);
    }

    /**
     * @inheritdoc
     */
    public function getWidthValue()
    {
        return $this->_getData(ItemInterface::WIDTH_VALUE);
    }

    /**
     * @inheritdoc
     */
    public function setWidthValue($width)
    {
        $this->setData(ItemInterface::WIDTH_VALUE, $width);
    }

    /**
     * @inheritdoc
     */
    public function getColumnCount()
    {
        return $this->_getData(ItemInterface::COLUMN_COUNT);
    }

    /**
     * @inheritdoc
     */
    public function setColumnCount($columnCount)
    {
        $this->setData(ItemInterface::COLUMN_COUNT, $columnCount);
    }

    public function getIcon(): ?string
    {
        return $this->_getData(ItemInterface::ICON);
    }

    public function setIcon($icon)
    {
        $this->setData(ItemInterface::ICON, $icon);
    }

    public function getSubmenuType(): ?int
    {
        return $this->getData(ItemInterface::SUBMENU_TYPE) !== null
            ? (int) $this->getData(ItemInterface::SUBMENU_TYPE)
            : null;
    }

    public function setSubmenuType($submenuType): void
    {
        $this->setData(ItemInterface::SUBMENU_TYPE, $submenuType);
    }

    public function getSubcategoriesPosition(): ?int
    {
        return $this->getData(ItemInterface::SUBCATEGORIES_POSITION) !== null
            ? (int) $this->getData(ItemInterface::SUBCATEGORIES_POSITION)
            : null;
    }

    public function setSubcategoriesPosition($subcategoriesPosition)
    {
        $this->setData(ItemInterface::SUBCATEGORIES_POSITION, $subcategoriesPosition);
    }
}
