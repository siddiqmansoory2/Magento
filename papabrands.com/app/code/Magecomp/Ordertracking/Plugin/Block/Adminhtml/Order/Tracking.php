<?php
namespace Magecomp\Ordertracking\Plugin\Block\Adminhtml\Order;

use Magecomp\Ordertracking\Helper\Data;
class Tracking
{
    protected $helper;
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }
    public function afterGetCarriers(\Magento\Shipping\Block\Adminhtml\Order\Tracking $subject,$result)
    {
        if($this->helper->isEnable())
        {
            if($this->helper->isBluedart()){$result['bluedart']  = __('Bluedart');}
            if($this->helper->isDelhivery()){$result['delhivery']  = __('Delhivery');}
            if($this->helper->isTirupatiCourier()){$result['shreetirupaticourier']  = __('Shree Tirupati Courier Service');}
            if($this->helper->isTrackonCourier()){$result['trackoncourier']  = __('Trackon Couriers PVT LTD');}
            if($this->helper->isProfessionalCourier()){$result['theprofessional']  = __('The Professional Couriers');}
            if($this->helper->isShipRocket()){$result['shiprocket']  = __('Ship Rocket');}
        }
        return $result;
    }
}
