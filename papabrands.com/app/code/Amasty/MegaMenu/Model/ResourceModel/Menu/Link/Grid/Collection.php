<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenu
 */


namespace Amasty\MegaMenu\Model\ResourceModel\Menu\Link\Grid;

use Amasty\MegaMenu\Model\ResourceModel\Menu\ResourceResolver;
use Amasty\MegaMenuLite\Model\ResourceModel\Menu\Link\Grid\Collection as GridCollectionLite;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Psr\Log\LoggerInterface;

class Collection extends GridCollectionLite
{
    /**
     * @var array
     */
    private $mappedFields = [
        'entity_id' => 'main_table.entity_id'
    ];

    /**
     * @var ResourceResolver
     */
    private $resourceResolver;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        ResourceResolver $resourceResolver,
        $model = Document::class,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $eventPrefix,
            $eventObject,
            $resourceModel,
            $model,
            $connection,
            $resource
        );
        $this->resourceResolver = $resourceResolver;
    }

    protected function _renderFiltersBefore()
    {
        $this->resourceResolver->joinLink($this, 'main_table', 'link');

        parent::_renderFiltersBefore();
    }
}
