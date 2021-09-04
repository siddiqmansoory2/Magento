<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Model;

class PackorderManagement implements \Papa\Restapi\Api\PackorderManagementInterface
{

    /**
     * {@inheritdoc}
     */
    public function postPackorder($orderCode,$locationCode,$shipmentId,$dimensions,$invoice,$shipmentItems)
    {
		
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/CreateshipmenturlManagement.php.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		
		/*$logger->info($orderCode);
		$logger->info($locationCode);
		$logger->info($shipmentId);
		$logger->info($dimensions);
		$logger->info($invoice);
		$logger->info($shipmentItems);*/
		
		
		
		header('Content-Type: application/json');
		$this->_sobjectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$UrlInterface = $this->_sobjectManager->create('\Magento\Framework\UrlInterface');
		
		
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		
		$order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderCode);
		
		$order->getId();
		
		
		if (! $order->canShip()) {
			
			
			$_shipmentItems=array();
			
			foreach($shipmentItems as $_shipment_Items){
				$_shipmentItems[$_shipment_Items['channelSkuCode']]=$_shipment_Items['quantity'];
			}
			
			
			$tracksCollection = $order->getTracksCollection();			
			
			foreach ($tracksCollection->getItems() as $track) {


				$_uitems=array();
		
				foreach ($order->getAllVisibleItems() AS $orderItem) {

					if ($orderItem->getParentItem()){
						continue;
					}		
					
					if(array_key_exists($orderItem->getSku(),$_shipmentItems)){				
						$getQtyOrdered=$_shipmentItems[$orderItem->getSku()];
						
						$_uitems[]=array(
							"channelSkuCode"=>$orderItem->getSku(),
							"orderItemCode"=>$orderItem->getSku(),
							"quantity"=>$getQtyOrdered,
						);						
					}	
					
				}

				
				
				$_array = array(
					"hasError"=>false,
					"shipmentCode"=>$track->getParentId(),
					"shipmentItems"=>$_uitems
				);
				
				header('Content-Type: application/json');
				echo $response = \Zend_Json::encode($_array);die;
				
			}
			
			
			
			$_array = array( 
				"hasError"=>true,
				"message"=>'You can\'t create an shipment.'
			);
			
			header('Content-Type: application/json');
			echo $response = \Zend_Json::encode($_array);
			$logger->info($response);			
			die;
		}
		 
		$_uitems=array();
		
		foreach ($order->getAllVisibleItems() AS $orderItem) {
			
			
			if ($orderItem->getParentItem()){
				continue;
			}
			
			if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
				continue;
			}
			
			$_uitems[]=array(
				"channelSkuCode"=>$orderItem->getSku(),
				"orderItemCode"=>$orderItem->getSku(),
				"quantity"=>$orderItem->getQtyOrdered()*1,
			);		
			
		}
		
		if(count($_uitems)==0){
			$_array = array( 
				"hasError"=>true,
				"message"=>'You can\'t create an shipment.'
			);
			
			header('Content-Type: application/json');
			echo $response = \Zend_Json::encode($_array);
			$logger->info($response);			
			die;
		}
		
		try {
			
			$url = $UrlInterface->getUrl('papabrands/bluedart/sendshipment');
			
			$param = array();
			$param['order_id']=$orderCode;
			$param['shiporder']='yes';
			$param['shipmode']='E';
			$param['shipment']['breadth']='10';
            $param['shipment']['height']='10';
            $param['shipment']['length']='10';
            $param['shipment']['weight']='10';
			
			$_shipmentItems=array();
			
			foreach($shipmentItems as $_shipment_Items){
				$_shipmentItems[$_shipment_Items['channelSkuCode']]=$_shipment_Items['quantity'];
			}
			
			
            $param['shipmentItems']=$_shipmentItems;
			$query = http_build_query($param);
			
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => "$url?$query",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => false,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'GET'
			));
			$response = curl_exec($curl);
			if(curl_errno($curl)) {
				
				$_array = array(
					"hasError"=>false,
					"message"=>curl_error($curl)
				);
				header('Content-Type: application/json');
				echo $response = \Zend_Json::encode($_array);
				$logger->info($response);			
				die;
				
			}else{
				$logger->info($response);
				echo $response;
			}
			curl_close($curl);die;
			
		} catch (\Exception $e) {
			
			$_array = array(
				"hasError"=>true,
				"message"=>$e->getMessage()
			);
			
			header('Content-Type: application/json');
			echo $response = \Zend_Json::encode($_array);
			$logger->info($response);			
			die;
		}
		
		
		$_array = array(
			"hasError"=>false,
			"shipmentCode"=>$this->getShipmentID($orderCode),
			"shipmentItems"=>$_uitems
		);
		
		header('Content-Type: application/json');
		echo $response = \Zend_Json::encode($_array);
		$logger->info($response);			
		die;
    }
	
	public function getShipmentID($orderId)
	{
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->orderRepository = $this->_objectManager->create('\Magento\Sales\Api\OrderRepositoryInterface');
		
		$order = $this->orderRepository->get($orderId);
		$shipmentCollection = $order->getShipmentsCollection();
		$shipmentId = [];
		foreach ($shipmentCollection as $shipment) {
			$shipmentId[] = $shipment->getId();
		}
		return implode(",",$shipmentId);
	}
}