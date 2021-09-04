<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


declare(strict_types=1);

namespace Amasty\MegaMenuLite\ViewModel\Store;

use Amasty\MegaMenuLite\Api\Data\Menu\ItemInterface;
use Amasty\MegaMenuLite\Api\ItemRepositoryInterface;
use Amasty\MegaMenuLite\Model\ConfigProvider;
use Amasty\MegaMenuLite\Model\Detection\MobileDetect;
use Amasty\MegaMenuLite\Model\OptionSource\Status;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Menu implements ArgumentInterface
{
    /**
     * @var MobileDetect
     */
    private $mobileDetect;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $criteriaBuilderFactory;

    /**
     * @var int|null
     */
    private $desktopCustomItemsCount;

    public function __construct(
        MobileDetect $mobileDetect,
        ConfigProvider $configProvider,
        ItemRepositoryInterface $itemRepository,
        SearchCriteriaBuilderFactory $criteriaBuilderFactory
    ) {
        $this->mobileDetect = $mobileDetect;
        $this->configProvider = $configProvider;
        $this->itemRepository = $itemRepository;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
    }

    public function isRenderSkeleton(): bool
    {
        $isMobile = $this->mobileDetect->isMobile();
        $isHamburger = $this->configProvider->isHamburgerEnabled();

        return !$isMobile && (!$isHamburger || $this->getDesktopCustomItemsCount() > 0);
    }

    private function getDesktopCustomItemsCount(): int
    {
        if ($this->desktopCustomItemsCount === null) {
            $searchCriteriaBuilder = $this->criteriaBuilderFactory->create();
            $searchCriteriaBuilder->addFilter(
                ItemInterface::STATUS,
                [Status::ENABLED, Status::DESKTOP],
                'in'
            )->addFilter(
                ItemInterface::TYPE,
                ItemInterface::CUSTOM_TYPE
            );
            $searchResult = $this->itemRepository->getList($searchCriteriaBuilder->create());
            $this->desktopCustomItemsCount = $searchResult->getTotalCount();
        }

        return $this->desktopCustomItemsCount;
    }
}
