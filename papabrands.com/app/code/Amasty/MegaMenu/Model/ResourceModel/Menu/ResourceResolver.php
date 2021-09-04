<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenu
 */


namespace Amasty\MegaMenu\Model\ResourceModel\Menu;

use Amasty\MegaMenu\Api\Data\Menu\LinkInterface;
use Amasty\MegaMenu\Model\OptionSource\UrlKey;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ResourceResolver
{
    /**
     * @var UrlKey
     */
    private $urlKeySource;

    public function __construct(
        UrlKey $urlKeySource
    ) {
        $this->urlKeySource = $urlKeySource;
    }

    /**
     * @param AbstractCollection $collection
     * @param string $tableAlias
     * @param string $columnAlias
     */
    public function joinLink(AbstractCollection $collection, string $tableAlias, string $columnAlias)
    {
        $coalesce[] = LinkInterface::LINK;
        foreach ($this->urlKeySource->getTablesToJoin() as $type => $table) {
            $collection->getSelect()->joinLeft(
                [$table => $collection->getTable($table)],
                sprintf(
                    '%s.page_id = %s.page_id AND %s.link_type = \'%s\'',
                    $tableAlias,
                    $table,
                    $tableAlias,
                    $type
                ),
                ['identifier']
            );
            $coalesce[] = $table . '.identifier';
        }
        $coalesce[] = '\'\'';

        $collection->getSelect()->columns(sprintf(
            'COALESCE(%s) AS %s',
            implode(', ', $coalesce),
            $columnAlias
        ));
    }
}
