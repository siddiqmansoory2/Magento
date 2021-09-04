<?php
namespace Meetanshi\MaintenancePage\Plugin\Subscriber;
use Magento\Framework\App\Response\Http as responseHttp;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Meetanshi\MaintenancePage\Helper\Data;

class Redirect {

    private $url;
    private $redirect;
    private $helper;
    public function __construct(
        responseHttp $response,
        UrlInterface $url,
        Data $data,
        RedirectInterface $redirect
    ) {
        $this->response = $response;
        $this->url = $url;
        $this->helper = $data;
        $this->redirect = $redirect;
    }

    public function afterExecute(\Magento\Newsletter\Controller\Subscriber\NewAction $subject, $result) {

        $redirectUrl = $this->redirect->getRedirectUrl();
        if (strpos($redirectUrl, 'maintenancepage') !== false && $this->helper->isModuleEnabled()) {
            $url = $this->url->getUrl('contact/index/index');
            $this->response->setRedirect($url);
            return $result;
        }
        return $result;
    }
}