<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Model;

class CancelorderManagement implements \Papa\Restapi\Api\CancelorderManagementInterface
{

    /**
     * {@inheritdoc}
     */
    public function postCancelorder($order_id)
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($order_id);
		
		$this->__objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->orderManagement = $this->__objectManager->create('\Magento\Sales\Api\OrderManagementInterface');
		
		$orderdata  = $order->getData();
        $order_status = $orderdata["status"];       
	   
        if($order_status == "pending"){
            $this->orderManagement->cancel($order_id); 
           
		   
			$_array = array( 
				"hasError"=>false,
				"message"=>'Order Cancelled successfully'
			);
			
			header('Content-Type: application/json');
			echo $response = \Zend_Json::encode($_array);die;
			
			
        }
        else{
			
			$_array = array( 
				"hasError"=>true,
				"message"=>'We cant Cancel this order at this time'
			);
			
			header('Content-Type: application/json');
			echo $response = \Zend_Json::encode($_array);die;
           
        }	
		
		
    }
}

