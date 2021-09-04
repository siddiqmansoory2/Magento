<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MegaMenuLite
 */


declare(strict_types=1);

namespace Amasty\MegaMenuLite\Model\Detection;

use Magento\Framework\ObjectManagerInterface;

class MobileDetect
{
    /**
     * @var \Zend_Http_UserAgent
     */
    private $userAgent;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Detection\MobileDetect|null
     */
    private $mobileDetector = null;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    private $httpHeader;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(
        \Zend_Http_UserAgent $userAgent,
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\App\RequestInterface $request,
        ObjectManagerInterface $objectManager
    ) {
        $this->userAgent = $userAgent;
        $this->httpHeader = $httpHeader;
        $this->request = $request;
        $this->objectManager = $objectManager;

        // We are using object manager to create 3rd-party packages' class
        if (class_exists(\Detection\MobileDetect::class)) {
            $this->mobileDetector = $this->objectManager->create(\Detection\MobileDetect::class);
        }
    }

    public function isMobile(): bool
    {
        if ($this->mobileDetector) {
            return $this->mobileDetector->isMobile();
        }
        
        $userAgent = $this->httpHeader->getHttpUserAgent();
        return \Zend_Http_UserAgent_Mobile::match($userAgent, $this->userAgent->getServer());
    }
}
