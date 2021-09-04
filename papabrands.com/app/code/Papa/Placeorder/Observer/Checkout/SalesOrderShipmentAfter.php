<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Placeorder\Observer\Checkout;

class SalesOrderShipmentAfter implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    
	protected $_objectManager;
	protected $_orderFactory;
	
	
	public function __construct() {
		$this->logisy_link = "https://logisy.tech/api/stores"; 
        $this->x_api_key = "FsyXtilERI527JVTMohMWgHcr5LZEsv5"; 
	}
	
	
	
	public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
		
		
		$tracksCollection = $shipment->getTracksCollection();
		

		$transporter='';
		$awbNumber='';
		
		$_uitems=array();
		
		foreach ($order->getAllVisibleItems() as $_item):
		
			if ($_item->getParentItem()){
				continue;
			}
		
		
			$_uitems[]=$_item->getSku();
			
		endforeach;
		
		
		foreach ($tracksCollection->getItems() as $track) {	
		
				 $transporter = $track->getTitle();
				 $awbNumber = $track->getTrackNumber();
				
				$curl = curl_init();
				curl_setopt_array($curl, array(
				  CURLOPT_URL => $this->logisy_link.'/order/fulfill/',
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => '',
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 0,
				  CURLOPT_FOLLOWLOCATION => false,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => 'PUT',
				  CURLOPT_POSTFIELDS =>'{
					"id": "'.$order->getId().'",
					"shipment_company": "'.$transporter.'",
					"awb_number": "'.$awbNumber.'",
					"shipment_tracking_url": "https://bluedart.com/track-shipment/'.$awbNumber.'/",
					"fulfilled_line_item_ids": '.json_encode($_uitems).'
				}',
				CURLOPT_HTTPHEADER => array(
					'X-API-Key: '.$this->x_api_key,
					'Content-Type: application/json'
				),
				));
				$response = curl_exec($curl);
				curl_close($curl);		

				$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/SalesOrderShipmentAfter.log');
				$logger = new \Zend\Log\Logger();
				$logger->addWriter($writer);
				
				$logger->info('{
					"id": "'.$order->getId().'",
					"shipment_company": "'.$transporter.'",
					"awb_number": "'.$awbNumber.'",
					"shipment_tracking_url": "https://bluedart.com/track-shipment/'.$awbNumber.'/",
					"fulfilled_line_item_ids": '.json_encode($_uitems).'
				}');
				$logger->info($response);
						 
		}	
    }
	
}

