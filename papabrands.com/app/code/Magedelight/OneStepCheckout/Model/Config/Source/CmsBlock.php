<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OneStepCheckout
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\OneStepCheckout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Class CmsBlock
 */
class CmsBlock implements OptionSourceInterface
{
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var SearchCriteriaInterface
     */
    private $searchCriteria;

    /**
     * CmsBlock constructor.
     * @param BlockRepositoryInterface $blockRepository
     * @param SearchCriteriaInterface $searchCriteria
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        SearchCriteriaInterface $searchCriteria
    ) {
        $this->blockRepository = $blockRepository;
        $this->searchCriteria = $searchCriteria;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $responseArray = [];
        $default = [
            'value' => 0,
            'label' => __('---- Please select a static block ----')
        ];

        $allItems = $this->blockRepository->getList($this->searchCriteria)->getItems();

        foreach ($allItems as $item) {
            $responseArray[] = [
                'value' => $item->getIdentifier(),
                'label' => __($item->getTitle())
            ];
        }
        array_unshift($responseArray, $default);
        return $responseArray;
    }
}
