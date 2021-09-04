<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


declare(strict_types = 1);

namespace Amasty\MegaMenuLite\Api\Data\Menu;

interface ItemInterface
{
    const TABLE_NAME = 'amasty_menu_item_content';

    const ID = 'id';

    const ENTITY_ID = 'entity_id';

    const TYPE = 'type';

    const STORE_ID = 'store_id';

    const NAME = 'name';

    const LABEL = 'label';

    const LABEL_GROUP = 'label_group';

    const LABEL_TEXT_COLOR = 'label_text_color';

    const LABEL_BACKGROUND_COLOR = 'label_background_color';

    const SORT_ORDER = 'sort_order';

    const CATEGORY_TYPE = 'category';

    const CUSTOM_TYPE = 'custom';

    const STATUS = 'status';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return void
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return void
     */
    public function setEntityId($entityId);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     *
     * @return void
     */
    public function setType($type);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     *
     * @return void
     */
    public function setStoreId($storeId);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $label
     *
     * @return void
     */
    public function setLabel($label);

    /**
     * @return string
     */
    public function getLabelTextColor();

    /**
     * @param string $labelColor
     *
     * @return void
     */
    public function setLabelTextColor($labelColor);

    /**
     * @return string
     */
    public function getLabelBackgroundColor();

    /**
     * @param string $labelColor
     *
     * @return void
     */
    public function setLabelBackgroundColor($labelColor);

    /**
     * @return int|null
     */
    public function getStatus();

    /**
     * @param int|null $status
     *
     * @return void
     */
    public function setStatus($status);
}
