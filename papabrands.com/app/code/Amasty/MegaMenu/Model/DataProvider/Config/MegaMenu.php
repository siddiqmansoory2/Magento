<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenu
 */


declare(strict_types=1);

namespace Amasty\MegaMenu\Model\DataProvider\Config;

use Amasty\MegaMenu\Model\ConfigProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class MegaMenu implements ArgumentInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function modifyConfig(array &$config): void
    {
        $config['is_sticky'] = $this->configProvider->getStickyEnabled();
        $config['icons_status'] = $this->configProvider->getIconsStatus();
        $config['mobile_class'] = $this->configProvider->getMobileTemplateClass();
    }
}
