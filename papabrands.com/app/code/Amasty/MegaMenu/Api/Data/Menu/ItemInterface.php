<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenu
 */


declare(strict_types = 1);

namespace Amasty\MegaMenu\Api\Data\Menu;

use Amasty\MegaMenuLite\Api\Data\Menu\ItemInterface as ItemInterfaceLite;

interface ItemInterface extends ItemInterfaceLite
{
    const CONTENT = 'content';

    const WIDTH = 'width';

    const WIDTH_VALUE = 'width_value';

    const COLUMN_COUNT = 'column_count';

    const ICON = 'icon';

    const SUBCATEGORIES_POSITION = 'subcategories_position';

    const SUBMENU_TYPE = 'submenu_type';

    /**
     * @return string|null
     */
    public function getContent();

    /**
     * @return int|null
     */
    public function getWidth();

    /**
     * @param int|null $width
     *
     * @return void
     */
    public function setWidth($width);

    /**
     * @return int|null
     */
    public function getWidthValue();

    /**
     * @param int|null $width
     *
     * @return void
     */
    public function setWidthValue($width);

    /**
     * @return int|null
     */
    public function getColumnCount();

    /**
     * @param int|null $columnCount
     *
     * @return void
     */
    public function setColumnCount($columnCount);

    /**
     * @return string|null
     */
    public function getIcon(): ?string;

    /**
     * @param string|null $icon
     *
     * @return void
     */
    public function setIcon($icon);

    /**
     * @return int|null
     */
    public function getSubmenuType(): ?int;

    /**
     * @param int|null $submenuType
     *
     * @return void
     */
    public function setSubmenuType($submenuType);

    /**
     * @return int|null
     */
    public function getSubcategoriesPosition(): ?int;

    /**
     * @param int|null $subcategoriesPosition
     *
     * @return void
     */
    public function setSubcategoriesPosition($subcategoriesPosition);
}
