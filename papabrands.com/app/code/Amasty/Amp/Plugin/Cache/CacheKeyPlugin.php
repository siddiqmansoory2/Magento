<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Amp
 */


namespace Amasty\Amp\Plugin\Cache;

use Amasty\Amp\Model\UrlConfigProvider;

class CacheKeyPlugin
{
    /**
     * @var \Amasty\Amp\Model\ConfigProvider
     */
    private $configProvider;

    public function __construct(\Amasty\Amp\Model\ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param $subject
     * @param array $keyInfo
     * @return array string
     */
    public function afterGetCacheKeyInfo($subject, $keyInfo)
    {
        $actionName = $subject->getRequest()->getFullActionName();
        if ($this->configProvider->isAmpEnabledOnCurrentPage($actionName)) {
            $keyInfo[] = UrlConfigProvider::AMP;
        }

        return $keyInfo;
    }
}
