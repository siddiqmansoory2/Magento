<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


namespace Amasty\MegaMenuLite\Ui\DataProvider\Form\Link;

use Amasty\MegaMenuLite\Api\Data\Menu\LinkInterface;
use Amasty\MegaMenuLite\Model\Backend\DataProvider\Pool;
use Amasty\MegaMenuLite\Model\Menu\Link;
use Amasty\MegaMenuLite\Model\ResourceModel\Menu\Link\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Pool
     */
    private $dataModifier;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataPersistorInterface $dataPersistor,
        CollectionFactory $collectionFactory,
        Registry $coreRegistry,
        PoolInterface $pool,
        RequestInterface $request,
        Pool $dataModifier,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->coreRegistry = $coreRegistry;
        $this->pool = $pool;
        $this->request = $request;
        $this->dataModifier = $dataModifier;
    }

    /**
     * @return array
     */
    public function getData()
    {
        /** @var Link $current */
        $current = $this->coreRegistry->registry(LinkInterface::PERSIST_NAME);
        if ($current && $current->getEntityId()) {
            $data = $current->getData();
            $storeId = (int) $this->request->getParam('store', Store::DEFAULT_STORE_ID);
            $entityId = (int) $current->getEntityId();
            $result[$entityId] = $this->dataModifier->execute($data, $storeId, $entityId);
        } else {
            $data = $this->dataPersistor->get(LinkInterface::PERSIST_NAME);
            if (!empty($data)) {
                /** @var Link $pack */
                $link = $this->collection->getNewEmptyItem();
                $link->setData($data);
                $data = $link->getData();
                $result[$link->getId()] = $data;
                $this->dataPersistor->clear(LinkInterface::PERSIST_NAME);
            }
        }

        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $result = $modifier->modifyData($result ?? []);
        }

        return $result ?? [];
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
