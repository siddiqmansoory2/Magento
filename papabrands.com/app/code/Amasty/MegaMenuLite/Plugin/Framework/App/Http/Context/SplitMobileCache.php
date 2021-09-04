<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


declare(strict_types=1);

namespace Amasty\MegaMenuLite\Plugin\Framework\App\Http\Context;

use Amasty\MegaMenuLite\Model\Detection\MobileDetect;
use Magento\Framework\App\Http\Context as HttpContext;

class SplitMobileCache
{
    const IS_MOBILE_HTTP_CONTEXT_KEY  = 'IS_MOBILE_HTTP_CONTEXT_KEY';

    /**
     * @var MobileDetect
     */
    private $mobileDetect;

    public function __construct(
        MobileDetect $mobileDetect
    ) {
        $this->mobileDetect = $mobileDetect;
    }

    /**
     * @param HttpContext $subject
     */
    public function beforeGetVaryString(HttpContext $subject)
    {
        $subject->setValue(self::IS_MOBILE_HTTP_CONTEXT_KEY, (int)$this->mobileDetect->isMobile(), '');
    }
}
