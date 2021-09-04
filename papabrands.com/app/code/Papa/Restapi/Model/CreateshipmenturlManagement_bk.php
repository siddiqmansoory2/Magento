<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Model;

class CreateshipmenturlManagement implements \Papa\Restapi\Api\CreateshipmenturlManagementInterface
{

    /**
     * {@inheritdoc}
     */
    public function putCreateshipmenturl($orderCode,$locationCode,$shipmentId,$dimensions,$invoice,$shipmentItems)
    {
				
        /*$_array=array(
			"hasError"=>false,
			"shipmentCode"=>"ship1234",
			"shipmentItems"=>array(
				"channelSkuCode"=>"100000789701",
				"orderItemCode"=>"item123",
				"quantity"=>7,
			),array(
				"channelSkuCode"=>"100000789702",
				"orderItemCode"=>"item124",
				"quantity"=>5,
			)
			
		);
		
        $paramaters = json_encode($_array);
		return $paramaters;*/
		
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		/*// Load the order increment ID
		$order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementID($incrementid);

		// OR
		$order = $this->_objectManager->create('Magento\Sales\Model\Order')
			->loadByAttribute('increment_id', '000000001');


		//load by order */
		$order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderCode);
		
		/*Check if order can be shipped or has already shipped*/
		if (! $order->canShip()) {
			
			$_array = array( 
				"hasError"=>false,
				"message"=>'You can\'t create an shipment.'
			);
			
			header('Content-Type: application/json');
			echo $response = \Zend_Json::encode($_array);die;
		}

		/*Initialize the order shipment object*/
		$this->__objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$convertOrder = $this->__objectManager->create('Magento\Sales\Model\Convert\Order');
		$shipment = $convertOrder->toShipment($order);

		/*Loop through order items*/
		
		$_uitems=array();
		
		foreach ($order->getAllVisibleItems() AS $orderItem) {
			
			if ($orderItem->getParentItem()){
				continue;
			}
			
			
			/* Check if order item has qty to ship or is virtual*/
			if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
				continue;
			}

			$qtyShipped = $orderItem->getQtyToShip();

			/* Create shipment item with qty */
			$shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);

			/* Add shipment item to shipment*/
			$shipment->addItem($shipmentItem);
			
			$_uitems[]=array(
				"channelSkuCode"=>$orderItem->getSku(),
				"orderItemCode"=>$orderItem->getSku(),
				"quantity"=>$orderItem->getQtyOrdered()*1,
			);
			
			
		}

		/* Register shipment*/
		$shipment->register();

		$shipment->getOrder()->setIsInProcess(true);

		try {
			/* Save created shipment and order*/
			$shipment->save();
			$shipment->getOrder()->save();

			/* Send email*/
			$this->___objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$this->___objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
				->notify($shipment);

			$shipment->save();
		} catch (\Exception $e) {
			
			$_array = array(
				"hasError"=>false,
				"message"=>$e->getMessage()
			);
			
			header('Content-Type: application/json');
			echo $response = \Zend_Json::encode($_array);die;
		}
		
		
		
		/*$paramaters = json_encode(
			array(
				"hasError"=>false,
				"shipmentCode"=>$this->getShipmentID($orderCode),
				"shipmentItems"=>json_encode($_uitems)
			)
			
		);*/
		
		$_array = array(
			"hasError"=>false,
			"shipmentCode"=>$this->getShipmentID($orderCode),
			"shipmentItems"=>$_uitems
		);
		
		header('Content-Type: application/json');
		echo $response = \Zend_Json::encode($_array);die;
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
