<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Placeorder\Observer\Checkout;

class OnepageControllerSuccessAction implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    
	protected $_objectManager;
	protected $_orderFactory;
	
	public function __construct(
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\UrlInterface $urlInterface,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\ObjectManager\ObjectManager $objectManager
	) {
		$this->_objectManager = $objectManager;        
        $this->_orderFactory = $orderFactory; 
        $this->urlInterface = $urlInterface; 
        $this->asim_link = "https://assure-proxy.increff.com/assuremagic2"; 
        $this->asim_authUsername = "PAPABRANDS_AMV2-1100014382"; 
        $this->authPassword = "6eeb2359-be7c-41c8-85c5-3b27ec053e6e"; 
		
		$this->logisy_link = "https://logisy.tech/api/stores"; 
        $this->x_api_key = "FsyXtilERI527JVTMohMWgHcr5LZEsv5"; 
		
		
		
	}
	
	
	public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
				
		/*$order_ids  = $observer->getEvent()->getOrderIds();*/
		$order = $observer->getEvent()->getData('order');
		
		if ($order) {
			
			
			$order_id = $order->getId();
			
			$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/asim_debug_order-'.$order_id.'.log');
			$logger = new \Zend\Log\Logger();
			$logger->addWriter($writer);
		
			/*$histories = $order->getStatusHistories();
			
			
			foreach($histories as $_histories){
				echo $_histories->getComment();
				echo "--". $_histories->getStatus();echo "<br>";
			}
			echo "<br>";
			echo $order->getIncreffState();
			echo $order->getStatus();
			echo $order->getState();die;*/
			
			/*die;*/
			
			/*if(empty($order->getIncreffState()) && $order->getState() == 'new'){*/
			if(empty($order->getIncreffState())){
				

				//Loading order details
				$orderModel         = $this->_orderFactory->create();
				/*$order              = $orderModel->load($order_id);*/
				$shipping_method    = $order->getShippingMethod();
				$order_status       = $order->getStatus();
				
				$payment = $order->getPayment();
				$method = $payment->getMethodInstance();
				
				$methodTitle = $method->getTitle();
				$actualmethod = $payment->getMethod();
				
				/*if($actualmethod=='checkmo'){
					$actualmethod = 'COD';
				}*/
				$actualmethod = 'COD';
				
				/*[COD, NCOD]*/
				
				$billingAddress = $order->getBillingAddress();
				$shippingAddress = $order->getShippingAddress();
				
				
				if($shippingAddress){
					
					$_uitems=array();
					$line_items=array();

					foreach ($order->getAllVisibleItems() as $_item) {
						
						if ($_item->getParentItem()){
							continue;
						} 						
						
						if($_item->getPrice()>0){
							$_uitems[]=array("channelSkuCode"=>$_item->getSku(),
								"orderItemCode"=>$_item->getSku(),
								"quantity"=>$_item->getQtyOrdered(),
								"sellerDiscountPerUnit"=>0,
								"channelDiscountPerUnit"=>0,
								"sellingPricePerUnit"=>$_item->getPrice(),
								"shippingChargePerUnit"=>0,
								"giftOptions"=>array(
									"giftwrapRequired"=>false,
									"giftMessage"=>"",
									"giftChargePerUnit"=>0
							));
							
							$line_items[]=array("id"=>$_item->getSku(),
								"sku"=>$_item->getSku(),
								"quantity"=>$_item->getQtyOrdered(),
								"name"=>$_item->getName(),
								"title"=>$_item->getName(),
								"grams"=>0,
								"total_tax"=>$_item->getTaxAmount(),
								"total_discounts"=>"0.00",
								"price_without_tax"=>$_item->getRowTotal(),
								"total_price"=>$_item->getRowTotalInclTax(),
								"product_id"=>$_item->getProductId(),
								"variant_id"=>"0"
							);
						}
						
						/*echo $_item->getId();
						echo $_item->getSku();
						echo $_item->getName();
						echo $_item->getProductType();
						echo $_item->getQtyOrdered();
						echo "<pre>";print_r($_item->debug());*/
					}
						

					/*if($order_status == 'processing'){*/
						$curl = curl_init();

						curl_setopt_array($curl, array(
						  CURLOPT_URL => $this->asim_link.'/orders/outward',
						  CURLOPT_RETURNTRANSFER => true,
						  CURLOPT_ENCODING => '',
						  CURLOPT_MAXREDIRS => 10,
						  CURLOPT_TIMEOUT => 0,
						  CURLOPT_FOLLOWLOCATION => false,
						  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						  CURLOPT_CUSTOMREQUEST => 'POST',
						  CURLOPT_POSTFIELDS =>'{
						   "parentOrderCode":"'.$order_id.'",
						   "locationCode":"110",
						   "orderCode":"'.$order_id.'",
						   "orderTime":"'.gmdate(DATE_W3C).'",
						   "orderType":"SO",
						   "isPriority":false,
						   "gift":false,
						   "onHold":false,
						   "qcStatus":"PASS",
						   "dispatchByTime":"'.gmdate(DATE_W3C).'",
						   "startProcessingTime":"'.gmdate(DATE_W3C).'",
						   "paymentMethod":"'.$actualmethod.'",
						   "shippingAddress":{
							  "name":"'.$shippingAddress->getFirstname().'",
							  "line1":"'.implode(",",$shippingAddress->getStreet()).'",
							  "line2":"'.implode(",",$shippingAddress->getStreet()).'",
							  "line3":"",
							  "city":"'.$shippingAddress->getCity().'",
							  "state":"'.$shippingAddress->getRegion().'",
							  "zip":"'.$shippingAddress->getPostcode().'",
							  "country":"'.$shippingAddress->getCountryId().'",
							  "email":"'.$shippingAddress->getEmail().'",
							  "phone":"'.$shippingAddress->getTelephone().'"
						   },
						   "billingAddress":{
							  "name":"'.$billingAddress->getFirstname().'",
							  "line1":"'.implode(",",$billingAddress->getStreet()).'",
							  "line2":"'.implode(",",$billingAddress->getStreet()).'",
							  "line3":"",
							  "city":"'.$billingAddress->getCity().'",
							  "state":"'.$billingAddress->getRegion().'",
							  "zip":"'.$billingAddress->getPostcode().'",
							  "country":"'.$billingAddress->getCountryId().'",
							  "email":"'.$billingAddress->getEmail().'",
							  "phone":"'.$billingAddress->getTelephone().'"
						   },
						   "orderItems":'.json_encode($_uitems).'
						}',
							CURLOPT_HTTPHEADER => array(
								'Content-Type: application/json',
								'authUsername: '.$this->asim_authUsername,
								'authPassword: '.$this->authPassword,
								'Accept: application/json'
							  ),
						));

						$response = curl_exec($curl);
						curl_close($curl);
						
						$logger->info('{
						   "parentOrderCode":"'.$order_id.'",
						   "locationCode":"110",
						   "orderCode":"'.$order_id.'",
						   "orderTime":"'.gmdate(DATE_W3C).'",
						   "orderType":"SO",
						   "isPriority":false,
						   "gift":false,
						   "onHold":false,
						   "qcStatus":"PASS",
						   "dispatchByTime":"'.gmdate(DATE_W3C).'",
						   "startProcessingTime":"'.gmdate(DATE_W3C).'",
						   "paymentMethod":"'.$actualmethod.'",
						   "shippingAddress":{
							  "name":"'.$shippingAddress->getFirstname().'",
							  "line1":"'.implode(",",$shippingAddress->getStreet()).'",
							  "line2":"'.implode(",",$shippingAddress->getStreet()).'",
							  "line3":"",
							  "city":"'.$shippingAddress->getCity().'",
							  "state":"'.$shippingAddress->getRegion().'",
							  "zip":"'.$shippingAddress->getPostcode().'",
							  "country":"'.$shippingAddress->getCountryId().'",
							  "email":"'.$shippingAddress->getEmail().'",
							  "phone":"'.$shippingAddress->getTelephone().'"
						   },
						   "billingAddress":{
							  "name":"'.$billingAddress->getFirstname().'",
							  "line1":"'.implode(",",$billingAddress->getStreet()).'",
							  "line2":"'.implode(",",$billingAddress->getStreet()).'",
							  "line3":"",
							  "city":"'.$billingAddress->getCity().'",
							  "state":"'.$billingAddress->getRegion().'",
							  "zip":"'.$billingAddress->getPostcode().'",
							  "country":"'.$billingAddress->getCountryId().'",
							  "email":"'.$billingAddress->getEmail().'",
							  "phone":"'.$billingAddress->getTelephone().'"
						   },
						   "orderItems":'.json_encode($_uitems).';
						}');
						
						
						if(!empty($response)){
							$logger->info($response);
						}else{
							$order->setIncreffState("order_placed");
							
							
							/*Logisy Order Placed*/
							
							/**/
							
							$curl_logisy = curl_init();

							curl_setopt_array($curl_logisy, array(
								CURLOPT_URL => $this->logisy_link.'/order/create/',
								CURLOPT_RETURNTRANSFER => true,
								CURLOPT_ENCODING => '',
								CURLOPT_MAXREDIRS => 10,
								CURLOPT_TIMEOUT => 0,
								CURLOPT_FOLLOWLOCATION => false,
								CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
								CURLOPT_CUSTOMREQUEST => 'POST',
								CURLOPT_POSTFIELDS =>'{
									"id": "'.$order_id.'",
									"note": "",
									"tags": "",
									"first_name": "'.$shippingAddress->getFirstname().'",
									"last_name": "'.$shippingAddress->getLastname().'",
									"email": "'.$order->getCustomerEmail().'",
									"phone": "'.$shippingAddress->getTelephone().'",
									"currency": "'.$order->getOrderCurrencyCode().'",
									"shipping_cost": "'.$order->getShippingAmount().'",
									"total_tax": "'.($order->getTaxAmount()*1).'",
									"total_discounts": "'.$order->getDiscountAmount().'",
									"price_without_tax": "'.(($order->getGrandTotal()*1)-($order->getTaxAmount()*1)).'",
									"total_price": "'.($order->getGrandTotal()*1).'",
									"created_at": "'.gmdate(DATE_W3C).'",
									"line_items": '.json_encode($line_items).',
									"checkout_id": "'.$order_id.'",
									"source_name": "web",
									"order_number": "'.$order_id.'",
									"discount_codes": '.json_encode(array()).',
									"billing_address": {
										"id": "'.$billingAddress->getCustomerAddressId().'",
										"zip": "'.$billingAddress->getPostcode().'",
										"city": "'.$billingAddress->getCity().'",
										"phone": "'.$billingAddress->getTelephone().'",
										"country": "'.$this->getCountryName($billingAddress->getCountryId()).'",
										"default": false,
										"address1": "'.implode(',',$billingAddress->getStreet()).'",
										"address2": "'.implode(',',$billingAddress->getStreet()).'",
										"province": "'.$billingAddress->getRegion().'",
										"country_code": "'.$billingAddress->getCountryId().'",
										"country_name": "'.$this->getCountryName($billingAddress->getCountryId()).'",
										"province_code": "'.$billingAddress->getRegionId().'",
										"latitude": "",
										"longitude": ""
									},
									"financial_status": "'.$order->getStatus().'",
									"shipment_tracking_id": "",
									"shipment_company": "",
									"shipment_tracking_url": "'.$this->urlInterface->getUrl('sales/order/history').'",
									"is_delivered": true,
									"is_rto": false,
									"cancelled_at": null,
									"order_status_url": "'.$this->urlInterface->getUrl('sales/order/history').'",
									"shipping_address": {
										"id": "'.$shippingAddress->getCustomerAddressId().'",
										"zip": "'.$shippingAddress->getPostcode().'",
										"city": "'.$shippingAddress->getCity().'",
										"phone": "'.$shippingAddress->getTelephone().'",
										"country": "'.$this->getCountryName($shippingAddress->getCountryId()).'",
										"default": false,
										"address1": "'.implode(',',$shippingAddress->getStreet()).'",
										"address2": "'.implode(',',$shippingAddress->getStreet()).'",
										"province": "'.$shippingAddress->getRegion().'",
										"country_code": "'.$shippingAddress->getCountryId().'",
										"country_name": "'.$this->getCountryName($shippingAddress->getCountryId()).'",
										"province_code": "'.$shippingAddress->getRegionId().'",
										"latitude": "",
										"longitude": ""
									}, 
									"payment_gateway_names": '.json_encode(array($actualmethod)).',
									"buyer_accepts_marketing": true,
									"device_id": "",
									"referring_site": "",
									"browser_ip": "'.$this->getIP().'"
								}',
								CURLOPT_HTTPHEADER => array(
									'X-API-Key: '.$this->x_api_key,
									'Content-Type: application/json'
								),
							));

							$response_logisy = curl_exec($curl_logisy);
							curl_close($curl_logisy);
							
							
							if($response_logisy){
								$writer_logisy = new \Zend\Log\Writer\Stream(BP . '/var/log/logisy_order-'.$order_id.'.log');
								$logger_logisy = new \Zend\Log\Logger();
								$logger_logisy->addWriter($writer_logisy);
								
								/*$logger_logisy->info('{
									"id": "'.$order_id.'",
									"note": "",
									"tags": "",
									"first_name": "'.$shippingAddress->getFirstname().'",
									"last_name": "'.$shippingAddress->getLastname().'",
									"email": "'.$order->getCustomerEmail().'",
									"phone": "'.$shippingAddress->getTelephone().'",
									"currency": "'.$order->getOrderCurrencyCode().'",
									"shipping_cost": "'.$order->getShippingAmount().'",
									"total_tax": "'.($order->getTaxAmount()*1).'",
									"total_discounts": "'.$order->getDiscountAmount().'",
									"price_without_tax": "'.(($order->getGrandTotal()*1)-($order->getTaxAmount()*1)).'",
									"total_price": "'.($order->getGrandTotal()*1).'",
									"created_at": "'.gmdate(DATE_W3C).'",
									"line_items": '.json_encode($line_items).',
									"checkout_id": "'.$order_id.'",
									"source_name": "web",
									"order_number": "'.$order_id.'",
									"discount_codes": '.json_encode(array()).',
									"billing_address": {
										"id": "'.$billingAddress->getCustomerAddressId().'",
										"zip": "'.$billingAddress->getPostcode().'",
										"city": "'.$billingAddress->getCity().'",
										"phone": "'.$billingAddress->getTelephone().'",
										"country": "'.$this->getCountryName($billingAddress->getCountryId()).'",
										"default": false,
										"address1": "'.implode(',',$billingAddress->getStreet()).'",
										"address2": "'.implode(',',$billingAddress->getStreet()).'",
										"province": "'.$billingAddress->getRegion().'",
										"country_code": "'.$billingAddress->getCountryId().'",
										"country_name": "'.$this->getCountryName($billingAddress->getCountryId()).'",
										"province_code": "'.$billingAddress->getRegionId().'",
										"latitude": "",
										"longitude": ""
									},
									"financial_status": "'.$order->getStatus().'",
									"shipment_tracking_id": "",
									"shipment_company": "",
									"shipment_tracking_url": "'.$this->urlInterface->getUrl('sales/order/history').'",
									"is_delivered": true,
									"is_rto": false,
									"cancelled_at": null,
									"order_status_url": "'.$this->urlInterface->getUrl('sales/order/history').'",
									"shipping_address": {
										"id": "'.$shippingAddress->getCustomerAddressId().'",
										"zip": "'.$shippingAddress->getPostcode().'",
										"city": "'.$shippingAddress->getCity().'",
										"phone": "'.$shippingAddress->getTelephone().'",
										"country": "'.$this->getCountryName($shippingAddress->getCountryId()).'",
										"default": false,
										"address1": "'.implode(',',$shippingAddress->getStreet()).'",
										"address2": "'.implode(',',$shippingAddress->getStreet()).'",
										"province": "'.$shippingAddress->getRegion().'",
										"country_code": "'.$shippingAddress->getCountryId().'",
										"country_name": "'.$this->getCountryName($shippingAddress->getCountryId()).'",
										"province_code": "'.$shippingAddress->getRegionId().'",
										"latitude": "",
										"longitude": ""
									}, 
									"payment_gateway_names": '.json_encode(array($actualmethod)).',
									"buyer_accepts_marketing": true,
									"device_id": "",
									"referring_site": "",
									"browser_ip": "'.$this->getIP().'"
								}');*/
								
								
								$logger_logisy->info($response_logisy);
							}
							
							
							/*Logisy Order Placed*/
							
						}
					
					
				}
				

				
					
				/*}*/
			
			}
			
			
			if($order->getState() == 'canceled' || $order->getState() == 'closed') {
				
				
				$_uitems=array();
				
				if($order_id=="94491"){
					$_uitems[]=array("channelSkuCode"=>'94833563',
						"orderItemCode"=>'94833563',
						"cancelledQuantity"=>1,
						"channelSkuCode"=>'94833563'
					);
				}else{
					foreach ($order->getAllVisibleItems() as $_item) {

						if ($_item->getParentItem()){
							continue;
						} 		
					
						$_uitems[]=array("channelSkuCode"=>$_item->getSku(),
							"orderItemCode"=>$_item->getSku(),
							"cancelledQuantity"=>$_item->getQtyOrdered()*1,
							"channelSkuCode"=>$_item->getSku()
						);
					}
				}
				
				
				
				
					
				$curl = curl_init();

				curl_setopt_array($curl, array(
				  CURLOPT_URL => $this->asim_link.'/orders/'.$order_id.'/cancel',
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => '',
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 0,
				  CURLOPT_FOLLOWLOCATION => false,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => 'PUT',
					CURLOPT_POSTFIELDS =>'{
					   "locationCode":"110",
					   "orderItems":'.json_encode($_uitems).'
					}',
				  CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json',
					'authUsername: '.$this->asim_authUsername,
					'authPassword: '.$this->authPassword,
					'Accept: application/json'
				  ),
				));
				
				$response = curl_exec($curl);
				
				
				curl_close($curl);
				
				if(!empty($response)){
					$logger->info($response);
				}
				$order->setIncreffState("order_cancelled");
				
				
				/*Logisy Order Cancel*/
				
				/**/
				
				$curl_logisy = curl_init();

				curl_setopt_array($curl_logisy, array(
					CURLOPT_URL => $this->logisy_link.'/order/cancel/',
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 0,
					CURLOPT_FOLLOWLOCATION => false,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_POSTFIELDS =>'{
						"id": "'.$order_id.'",
						"cancelled_at": "'.gmdate(DATE_W3C).'",
						"cancel_reason": "Cancelled by customer"
					}',
					CURLOPT_HTTPHEADER => array(
						'X-API-Key: '.$this->x_api_key,
						'Content-Type: application/json'
					),
				));

				$response_logisy = curl_exec($curl_logisy);
				curl_close($curl_logisy);
				
				
				if($response_logisy){
					$writer_logisy = new \Zend\Log\Writer\Stream(BP . '/var/log/logisy_order-'.$order_id.'.log');
					$logger_logisy = new \Zend\Log\Logger();
					$logger_logisy->addWriter($writer_logisy);					
					$logger_logisy->info($response_logisy);
				}				
				
				/*Logisy Order Cancel*/
				
				
				
				
			}elseif($order->getState() == 'holded') {
				
				$order_id = $order->getId();
				
				$curl = curl_init();

				curl_setopt_array($curl, array(
				  CURLOPT_URL => $this->asim_link.'/orders/'.$order_id,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => '',
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 0,
				  CURLOPT_FOLLOWLOCATION => false,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => 'PUT',
					CURLOPT_POSTFIELDS =>'{
					   "locationCode":"110",
					   "onHold":true,
					   "dispatchByTime":"'.gmdate(DATE_W3C).'",
					   "startProcessingTime":"'.gmdate(DATE_W3C).'"
					}',
				  CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json',
					'authUsername: '.$this->asim_authUsername,
					'authPassword: '.$this->authPassword,
					'Accept: application/json'
				  ),
				));
				
				$response = curl_exec($curl);
				
				curl_close($curl);
				
				if(!empty($response)){
					$logger->info($response);
				}
				$order->setIncreffState("order_holded");
				
			}elseif($order->getIncreffState()=="order_holded") {
									
				$curl = curl_init();

				curl_setopt_array($curl, array(
				  CURLOPT_URL => $this->asim_link.'/orders/'.$order_id.'/unhold/110',
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => '',
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 0,
				  CURLOPT_FOLLOWLOCATION => false,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => 'PUT',
				  CURLOPT_POSTFIELDS =>'{
					   "locationCode":"110",
					   "onHold":true,
					   "dispatchByTime":"'.gmdate(DATE_W3C).'",
					   "startProcessingTime":"'.gmdate(DATE_W3C).'"
					}',
				  CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json',
					'authUsername: '.$this->asim_authUsername,
					'authPassword: '.$this->authPassword,
					'Accept: application/json'
				  ),
				));
				
				$response = curl_exec($curl);
				
				curl_close($curl);
				
				if(!empty($response)){
					$logger->info($response);
				}
				$order->setIncreffState("order_unholded");
			}
		
		}
		$order->save();
		
    }
	
	public function getCountryName($countryId)
    {
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$allowerdContries = $objectManager->get('Magento\Directory\Model\AllowedCountries')->getAllowedCountries() ;
		$countryFactory = $objectManager->get('\Magento\Directory\Model\CountryFactory');
		
        $countryName = '';
        $country = $countryFactory->create()->loadByCode($countryId);
        if ($country) {
            $countryName = $country->getName();
        }
        return $countryName;
    }
	
	public function getIP(){
		$objctManager = \Magento\Framework\App\ObjectManager::getInstance();
		$remote = $objctManager->get('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress');
		return $remote->getRemoteAddress();		
	}
	
}

