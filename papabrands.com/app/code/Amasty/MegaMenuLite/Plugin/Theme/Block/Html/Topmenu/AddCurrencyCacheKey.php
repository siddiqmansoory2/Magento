<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


declare(strict_types=1);

namespace Amasty\MegaMenuLite\Plugin\Theme\Block\Html\Topmenu;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Topmenu;

class AddCurrencyCacheKey
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    public function afterGetCacheKeyInfo(Topmenu $subject, array $keys): array
    {
        $keys['currency'] = $this->storeManager->getStore()->getCurrentCurrency()->getCode();

        return $keys;
    }
}
