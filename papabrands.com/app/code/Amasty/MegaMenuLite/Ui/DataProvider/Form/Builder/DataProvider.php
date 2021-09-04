<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


namespace Amasty\MegaMenuLite\Ui\DataProvider\Form\Builder;

use Amasty\MegaMenuLite\Api\Data\Menu\LinkInterface;
use Amasty\MegaMenuLite\Api\Data\Menu\ItemInterface;
use Magento\Framework\App\RequestInterface;
use Amasty\MegaMenuLite\Model\Menu\Link;
use Amasty\MegaMenuLite\Model\OptionSource\CmsPage;
use Amasty\MegaMenuLite\Model\OptionSource\UrlKey;
use Magento\Framework\App\Request\DataPersistorInterface;
use Amasty\MegaMenuLite\Model\ResourceModel\Menu\Link\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Amasty\MegaMenuLite\Api\ItemRepositoryInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @var ItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataPersistorInterface $dataPersistor,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        PoolInterface $pool,
        ItemRepositoryInterface $itemRepository,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->coreRegistry = $coreRegistry;
        $this->pool = $pool;
        $this->itemRepository = $itemRepository;
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $result = parent::getData();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
