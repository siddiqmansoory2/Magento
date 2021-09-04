<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


declare(strict_types=1);

namespace Amasty\MegaMenuLite\ViewModel\Header;

use Amasty\MegaMenuLite\Model\ConfigProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Logo implements ArgumentInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function isHamburgerEnabled(): bool
    {
        return $this->configProvider->isHamburgerEnabled();
    }
}
