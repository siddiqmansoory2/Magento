<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenu
 */


namespace Amasty\MegaMenu\Api;

use Amasty\MegaMenuLite\Api\ItemRepositoryInterface as ItemRepositoryInterfaceLite;

/**
 * @api
 */
interface ItemRepositoryInterface extends ItemRepositoryInterfaceLite
{
    /**
     * Get by id
     *
     * @param int $id
     *
     * @return \Amasty\MegaMenu\Api\Data\Menu\ItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Get by entity id & store id
     *
     * @param int $entityId
     * @param int $storeId
     * @param string $type
     *
     * @return \Amasty\MegaMenu\Api\Data\Menu\ItemInterface
     */
    public function getByEntityId($entityId, $storeId, $type);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Amasty\MegaMenu\Api\Data\Menu\ItemSearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
