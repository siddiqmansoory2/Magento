<?php
namespace Magecomp\Ordertracking\Plugin\Block\Tracking;

use Magento\Shipping\Model\Tracking\Result\Status;
use Magecomp\Ordertracking\Helper\Data;

class Popup
{
    protected $helper;
    protected $trackResultStatus;
    public function __construct(Data $helper,Status $trackResult)
    {
        $this->helper = $helper;
        $this->trackResultStatus = $trackResult;
    }
    public function afterGetTrackingInfo(\Magento\Shipping\Block\Tracking\Popup $subject,$result)
    {
        if($this->helper->isEnable())
        {
            foreach($result as $shipid => $_result)
            {
                foreach($_result as $key => $track)
                {
                    if($this->helper->isBluedart() &&  $track['title']=='Bluedart')
                    {
                        $data = $this->helper->getBluedartData($track['number']);
                        $allData = array('carrier' => 'bluedart',
                            'carrier_title' => 'Bluedart',
                            'tracking' => $track['number'],
                        );
                        $result[$shipid][$key] = $this->trackResultStatus->setData($allData)
                            ->setErrorMessage(null)
                            ->setTrackSummary($data);
                    }
                    if($this->helper->isDelhivery() &&  $track['title']=='Delhivery')
                    {
                        $data = $this->helper->getDelhiveryData($track['number']);
                        $allData = array('carrier' => 'delhivery',
                            'carrier_title' => 'Delhivery',
                            'tracking' => $track['number'],
                        );
                        $result[$shipid][$key] = $this->trackResultStatus->setData($allData)
                            ->setErrorMessage(null)
                            ->setTrackSummary($data);
                    }
                    if($this->helper->isTirupatiCourier() && $track['title']==__("Shree Tirupati Courier Service"))
                    {
                        $data = $this->helper->getTirupatiCourierData($track['number']);
                        $allData = array('carrier' => 'shreetirupaticourier',
                            'carrier_title' => __('Shree Tirupati Courier Service'),
                            'tracking' => $track['number'],
                        );

                        $result[$shipid][$key] =  $this->trackResultStatus->setData($allData)
                            ->setErrorMessage(null)
                            ->setTrackSummary($data);
                    }
                    if($this->helper->isTrackonCourier() && $track['title']==__("Trackon Couriers PVT LTD"))
                    {
                        $data = $this->helper->getTrackonCourierData($track['number']);
                        $allData = array('carrier' => 'trackoncourier',
                            'carrier_title' => __('Trackon Couriers PVT LTD'),
                            'tracking' => $track['number'],
                        );
                        $result[$shipid][$key] =  $this->trackResultStatus->setData($allData)
                            ->setErrorMessage(null)
                            ->setTrackSummary($data);
                    }
                    if($this->helper->isProfessionalCourier() && $track['title']==__("The Professional Couriers"))
                    {
                        $data = $this->helper->getProfessionalCourierData($track['number']);
                        $allData = array('carrier' => 'theprofessional',
                            'carrier_title' => __('The Professional Couriers'),
                            'tracking' => $track['number'],
                        );
                        $result[$shipid][$key] =  $this->trackResultStatus->setData($allData)
                            ->setErrorMessage(null)
                            ->setTrackSummary($data);
                    }
                    if($this->helper->isShipRocket() && $track['title']==__("Ship Rocket"))
                    {
                        $data = $this->helper->getShipRocket($track['number']);
                        $allData = array('carrier' => 'shiprocket',
                            'carrier_title' => 'Shiprocket',
                            'tracking' => $track['number'],
                        );

                        $result[$shipid][$key] = $this->trackResultStatus->setData($allData)
                            ->setErrorMessage(null)
                            ->setTrackSummary($data);
                    }
                    if (!is_object($track)) {
                        continue;
                    }

                    $carrier = $track->getCarrier();
                    if ($carrier=='usps')
                    {
                        $data = $this->helper->getUspsData($track->getTracking());
                        $result[$shipid][$key] = $this->trackResultStatus->setData($track->getAllData())
                            ->setErrorMessage(null)
                            ->setTrackSummary($data);
                    }
                    if ($carrier=='dhl')
                    {
                        $data = $this->helper->getDhlData($track->getTracking());
                        $result[$shipid][$key] = $this->trackResultStatus->setData($track->getAllData())
                            ->setErrorMessage(null)
                            ->setTrackSummary($data);
                    }

                }
            }
        }
        return $result;
    }
}
