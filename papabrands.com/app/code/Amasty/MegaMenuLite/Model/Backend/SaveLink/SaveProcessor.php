<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


declare(strict_types=1);

namespace Amasty\MegaMenuLite\Model\Backend\SaveLink;

use Amasty\MegaMenuLite\Api\Data\Menu\LinkInterface;
use Amasty\MegaMenuLite\Model\Menu\Item;
use Amasty\MegaMenuLite\Model\Provider\FieldsByStore;
use Amasty\MegaMenuLite\Model\Repository\ItemRepository;
use Amasty\MegaMenuLite\Model\Repository\LinkRepository;
use Amasty\MegaMenuLite\Model\ResourceModel\Menu\Item\Position\GetMaxSortOrder;
use Magento\Framework\Exception\LocalizedException;

class SaveProcessor
{
    /**
     * @var GetMaxSortOrder
     */
    private $maxSortOrder;

    /**
     * @var LinkRepository
     */
    private $linkRepository;

    /**
     * @var ItemRepository
     */
    private $itemRepository;

    /**
     * @var FieldsByStore
     */
    private $fieldsByStore;

    /**
     * @var Pool
     */
    private $dataCollector;

    public function __construct(
        GetMaxSortOrder $maxSortOrder,
        LinkRepository $linkRepository,
        ItemRepository $itemRepository,
        FieldsByStore $fieldsByStore,
        Pool $dataCollector
    ) {
        $this->maxSortOrder = $maxSortOrder;
        $this->linkRepository = $linkRepository;
        $this->itemRepository = $itemRepository;
        $this->fieldsByStore = $fieldsByStore;
        $this->dataCollector = $dataCollector;
    }

    public function execute(array $inputData): int
    {
        /** @var Item $itemContent */
        $itemContent = $this->retrieveItemContent($inputData);
        $linkEntityId = $this->saveLink($inputData);

        $itemContent->setEntityId($linkEntityId);
        $itemContent->setSortOrder($this->maxSortOrder->execute());
        $this->itemRepository->save($itemContent);

        return $linkEntityId;
    }

    private function saveLink(array $inputData): int
    {
        $entityId = (int) ($inputData[LinkInterface::ENTITY_ID] ?? 0);
        /** @var LinkInterface $model */
        $link = $entityId ? $this->linkRepository->getById($entityId) : $this->linkRepository->getNew();
        $data = $this->dataCollector->execute($inputData);
        $link->setData($data);
        $link = $this->linkRepository->save($link);

        return (int) $link->getEntityId();
    }

    private function retrieveItemContent(array &$data): Item
    {
        $itemContent = $this->getItemContent($data);

        return $this->updateContentData($itemContent, $data);
    }

    private function getItemContent(array $data): Item
    {
        $storeId = (int) $data['store_id'] ?? 0;
        /** @var Item $itemContent */
        $itemContent = $this->itemRepository->getNew();
        if (isset($data[Item::ENTITY_ID]) && $data[Item::ENTITY_ID]) {
            $itemContentDefault = $this->itemRepository->getByEntityId(
                $data[Item::ENTITY_ID],
                0,
                Item::CUSTOM_TYPE
            );
            if ($storeId) {
                $itemContent->setStoreId($storeId);
                $itemContentTemp = $this->itemRepository->getByEntityId(
                    $data[Item::ENTITY_ID],
                    $storeId,
                    Item::CUSTOM_TYPE
                );
                if ($itemContentTemp) {
                    $itemContent = $itemContentTemp;
                }
            } else {
                $itemContent = $itemContentDefault;
            }
        }
        $itemContent->setType(Item::CUSTOM_TYPE);

        return $itemContent;
    }

    private function updateContentData(Item $itemContent, array $data): Item
    {
        $useDefaults = $data['use_default'] ?? [];
        foreach ($this->fieldsByStore->getCustomFields() as $fieldSet) {
            foreach ($fieldSet as $field) {
                if (isset($data[$field])) {
                    if (isset($useDefaults[$field]) && $useDefaults[$field]) {
                        $itemContent->setData($field, null);
                    } else {
                        $itemContent->setData($field, $data[$field]);
                    }
                    unset($data[$field]);
                } else {
                    $this->triggerNotValid($field);
                }
            }
        }

        return $itemContent;
    }

    /**
     * @param $field
     * @throws LocalizedException
     */
    private function triggerNotValid($field): void
    {
        throw new LocalizedException(__('Please enter valid %1', $field));
    }
}
