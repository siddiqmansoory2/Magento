<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenu
 */


namespace Amasty\MegaMenu\Api;

use Amasty\MegaMenuLite\Api\LinkRepositoryInterface as LinkRepositoryInterfaceLite;

/**
 * @api
 */
interface LinkRepositoryInterface extends LinkRepositoryInterfaceLite
{
    /**
     * Get by id
     *
     * @param int $entityId
     *
     * @return \Amasty\MegaMenu\Api\Data\Menu\LinkInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Amasty\MegaMenu\Api\Data\Menu\LinkSearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
