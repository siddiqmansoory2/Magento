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
namespace Magedelight\OneStepCheckout\Block\Success;

use Magento\Framework\View\Element\Context;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Cms\Model\BlockFactory;
use Magedelight\OneStepCheckout\Helper\Data;

/**
 * Class Block
 */
class CmsBlock extends \Magento\Cms\Block\Block
{
    /**
     * @var $blockId
     */
    private $blockId;

    /**
     * @var Data
     */
    private $helper;

    /**
     * CmsBlock constructor.
     * @param Context $context
     * @param FilterProvider $filterProvider
     * @param StoreManagerInterface $storeManager
     * @param BlockFactory $blockFactory
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        FilterProvider $filterProvider,
        StoreManagerInterface $storeManager,
        BlockFactory $blockFactory,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $filterProvider, $storeManager, $blockFactory, $data);
        $this->helper = $helper;
    }

    /**
     * @return string|int
     */
    public function getBlockId()
    {
        if ($this->blockId === null && $this->helper->isEnabled()) {
            $this->blockId = $this->helper->getSuccessCmsBlockByArea($this->getArea());
        }
        return $this->blockId;
    }
}
