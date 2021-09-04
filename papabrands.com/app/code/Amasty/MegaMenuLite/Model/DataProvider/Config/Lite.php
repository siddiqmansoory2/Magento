<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


declare(strict_types=1);

namespace Amasty\MegaMenuLite\Model\DataProvider\Config;

use Amasty\MegaMenuLite\Model\ConfigProvider;
use Amasty\MegaMenuLite\Model\Di\Wrapper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Lite implements ArgumentInterface
{
    const SUBMENU_BACKGROUND_IMAGE = 'submenu_background_image';

    const SUBMENU_BACKGROUND_IMAGE_PATH = 'amasty/megamenu/submenu_background_image/';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Wrapper
     */
    private $invitationHelper;

    public function __construct(
        ConfigProvider $configProvider,
        UrlInterface $urlBuilder,
        Wrapper $invitationHelper
    ) {
        $this->configProvider = $configProvider;
        $this->urlBuilder = $urlBuilder;
        $this->invitationHelper = $invitationHelper;
    }

    public function modifyConfig(array &$config): void
    {
        $config['is_hamburger'] = $this->configProvider->isHamburgerEnabled();
        $config['color_settings'] =  $this->getColorSettings();
        $config['welcome_message']['message'] = $this->configProvider->getWelcomeMessage();
        $config['invitation_url'] = (string) $this->invitationHelper->getCustomerInvitationFormUrl();
        $config['mobile_class'] = 'accordion';
    }
    
    private function getColorSettings(): array
    {
        if ($this->configProvider->isSomeTemplateApplied()) {
            $colorSettings = $this->configProvider->getColorSettings();
            $colorSettings[self::SUBMENU_BACKGROUND_IMAGE] = $this->getSubmenuBackgroundImage();
        }
        
        return $colorSettings ?? [];
    }

    public function getSubmenuBackgroundImage(): string
    {
        $mediaUrl = $this->urlBuilder->getBaseUrl(['_type' => 'media']) . self::SUBMENU_BACKGROUND_IMAGE_PATH;
        $image = $this->configProvider->getSubmenuBackgroundImage();

        return $image ? $mediaUrl . $image : '';
    }
}
