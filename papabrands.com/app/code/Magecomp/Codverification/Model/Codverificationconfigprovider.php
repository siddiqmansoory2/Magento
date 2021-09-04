<?php
namespace Magecomp\Codverification\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class Codverificationconfigprovider implements ConfigProviderInterface
{
    protected $helperdata;
    protected $checkoutSession;
    public function __construct(\Magecomp\Codverification\Helper\Data $helperdata,
                                \Magento\Checkout\Model\Session $checkoutSession) {
        $this->helperdata = $helperdata;
        $this->checkoutSession = $checkoutSession;
    }

    public function getConfig()
    {
        $config = [
            'payment' => [
                'codverification' => [
					'isenable' => $this->helperdata->isEnabled(),
					'customtitle' => $this->helperdata->getCustomTitle(),
                    'isalreadyverify' => $this->checkoutSession->getQuote()->getCodverification()
                ]
            ]
        ];

        return $config;
    }
}