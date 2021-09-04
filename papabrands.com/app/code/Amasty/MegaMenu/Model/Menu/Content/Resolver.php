<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenu
 */


declare(strict_types=1);

namespace Amasty\MegaMenu\Model\Menu\Content;

use Amasty\MegaMenu\Api\Data\Menu\ItemInterface;
use Amasty\MegaMenu\Model\Menu\Subcategory;
use Amasty\MegaMenu\Model\OptionSource\SubmenuType;
use Amasty\MegaMenuLite\Model\Menu\Content\Resolver as ResolverLite;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\LayoutInterface;
use Magento\Widget\Model\Template\Filter;

class Resolver extends ResolverLite
{
    /**
     * @var array
     */
    private $directivePatterns = [
        'construct' =>'/{{([a-z]{0,10})(.*?)}}/si'
    ];

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        Filter $filter,
        Manager $moduleManager,
        LayoutInterface $layout,
        $directivePatterns = []
    ) {
        $this->filter = $filter;
        $this->moduleManager = $moduleManager;
        $this->directivePatterns = array_merge($this->directivePatterns, $directivePatterns);
        parent::__construct($layout);
    }

    public function resolve(Node $node): ?string
    {
        $content = $node->getData(ItemInterface::CONTENT);
        if ($node->getIsCategory() && $content === null) {
            $content = $this->getDefaultContent();
        }

        return $content ? $this->parseContent($node, $content) : $content;
    }

    private function getDefaultContent(): string
    {
        if ($this->moduleManager->isEnabled('Magento_PageBuilder')
            && $this->moduleManager->isEnabled('Amasty_MegaMenuPageBuilder')
        ) {
            $content = self::CHILD_CATEGORIES_PAGE_BUILDER;
        } else {
            $content = self::CHILD_CATEGORIES;
        }

        return $content;
    }

    private function parseContent(Node $node, string $content): ?string
    {
        if ($content) {
            if ($content !== self::CHILD_CATEGORIES && $this->isDirectivesExists($content)) {
                $content = $this->parseWysiwyg($content);
            }

            $content = $this->isSubmenuContentEnabled($node)
                ? $this->removeVariables($content)
                : $this->parseVariables($node, $content);
        }

        return $content;
    }

    private function removeVariables(string $content): string
    {
        return str_replace(self::CHILD_CATEGORIES, '', $content);
    }

    private function parseWysiwyg(string $content): string
    {
        return (string) $this->filter->filter($content);
    }

    private function isDirectivesExists(string $html): bool
    {
        $matches = false;
        if ($this->moduleManager->isEnabled('Magento_PageBuilder')) {
            return true;
        }

        foreach ($this->directivePatterns as $pattern) {
            if (preg_match($pattern, $html)) {
                $matches = true;
                break;
            }
        }

        return $matches;
    }

    public function isSubmenuContentEnabled(Node $node): bool
    {
        $mainNode = $this->getParentNode($node, Subcategory::TOP_LEVEL);

        return  $mainNode->getData(ItemInterface::SUBMENU_TYPE) == SubmenuType::WITH_CONTENT;
    }

    private function getParentNode(Node $node, int $level): Node
    {
        if ($node->getLevel() > $level) {
            $node = $this->getParentNode($node->getParent(), $level);
        }

        return $node;
    }
}
