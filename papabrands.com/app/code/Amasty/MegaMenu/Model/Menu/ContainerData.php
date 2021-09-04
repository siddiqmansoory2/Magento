<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenu
 */


declare(strict_types=1);

namespace Amasty\MegaMenu\Model\Menu;

use Amasty\MegaMenu\Api\Data\Menu\ItemInterface;
use Amasty\MegaMenu\Model\OptionSource\SubcategoriesPosition;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\DataObject;

class ContainerData
{
    const MAX_COLUMN_COUNT = 10;

    const DEFAULT_COLUMN_COUNT = 4;
    
    /**
     * @var SubcategoriesPosition
     */
    private $subcategoriesPosition;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var string|null
     */
    private $baseUrl = null;

    public function __construct(
        SubcategoriesPosition $subcategoriesPosition,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->subcategoriesPosition = $subcategoriesPosition;
        $this->urlBuilder = $urlBuilder;
    }

    public function setNodeDataToObject(Node $node, DataObject $object): void
    {
        $data = [
            ItemInterface::TYPE => $this->getNodeType($node),
            ItemInterface::SUBMENU_TYPE => (bool) $node->getData(ItemInterface::SUBMENU_TYPE),
            ItemInterface::WIDTH => (int) $node->getData(ItemInterface::WIDTH),
            ItemInterface::WIDTH_VALUE => (int) $node->getData(ItemInterface::WIDTH_VALUE),
            ItemInterface::COLUMN_COUNT => $this->getColumnCount($node)
        ];

        if ($node->getData(ItemInterface::ICON)) {
            $data[ItemInterface::ICON] = $this->getIcon($node);
        }

        $object->setData($data);
    }

    public function getIcon(Node $node): string
    {
        $url = '';
        if ($node->getIcon()) {
            $url = $node->getIcon();
            $url = rtrim($this->getBaseUrl(), '/') . str_replace(' ', '%20', $url);
        }

        return $url;
    }

    private function getNodeType(Node $node): ?array
    {
        $options = $this->subcategoriesPosition->toOptionArray(true);
        $position = $node->getData(ItemInterface::SUBCATEGORIES_POSITION);
        if ($position === null) {
            $position = $this->getDefaultPosition((int) $node->getData('level'));
        }

        if (isset($options[$position]['label'])) {
            $type = $options[$position]['label']->getText();
            $type = [
                'value' => (int) $position,
                'label' => strtolower($type)
            ];
        }

        return $type ?? null;
    }

    private function getDefaultPosition(int $level): int
    {
        return $level === Subcategory::TOP_LEVEL ? SubcategoriesPosition::LEFT : SubcategoriesPosition::NOT_SHOW;
    }

    public function getBaseUrl(): string
    {
        if (!$this->baseUrl) {
            $this->baseUrl = $this->urlBuilder->getBaseUrl();
        }
        return $this->baseUrl;
    }

    public function getColumnCount(Node $node): int
    {
        $count = $node->getColumnCount() !== null ? (int)$node->getColumnCount() : self::DEFAULT_COLUMN_COUNT;

        return min($count, static::MAX_COLUMN_COUNT);
    }
}
