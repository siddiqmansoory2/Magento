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

namespace Magedelight\OneStepCheckout\Plugin\View\Page\Config;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Config\Renderer as MagentoRenderer;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\GroupedCollection;
use Magedelight\OneStepCheckout\Helper\Data;

/**
 * Class Renderer
 */
class Renderer
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var GroupedCollection
     */
    private $pageAssets;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Renderer constructor.
     * @param Config $config
     * @param CacheInterface $cache
     * @param Repository $assetRepo
     * @param GroupedCollection $pageAssets
     * @param Data $helper
     */
    public function __construct(
        Config $config,
        CacheInterface $cache,
        Repository $assetRepo,
        GroupedCollection $pageAssets,
        Data $helper
    ) {
        $this->config = $config;
        $this->cache = $cache;
        $this->assetRepo = $assetRepo;
        $this->pageAssets = $pageAssets;
        $this->helper = $helper;
    }

    /**
     * Add js file for version compare
     *
     * @param MagentoRenderer $subject
     * @param array $resultGroups
     *
     * @return array
     */
    public function beforeRenderAssets(MagentoRenderer $subject, $resultGroups = [])
    {
        if ($this->helper->isVersionAbove('2.3.2')) {
            $file = 'Magedelight_OneStepCheckout::js/oscPatch.js';
        } else {
            $file = 'Magedelight_OneStepCheckout::js/oscNoPatch.js';
        }
        $asset = $this->assetRepo->createAsset($file);
        $this->pageAssets->insert($file, $asset, 'requirejs/require.js');
        return [$resultGroups];
    }
}
