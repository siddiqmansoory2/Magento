<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Model;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;


class GetinvoiceurlManagement implements \Papa\Restapi\Api\GetinvoiceurlManagementInterface
{

    /**
     * {@inheritdoc}
     */
	 
	/**
	* @return mixed[]
	*/ 
	
	public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        Transaction $transaction
    )
    {
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
		
    }

	
    public function putGetinvoiceurl($shipmentCode,$orderCode)
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
			
			$this->createInvoice($orderCode);
		
			$order = $this->orderRepository->get($orderCode);
			
			$invoiceIncrementID='';
			$invoiceCreatedAt='';
			
			foreach ($order->getInvoiceCollection() as $invoice) {
				$invoiceIncrementID = $invoice->getIncrementId();
				$invoiceCreatedAt = $invoice->getCreatedAt();
			}	
				
			if(empty($invoiceIncrementID)){ 
				
				$_array = array( 
					"hasError"=>false,
					"message"=>'Invoice not found.'
				);
				
				header('Content-Type: application/json');
				echo $response = \Zend_Json::encode($_array);die;
			}  
			
			$_uitems=array();
			
			foreach ($order->getAllVisibleItems() AS $orderItem) {
				
				if ($orderItem->getParentItem()){
					continue;
				}
				
				
				$_uitems[]=array(
					"channelSkuCode"=>$orderItem->getSku(),
					"orderItemCode"=>$orderItem->getSku(),
					"quantity"=>$orderItem->getQtyOrdered()*1,
					"netTaxAmountPerUnit"=>0,
					"netTaxAmountTotal"=>0,
					"baseSellingPricePerUnit"=>$orderItem->getPrice()*1,
					"baseSellingPriceTotal"=>$orderItem->getPrice()*$orderItem->getQtyOrdered()*1,
					"actualSellingPricePerUnit"=>$orderItem->getPrice()*1,
					"actualSellingPriceTotal"=>$orderItem->getPrice()*$orderItem->getQtyOrdered()*1,
					"taxItems"=>array(
						array(
							"type"=>"SGST",
							"rate"=>0,
							"taxPerUnit"=>0,
							"taxTotal"=>0
						),array(
							"type"=>"SGST",
							"rate"=>0,
							"taxPerUnit"=>0,
							"taxTotal"=>0
						),
					),
				);
			} 
			
			
			$_array=array(
				"hasError"=>false,
				"invoiceCode"=>$invoiceIncrementID,
				"invoiceUrl"=>"",
				"invoiceDate"=>gmdate(DATE_W3C,strtotime($invoiceCreatedAt)),
				"invoiceDetails"=>$_uitems,				
			);	
			
			
			try {
				
				$this->_sobjectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$UrlInterface = $this->_sobjectManager->create('\Magento\Framework\UrlInterface');
			
				$url = $UrlInterface->getUrl('papabrands/bluedart/Createinvoice');
				
				
				$param = array();
				$param['order_id']=$orderCode;
				$param['shipmentCode']=$shipmentCode;
				
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
					echo $response = \Zend_Json::encode($_array);die;
					
				}else{
					$_array['invoiceUrl'] = rtrim($UrlInterface->getUrl('media/bluredart_pdf/'.$response),"/");
				}
				curl_close($curl);
				
			} catch (\Exception $e) {
				
				$_array = array(
					"hasError"=>true,
					"message"=>$e->getMessage()
				);
				
				header('Content-Type: application/json');
				echo $response = \Zend_Json::encode($_array);die;
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
	
	public function createInvoice($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        if ($order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->save();
            $transactionSave = $this->transaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();
            $this->invoiceSender->send($invoice);
            $order->addStatusHistoryComment(
                __('Notified customer about invoice creation #%1.', $invoice->getId())
            )
                ->setIsCustomerNotified(true)
                ->save();
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
				return true;
			}	
			
		}
		return false;
		return implode(",",$shipmentId);
	}
	
}