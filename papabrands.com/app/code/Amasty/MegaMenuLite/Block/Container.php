<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


declare(strict_types=1);

namespace Amasty\MegaMenuLite\Block;

use Amasty\MegaMenuLite\Api\Data\Menu\ItemInterface;
use Amasty\MegaMenuLite\Model\Menu\Content\Resolver;
use Amasty\MegaMenuLite\Model\Menu\TreeResolver;
use Magento\Customer\Model\Url as CustomerUrlModel;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;

class Container extends Template
{
    const CONFIGURATION = 'config';

    const DATA = 'data';

    const IS_CHILD_HAS_ICON = 'isChildHasIcons';

    /**
     * @var Node|null
     */
    private $menu = null;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var TreeResolver
     */
    private $treeResolver;

    /**
     * @var Resolver
     */
    private $contentResolver;

    /**
     * @var CustomerUrlModel
     */
    private $customerUrlModel;

    public function __construct(
        Template\Context $context,
        Json $json,
        TreeResolver $treeResolver,
        Resolver $contentResolver,
        CustomerUrlModel $customerUrlModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->json = $json;
        $this->treeResolver = $treeResolver;
        $this->contentResolver = $contentResolver;
        $this->customerUrlModel = $customerUrlModel;
    }

    public function getJsComponents()
    {
        $this->jsLayout = $this->getData('jsLayout')['components'] ?? [];

        return $this->json->serialize($this->jsLayout);
    }

    public function getJsSettings()
    {
        $settings = [
            'account' => [
                'login' => $this->customerUrlModel->getLoginUrl(),
                'create' => $this->customerUrlModel->getRegisterUrl(),
                'logout' => $this->customerUrlModel->getLogoutUrl(),
                'account' => $this->customerUrlModel->getAccountUrl()
            ]
        ];

        $layoutSettings = $this->getData('jsLayout')['settings'] ?? [];
        foreach ($layoutSettings as $key => $layoutSettingModel) {
            $settings[$key] = $layoutSettingModel->getData();
        }

        return $this->json->serialize($settings);
    }

    public function getStoreLinks(): string
    {
        $block = $this->getLayout()->getBlock('store.links');
        if ($block) {
            $data = $block->getData();
        }

        return $this->json->serialize($data ?? []);
    }

    public function getJsConfig(): string
    {
        $settings = [];
        $configs = $this->getData('jsLayout')['config'] ?? [];

        foreach ($configs as $config) {
            $config->modifyConfig($settings);
        }

        return $this->json->serialize($settings);
    }

    public function getJsData(): string
    {
        return $this->json->serialize(
            $this->getNodeData($this->getMenuTree())
        );
    }

    public function getMenuTree(): ?Node
    {
        if ($this->menu === null) {
            $this->menu = $this->treeResolver->get(
                (int) $this->_storeManager->getStore()->getId()
            );
        }

        return $this->menu;
    }

    public function getNodeData(Node $node): array
    {
        $data = [];
        if ($node->getChildren()->count()) {
            foreach ($node->getChildren() as $child) {
                $data[] = $this->getNodeData($child);
                if ($child->getData('icon') && !$node->getData(self::IS_CHILD_HAS_ICON)) {
                    $node->setData(self::IS_CHILD_HAS_ICON, true);
                }
            }
        }

        return $this->getCurrentNodeData($node, $data);
    }

    private function getCurrentNodeData(Node $node, array $elems = []): array
    {
        $data = [
            ItemInterface::NAME => $node->getData('name'),
            'is_category' => $node->getData('is_category'),
            ItemInterface::ID => $node->getId(),
            self::IS_CHILD_HAS_ICON => (bool) $node->getData(self::IS_CHILD_HAS_ICON),
            ItemInterface::STATUS => $this->getNodeStatus($node),
            'content' => $this->contentResolver->resolve($node),
            'elems' => $elems,
            'url' => $node->getData('url'),
            'current' => $node->getData('has_active') || $node->getData('is_active')
        ];

        if ($node->getData(ItemInterface::LABEL)) {
            $data[ItemInterface::LABEL] = [
                ItemInterface::LABEL => $node->getData(ItemInterface::LABEL),
                ItemInterface::LABEL_TEXT_COLOR => $node->getData(ItemInterface::LABEL_TEXT_COLOR),
                ItemInterface::LABEL_BACKGROUND_COLOR => $node->getData(ItemInterface::LABEL_BACKGROUND_COLOR)
            ];
        }

        $transportObject = new DataObject();
        $this->_eventManager->dispatch(
            'am_mega_menu_update_node_data',
            [
                'node' => $node,
                'transport_object' => $transportObject
            ]
        );

        return array_merge($data, $transportObject->getData());
    }

    private function getNodeStatus(Node $node): int
    {
        return $node->getData('is_category') ? 1 : (int) $node->getData(ItemInterface::STATUS);
    }
}
