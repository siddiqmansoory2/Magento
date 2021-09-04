<?php

namespace Meetanshi\MaintenancePage\Observer;

use Meetanshi\MaintenancePage\Helper\Data;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Request\Http;

class HandleControllerActionPredispatch implements ObserverInterface
{
    private $helper;

    private $remoteAddress;

    private $messageManager;

    private $request;
    
    public function __construct(
        Data $helper,
        RemoteAddress $remoteAddress,
        ManagerInterface $messageManager,
        Http $request
    ) {
        $this->helper = $helper;
        $this->remoteAddress = $remoteAddress;
        $this->messageManager = $messageManager;
        $this->request = $request;
    }
    public function execute(Observer $observer)
    {
        $isEnabled = ($this->helper->isModuleEnabled()) ? true : false;
        if(!$isEnabled){
            return $this;
        }else {
        try{
            $urlInterface = $this->helper->getUrlInterface();


            $whitelistIps = $this->helper->getWhitelistIps();
            $allowedUrls = $this->helper->getAllowedUrls();
            $redirectToPage = $this->helper->getRedirectToPage(true);

            $ipAddress = $this->remoteAddress->getRemoteAddress();
            $allowIpAddress = ($isEnabled && in_array($ipAddress, $whitelistIps)) ? true : false;
            $currentUrl = $urlInterface->getCurrentUrl();
            $currentUrl = (strpos($currentUrl, 'referer')) ? strstr($currentUrl, 'referer', true) : $currentUrl;
            $currentUrl = (substr($currentUrl, -1) != '/') ? $currentUrl . '/' : $currentUrl;

            $allowUrl = false;
            foreach ($allowedUrls as $urla){
                $allowUrl = false;
                $ch = strpos($urla,'#/',-2);
                if($ch){
                    if(strpos($currentUrl,substr_replace($urla ,"",-2)) !== false && $isEnabled){
                        $allowUrl = true;
                        break;
                    }
                }else{
                    if(in_array($currentUrl, $allowedUrls) && $isEnabled){
                        $allowUrl = true;
                        break;
                    }
                }
            }

            if ($allowIpAddress || $allowUrl || $this->helper->canDisableMaintenanceMode()) {
                return $this;
            } elseif ($isEnabled) {
                $observer->getControllerAction()
                    ->getResponse()
                    ->setRedirect($redirectToPage);
            } else {
                return $this;
            }
        }catch (\exception $e){
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        return $this;
    }
}
