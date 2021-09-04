<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Model;
use Magento\Framework\Controller\Result\JsonFactory;


class GetshippinglabelurlManagement implements \Papa\Restapi\Api\GetshippinglabelurlManagementInterface
{

    /**
     * {@inheritdoc}
     */
    public function putGetshippinglabelurl($shipmentCode,$orderCode)
    {
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderCode);
				
		if(!empty($this->getShipmentID($orderCode,$shipmentCode))){
			
			$tracksCollection = $order->getTracksCollection();
			
			$transporter=array();
			$awbNumber=array();
			
			foreach ($tracksCollection->getItems() as $track) {				
				if($track->getParentId()==$shipmentCode){
					$transporter[] = $track->getTitle();
					$awbNumber[] = $track->getTrackNumber();
				}				 
			}		
			
			/*$order->getTracksCollection()->fetchItem();
			$transporter=$order->getTracksCollection()->fetchItem()->getTitle();
			$awbNumber=$order->getTracksCollection()->fetchItem()->getTrackNumber(); */		

			$_array = array("hasError"=>false,"shippingLabelUrl"=>'',"awbNumber"=>implode(",",$awbNumber),"transporter"=>implode(",",$transporter));	
			
			$this->_SoftprodigyobjectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$_bHelper = $this->_SoftprodigyobjectManager->get('Softprodigy\Bluedart\Helper\Data');
			
			
			/*$file_name = 'order_' . $order->getRealOrderId() . '_'.$shipmentCode.'.pdf';*/
			$file_name = 'order_' . $order->getRealOrderId().'.pdf';
			$filename = $_bHelper->getDirPath('media') . "/bluredart_pdf/" . $file_name;
			$listRowId = $_bHelper->hasOrderAwbById($order->getId());
			if (!empty($listRowId)) {					
				$_array['shippingLabelUrl'] = $_bHelper->getMediaUrl(). "bluredart_pdf/" . $file_name;
			}
					
			header('Content-Type: application/json');
			echo $response = \Zend_Json::encode($_array);die;
		}else{
			 
			$_array = array( 
				"hasError"=>false,
				"message"=>'Shipping label not found.'
			);
			
			header('Content-Type: application/json');
			echo $response = \Zend_Json::encode($_array);die;
		} 
		
    }
	
	public function getShipmentID($orderId,$shipmentCode)
	{
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->orderRepository = $this->_objectManager->create('\Magento\Sales\Api\OrderRepositoryInterface');
		
		$order = $this->orderRepository->get($orderId);
		$shipmentCollection = $order->getShipmentsCollection();
		//$shipmentId = [];
		foreach ($shipmentCollection as $shipment) {
			//$shipmentId[] = $shipment->getId();
			if($shipmentCode==$shipment->getId()){
				return $shipment->getId();
			}	
			
		}
		return false;
		return implode(",",$shipmentId);
	}
}