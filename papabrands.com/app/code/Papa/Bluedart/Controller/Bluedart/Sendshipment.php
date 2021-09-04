<?php 
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Bluedart\Controller\Bluedart;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Psr\Log\LoggerInterface as Logger;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Softprodigy\Bluedart\Model\Mail\TransportBuilder;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
/**
 * Description of Sendshipment
 *
 * @author mannu
 */
class Sendshipment extends \Magento\Backend\App\Action {

    const XML_PATH_EMAIL_RECIPIENT = 'contacts/email/recipient_email';
    const XML_PATH_EMAIL_SENDER = 'contacts/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE = 'contacts/email/email_template';
    const XML_PATH_EMAIL_PDF_SENDER = 'sales_email/invoice/pdf_sender';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     *
     * @var Softprodigy\Bluedart\Helper\Data 
     */
    protected $__helper;
    protected $messageManager;
    protected $transportBuilder;
    protected $orderModel;
    
    
    /**
     * @var ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
    Context $context, PageFactory $resultPageFactory, 
            ShipmentSender $shipmentSender,
            \Magento\Sales\Model\Order $orderModel,
            \Magento\Framework\Message\ManagerInterface $messageManager, \Softprodigy\Bluedart\Helper\Data $__helper, Logger $logger, TransportBuilder $transportBuilder
    ) {
        parent::__construct($context);
        $this->__helper = $__helper;
        $this->messageManager = $messageManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->transportBuilder = $transportBuilder;
        $this->orderModel = $orderModel;
        //$this->shipmentLoader = $shipmentLoader;
        $this->shipmentSender = $shipmentSender;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed() {
        return true;
        return $this->_authorization->isAllowed('Softprodigy_Bluedart::Bluedart');
    }
    /**
     * Save shipment and order in one transaction
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->_objectManager->create(
            'Magento\Framework\DB\Transaction'
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute() {
		
		//header('Content-Type: application/json');
		
		$shipment__id='';
		
        try {
            $errors = [];
           
            $param = $this->getRequest()->getParams();
			
			//print_r($param);
			
			
			/*$param['order_id']='94467';*/
			$param['shiporder']='yes';
			$param['shipmode']='A';
			$param['shipment']['breadth']='10';
            $param['shipment']['height']='10';
            $param['shipment']['length']='10';
            $param['shipment']['weight']='10';
			$shipmentItems=$param['shipmentItems'];
			//$shipmentItems=array();
			
			
			if(!(is_array($param) && array_key_exists('order_id',$param))){
				$_array = array( 
					"hasError"=>true,
					"message"=>'order_id is missing.'
				);
				
				header('Content-Type: application/json');
				echo $response = \Zend_Json::encode($_array);die;
			}
			
			
            $helper = $this->__helper;

            if ($helper->getStoreConfig('Softprodigy_Bluedart/general/enabled')) {
                $order = $this->orderModel->load($param['order_id']); //load order by order id 

                $shipping_address = $order->getShippingAddress();
                if(!$shipping_address or empty($shipping_address)){
					
					$_array = array( 
						"hasError"=>true,
						"message"=>"Sorry! Could not send consignment for this order"
					);
					
					header('Content-Type: application/json');
					echo $response = \Zend_Json::encode($_array);die;
					
                } 
                $shipping_address->getTelephone();
                $shipping_address->getPostcode();
                $cust_name = $shipping_address->getFirstname() . ' ' . $shipping_address->getLastname();
                $order->getIncrementId();
                $payment_method_code = $order->getPayment()->getMethodInstance()->getCode(); //cashondelivery
                $payment_title = $order->getPayment()->getMethodInstance()->getTitle(); //cashondelivery


                $collectableAmount = '';
                $SubProductCode = 'p';
                $pdf_method = "PREPAID";
                if ($payment_method_code == 'cashondelivery') {
                    $collectableAmount = $order->getGrandTotal();
                    $SubProductCode = 'c';
                    $pdf_method = "CASH ON DELIVERY (COD)";
                }

                //echo '<pre>';
                //echo 'col - '.$collectableAmount; 
                $ordered_items = $order->getAllVisibleItems();
                $mrp = 0;
                $commodityDetail = array();
                $i = 1;
                $qty = 0;
                //$weight = 10;
                $specialInstruction = '';
                $orItems = [];
                
                foreach ($ordered_items as $item) {
					
					if(array_key_exists($item->getSku(),$shipmentItems)){				
						$getQtyOrdered=$shipmentItems[$item->getSku()];
						
						if ($item->getParentItemId()) {
							//$item = $item->getParentItem();
						}

						$item->getItemId(); //product id     
						//var_dump($item->getProductId());  die;
						$item->getSku();
						$qty = $qty + ($getQtyOrdered*1); //ordered qty of item     
						$mrp = $mrp + $item->getprice();
						$orItems['items'][$item->getItemId()] =  ($getQtyOrdered*1);
						//var_dump($item->getWeight()); die;
						//$weight += ($item->getWeight() * $item->getQtyOrdered());

						//$commodityDetail['CommodityDetail'.$i] =  preg_replace('/[^a-zA-Z0-9]/', ' ', $item->getName());
						$commodityDetail['CommodityDetail' . $i] = preg_replace('/[^a-zA-Z0-9]/', ' ', substr($item->getName(), 0, 30));
						//$specialInstruction = $commodityDetail['CommodityDetail' . $i] . ' ' . $specialInstruction;
						$i++;						
					}
                    
                }
                 
                //var_dump($weight); die;
                $blueAddress = ($helper->getStoreConfig('Softprodigy_Bluedart/general/store_contact_addr'));
                $line_store_address = explode("\n", $blueAddress);


                $bluedartKey = $helper->getStoreConfig('Softprodigy_Bluedart/general/licence_key');
                $loginId = $helper->getStoreConfig('Softprodigy_Bluedart/general/login_id');

                $storeName = $helper->getStoreConfig('Softprodigy_Bluedart/general/store_name');
                $email = $helper->getStoreConfig('Softprodigy_Bluedart/general/email_id_from');
                $storePhone = $helper->getStoreConfig('Softprodigy_Bluedart/general/contact_number');
                $storePincode = $helper->getStoreConfig('Softprodigy_Bluedart/general/pin_code');
                $customercode = $helper->getStoreConfig('Softprodigy_Bluedart/general/customer_code');
                $vandercode = $helper->getStoreConfig('Softprodigy_Bluedart/general/vander_code');
                $originarea = $helper->getStoreConfig('Softprodigy_Bluedart/general/origin_area');
                $tin = $helper->getStoreConfig('Softprodigy_Bluedart/general/tin_no');

                $dimension_breadth = $param['shipment']['breadth'];
                $dimension_height = $param['shipment']['height'];
                $dimension_length = $param['shipment']['length'];
                $actualWeight = $param['shipment']['weight'];
                $actualWeight = number_format(floatval($actualWeight)/1000,4);
                $dimension = $dimension_length . '*' . $dimension_breadth . '*' . $dimension_height;

                if ($collectableAmount)
                    $mrp = $collectableAmount;

                /* -------- Start Blue Dart API--------- */

                if ($helper->getStoreConfig('Softprodigy_Bluedart/general/sandbox') == 1)
                    $ApiUrl = 'https://netconnect.bluedart.com/ver1.8/Demo/ShippingAPI/WayBill/WayBillGeneration.svc'; //-----For Sandbox-----
                else
                    $ApiUrl = 'https://netconnect.bluedart.com/ver1.8/ShippingAPI/WayBill/WayBillGeneration.svc'; //----For Live----

                //echo $ApiUrl . '?wsdl';
                
                $soap = new \Softprodigy\Bluedart\Controller\DebugSoapClient($ApiUrl . '?wsdl', array(
                    'trace' => 1,
                    'style' => SOAP_DOCUMENT,
                    'use' => SOAP_LITERAL,
                    'soap_version' => SOAP_1_2
                ));

                $soap->__setLocation($ApiUrl);

                $soap->sendRequest = true;
                $soap->printRequest = false;
                $soap->formatXML = true;

                $actionHeader = new \SoapHeader('http://www.w3.org/2005/08/addressing', 'Action', 'http://tempuri.org/IWayBillGeneration/GenerateWayBill', true);
                $soap->__setSoapHeaders($actionHeader);
                #echo "end of Soap 1.2 version (WSHttpBinding)  setting";
                $street = $shipping_address->getStreet();
                
                $invoiceIncrementID = '';
                if ($order->hasInvoices()) {
                    foreach ($order->getInvoiceCollection() as $inv) {
                        $invoiceIncrementID = $inv->getIncrementId();
                        //$invDate = date('d-M-Y', strtotime($inv->getCreatedAt()));
                    }
                }
                
                $params = array(
                    'Request' =>
                    array(
                        'Consignee' =>
                        array(
                            'ConsigneeAddress1' => $street[0],
                            'ConsigneeAddress2' => @$street[1],
                            'ConsigneeAddress3' => '',
                            'ConsigneeAttention' => '',
                            'ConsigneeMobile' => $shipping_address->getTelephone(),
                            'ConsigneeName' => $cust_name,
                            'ConsigneePincode' => $shipping_address->getPostcode(),
                            'ConsigneeTelephone' => $shipping_address->getTelephone(),
                        ),
                        'Services' =>
                        array(
                            'ActualWeight' => $actualWeight,
                            'CollectableAmount' => $collectableAmount,
                            'Commodity' => $commodityDetail,
                            'CreditReferenceNo' => $order->getIncrementId().rand(234,2343),
                            'DeclaredValue' => $mrp,
                            'Dimensions' =>
                            array(
                                'Dimension' =>
                                array(
                                    'Breadth' => $dimension_breadth,
                                    'Count' => '1',
                                    'Height' => $dimension_height,
                                    'Length' => $dimension_length
                                ),
                            ),
                            'InvoiceNo' => $invoiceIncrementID,
                            'PackType' => '',
                            'PickupDate' => date('Y-m-d'),
                            'PickupTime' => '1800', //(optional)
                            'PieceCount' => '1', //(#default)
                            'ProductCode' => 'A', //(#default)
                            'ProductType' => 'Dutiables', //(#default)
                            'SpecialInstruction' => $specialInstruction,
                            'SubProductCode' => $SubProductCode, //(for prepaid ordered it will p & #for COD it will be c)
                        ),
                        'Shipper' =>
                        array(
                            'CustomerAddress1' => $line_store_address[0],
                            'CustomerAddress2' => @$line_store_address[1],
                            'CustomerAddress3' => @$line_store_address[2],
                            'CustomerAddress4' => @$line_store_address[4],
                            'CustomerAddress5' => @$line_store_address[5],
                            'CustomerCode' => $customercode,
                            'CustomerEmailID' => $email,
                            'CustomerMobile' => $storePhone,
                            'CustomerName' => $storeName,
                            'CustomerPincode' => $storePincode,
                            'CustomerTelephone' => $storePhone,
                            'IsToPayCustomer' => '',
                            'OriginArea' => $originarea,
                            'Sender' => '',
                            'VendorCode' => $vandercode
                        ),
                        'SubShipper' =>
                        array(
                            'CustomerAddress1' => $line_store_address[0],
                            'CustomerAddress2' => @$line_store_address[1],
                            'CustomerAddress3' => @$line_store_address[2],
                            'CustomerAddress4' => @$line_store_address[4],
                            'CustomerAddress5' => @$line_store_address[5],
                            'CustomerCode' => $customercode,
                            'CustomerEmailID' => $email,
                            'CustomerMobile' => $storePhone,
                            'CustomerName' => $storeName,
                            'CustomerPincode' => $storePincode,
                            'CustomerTelephone' => $storePhone,
                            'IsToPayCustomer' => '',
                            'OriginArea' => $originarea,
                            'Sender' => '',
                            'VendorCode' => $vandercode
                        )
                    ),
                    'Profile' =>
                    array(
                        'Api_type' => 'S',
                        'LicenceKey' => $bluedartKey,
                        'LoginID' => $loginId,
                        'Version' => '1.3')
                );
             

                //echo "Before";
                // Here I call my external function
                $result = $soap->__soapCall('GenerateWayBill', array($params));

                /* echo "Generated Waybill number " + $result->GenerateWayBillResult->AWBNo;
                  echo "<br>";
                  echo $result->GenerateWayBillResult->Status->WayBillGenerationStatus->StatusInformation ;

                  echo "<br>";
                  echo '<h2>Result</h2><pre>'; print_r($result); die; //echo '</pre>';die; */

                $data = $result->GenerateWayBillResult->AWBPrintContent;
                $error = $result->GenerateWayBillResult->IsError;
				 
                if (!empty($error)) {

                    $check_err = $result->GenerateWayBillResult->Status->WayBillGenerationStatus; 
					
					
					$_array = array( 
						"hasError"=>true,
						"message"=>$check_err
					);
					
					header('Content-Type: application/json');
					echo $response = \Zend_Json::encode($_array);die;
					
					//->StatusInformation ;

                    if (is_array($check_err)) {
                        $k = 1;
                        $error_msg = '';
                        
                        if(count($check_err)>0)
                            $error_msg .= '<ul>';
                        
                        foreach ($check_err as $err) {
                            $error_msg .='<li>'. $err->StatusInformation.'</li>';
                        }
                        
                        if(count($check_err)>0)
                            $error_msg .= '</ul>';
                    } else
                        $error_msg = $result->GenerateWayBillResult->Status->WayBillGenerationStatus->StatusInformation;

                    if (!empty($error_msg))
                        $errors['error'] = $error_msg;
                }
                else { 
					/*die('fds');*/
                    /*                     * ******Genarating PDF******** */
                    /*require ('Softprodigy/MPDF6/mPDF.php');*/
					
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
					$rootPath  =  $directory->getRoot(); 
					
                    require ($rootPath. '/app/code/Softprodigy/mpdf-8.0.11/vendor/autoload.php');
                    $order_id = $order->getIncrementId();
                    $AWB_No = $result->GenerateWayBillResult->AWBNo;
                    $des_area = $result->GenerateWayBillResult->DestinationArea;
                    $des_loc = $result->GenerateWayBillResult->DestinationLocation;
                    $order_date = date('M d, Y, h:i:s A', strtotime($order->getCreatedAt()));
                    $address = '';

                    $cust_street = $shipping_address->getStreet();
                    $cust_street = $cust_street[0];

                    $cust_resion = $shipping_address->getRegion();
                    $cust_pin = $shipping_address->getPostcode();
                    $cust_phone = $shipping_address->getTelephone();
					$oneLine_address='';
					
                    foreach ($line_store_address as $add) {
                        $address .= '<p>' . $add . '</p>';
                        $oneLine_address .= $add;
                    }

                    $html_2 = '';
                    if ($payment_method_code == 'cashondelivery') {
                        $html_2 = '<div class="ttl-amnt">
                                            <h2>AMOUNT TO BE COLLECTED <br> Rs. ' . $collectableAmount . '</h2>
                                    </div>';
                    }
					
					$html_3 = '<table style="width: 100%; margin-top: 5px;">
					<thead style="display: table-caption; width: 100%;">
						<tr style="display: flex;">
							<th style="color: #000; font-size: 1.6rem; font-weight: 600; width: 20%; text-align: left; border: 1px solid #000; padding: 5px 10px;"><b>Product</b></th>
							<th style="color: #000; font-size: 1.6rem; font-weight: 600; width: 20%; text-align: left; border: 1px solid #000; padding: 5px 10px;"><b>Qty</b></th>
							<th style="color: #000; font-size: 1.6rem; font-weight: 600; width: 20%; text-align:left; border: 1px solid #000; padding: 5px 10px;"><b>Wt</b></th>
							<th style="color: #000; font-size: 1.6rem; font-weight: 600; width: 20%; text-align:left; border: 1px solid #000; padding: 5px 10px;"><b>Tax</b></th>
							<th style="color: #000; font-size: 1.6rem; font-weight: 600; width: 20%; text-align:left; border: 1px solid #000; padding: 5px 10px;"><b>Price</b></th>
							<th style="color: #000; font-size: 1.6rem; font-weight: 600; width: 20%; text-align: left; border: 1px solid #000; padding: 5px 10px;"><b>Total</b></th>
						</tr>
					</thead>';
					
					

                    /*$html_3 = '<table width="100%" cellspacing="0" cellpadding="8" >
                                <tr>
                                        <td align="center" valign="middle" style="width: 6%;">Sr.</td>
                                        <td align="center" valign="middle" style="width: 10%;">Item Code</td>
                                        <td align="center" valign="middle" style="width: 30%;">Item Description</td>
                                        <td align="center" valign="middle" style="width: 12%;">Quantity</td>
                                        <td align="center" valign="middle" style="width: 12%;">Value</td>
                                        <td align="center" valign="middle" style="width: 12%;">Total Amount</td>
                                </tr>';*/
                    $j = 1;
                    $productsinawb = [];
                    foreach ($ordered_items as $item) {
						
						
						if(array_key_exists($item->getSku(),$shipmentItems)){				
							$getQtyOrdered=$shipmentItems[$item->getSku()];
							
							
							if (!$item->getParentItemId()) {
								$sku = $item->getSku();
								$p_name = $item->getName();
								$p_qty = number_format(($getQtyOrdered*1), 2);
								$p_baseprice = number_format(($item->getBasePrice()*1), 2);
								$p_price = number_format(($item->getPrice()*1), 2);
								$final_price = number_format((($getQtyOrdered*1) * (($item->getPrice()*1))), 2);
								$productsinawb[] = $p_name;
								
								$shipping_label="";
								
								
								$getTaxPercent=$item->getTaxPercent()*1;
								
								if($getTaxPercent>0){
									if($shipping_address->getRegion()=="Karnataka"){
										$shipping_label="CGST Tax ".($getTaxPercent/2)."% <br>SGST Tax ".($getTaxPercent/2)."%";
									}else{
										$shipping_label="IGST TAX ".$getTaxPercent."%";
									}
								}
								
								
								$html_3 .= '<tr style="display: flex;">
									<td style="font-size: 1.3rem; text-align: left; width: 20%; border: 1px solid #000; padding: 5px 10px;"><b>' . $p_name . ' ' . $sku . '</b>
									</td>
									<td style="font-size: 1.3rem; text-align: left; width: 20%; border: 1px solid #000; padding: 5px 10px;"><b>' . $p_qty . '</b></td>
									<td style="font-size: 1.3rem; text-align: left; width: 20%; border: 1px solid #000; padding: 5px 10px;"><b>'.$this->outputWeight($item->getWeight()).'</b></td>
									<td style="font-size: 1.3rem; text-align: left; width: 20%; border: 1px solid #000; padding: 5px 10px;"><b>'.$shipping_label.'</b></td>
									<td style="font-size: 1.3rem; text-align: left; width: 20%; border: 1px solid #000; padding: 5px 10px;"><b>' . $p_price . '</b></td>
									<td style="font-size: 1.3rem; text-align: left; width: 20%; border: 1px solid #000; padding: 5px 10px;"><b>' . $final_price . '</b></td>
								</tr>';
								
								/*$html_3 .= '<tr>
												<td align="center" valign="middle" style="width: 6%;">' . $j . '</td>
												<td align="center" valign="middle" style="width: 10%;">' . $sku . '</td>
												<td align="center" valign="middle" style="width: 30%;">' . $p_name . '</td>
												<td align="center" valign="middle" style="width: 12%;">' . $p_qty . '</td>
												<td align="center" valign="middle" style="width: 12%;">' . $p_price . '</td>
												<td align="center" valign="middle" style="width: 12%;">' . $final_price . '</td>
											</tr>';*/
								$j++;
							}
						}
                        
                    }
                    $grand_total = number_format($order->getGrandTotal()*1, 2);
                    $ship_changes = number_format($order->getShippingAmount()*1, 2);
                    $tax_amt = number_format($order->getTaxAmount()*1, 2);


                    $discount_amt = number_format($order->getDiscountAmount()*1, 2);
                    $dis_html = '';
                    if ($discount_amt) {
                        $dis_html = '<tr style="background: #eaeaea; border: 2px solid #808080; border-top: none; width: 100%;">
                                        <td colspan="2" align="left" valign="middle" style="width: 46%;"></td>
                                        <td colspan="2" align="left" valign="middle" style="width: 24%;"><b>' . __('Discount', $order->getDiscountDescription()) . '</b></td>
                                        <td align="left" valign="middle" style="width: 12%;"><b>' . $discount_amt . '</b></td>
                                    </tr>';
                    }
					
                    $html_3 .= '<tr style="background: #eaeaea; border: 2px solid #808080; border-top: none; width: 100%;">
                                    <td colspan="2" align="left" valign="middle" style="width: 46%;"></td>
                                    <td colspan="2" align="left" valign="middle" style="width: 24%;"><b>Shipping Charges</b></td>
                                    <td align="left" valign="middle" style="width: 12%;"><b>' . $ship_changes . '</b></td>
                                </tr>
                                <tr style="background: #eaeaea; border: 2px solid #808080; border-top: none; width: 100%;">
                                        <td colspan="2" align="left" valign="middle" style="width: 46%;"></td>
                                        <td colspan="2" align="left" valign="middle" style="width: 24%;"><b>Tax Charges</b></td>
                                        <td align="left" valign="middle" style="width: 12%;"><b>' . $tax_amt . '</b></td>
                                </tr>	
                                ' . $dis_html . '
                                <tr style="background: #eaeaea; border: 2px solid #808080; border-top: none; width: 100%;">
                                        <td colspan="2" align="left" valign="middle" style="width: 46%;"></td>
                                        <td colspan="2" align="left" valign="middle" style="width: 24%;"><b>Total</b></td>
                                        <td align="left" valign="middle" style="width: 12%;"><b>' . $grand_total . '</b></td>
                                </tr>	
                        </table>';

                    $invIncrementIDs = '';
                    $invDate = '';
                    if ($order->hasInvoices()) {
                        $invIncrementIDs = array();
                        foreach ($order->getInvoiceCollection() as $inv) {
                            $invIncrementIDs = $inv->getIncrementId();
                            $invDate = date('d-M-Y', strtotime($inv->getCreatedAt()));
                        }
                    }
                    //$order_date = $order->getCreatedAt();
                    $logoUrl = $helper->getMediaUrl() . 'bluedart/' . $helper->getStoreConfig('Softprodigy_Bluedart/general/pdf_logo');
                    //echo $logoUrl;
                    /*$html = '<html>
                    <head>
                    <meta charset="utf-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <title></title>
                    <meta name="description" content="">

                    </head>
                    <body>
                        <div class="main-block">
                            <div class="sectn-top">
                                <div class="log-main">
                                    <img alt="logo" src="' . $logoUrl . '" />
                                </div>
                                <div class="ship-adrs">
                                    <h2>' . $storeName . '</h2>
                                    ' . $address . '
                                    <p>PIN : ' . $storePincode . '</p>
                                    <p>Phone : ' . $storePhone . '</p>
                                    <p>Email : ' . $email . '</p>
                                    <h4>TIN : ' . $tin . '</h4>
                                </div>
                                <div class="inv-dtails">
                                    <p>INVOICE NO  <span>: ' . $invIncrementIDs . '</span></p>
                                    <p>INVOICE DATE <span>: ' . $invDate . '</span></p>
                                    <p>VAT REG NO <span>: </span></p>
                                    <p>CST REG NO <span>: </span></p>
                                </div>
                            </div>

                            <div class="sectn-mid">
                                <div class="ship-adrs border-no">
                                    <h2>DELIVER TO</h2>
                                    <p>' . $cust_name . '<br>' . $cust_street . '</p>
                                    <p>' . $cust_resion . '</p>
                                    <h2>' . $cust_pin . ' - ' . $des_area . '/' . $des_loc . '</h2>
                                    <p>Phone ' . $cust_phone . '</p>

                                </div>
                                <div class="ordr-dtails">
                                    <div class="o-id">
                                        <h2>ORDER ID</h2>
                                        <div class="img-cntr"><barcode code=' . $order_id . ' type="C39" size="1.0" height="2.0" /></div>
                                        <p>' . $order_id . '</p>
                                    </div>
                                    <div class="o-id o-cod">
                                        <h2>' . $pdf_method . '</h2>
                                        <div class="img-cntr"><barcode code=' . $AWB_No . ' type="C39" size="1.0" height="2.0" /></div>
                                        <p style="width:100%; text-align:center;">' . $AWB_No . '</p>
                                    </div>

                                    <div class="p-details">
                                        <p>AWB No. <span>: ' . $AWB_No . '</span></p>
                                        <p>Weight (kgs) <span>: ' . $actualWeight . '</span></p>
                                        <p>Dimensions (cms) <span>: ' . $dimension . '</span></p>
                                        <p>Order ID <span>: ' . $order_id . '</span></p>
                                        <p>Order Date <span>: ' . $order_date . '</span></p>
                                        <p>Pieces <span>: ' . $qty . '</span></p> 
                                    </div>
                                </div>

                                ' . $html_2 . '

                            </div>

                            <div class="tble-btm">
                                    ' . $html_3 . '
                            </div>
                            <p style="width: 100%; text-align:center; font-size: 16px; margin-bottom: 5px;">This is computer generated document, hence does not require signature.</p>


                             <table class="botm-adrss" cellspacing="0" cellpadding="5" border="0">
                             <tr>
                                     <td style="width: 150px" valign="top"><p style="font-size: 18px; font-weight:600; margin:0">Return Address :</p></td>
                                            <td style="width: 800px" valign="top"><p style="font-size: 16px; font-weight:normal; margin:0 0 0 15px">' . $storeName . ', ' . $oneLine_address . ',' . $storePincode . '</p></td>
                                    </tr>
                            </table>
                        </div>
                    </body>
                    </html>'; *///Assign HTML HERE 
					
					
					
					$html='<!doctype html>
						<html>

						<head>

							<meta http-equiv="Content-type" content="text/html; charset=utf-8" />

							<meta content="telephone=no" name="format-detection" />
						</head>

						<body class="body" style="padding:0 !important; margin:0 !important; display:block !important; background:#ffffff; -webkit-text-size-adjust:none;">

							<table width="100%" border="0" cellspacing="0" cellpadding="0" valign="top">

								<tr>

									<td align="left" valign="top">

										<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-left: 2px dashed #000; border-right: 2px dashed #000; padding: 0 5px;">
											<tr align="center">
												<td style="background: #eaeaea;" align="center">
													<img src="images/papa-logo-black.png" alt="logo" width="158px">
												</td>
											</tr>
											<tr>
												<td style="background: #eaeaea; padding: 10px 20px;">
													<p style="margin: 0; text-transform: uppercase; font-size: 1.6rem; font-weight: 600;"><b>'.$pdf_method.'</b></p>
												</td>
											</tr>
											<tr>
												<td style="border-left: 2px solid #b4b4b4; border-right: 2px solid #b4b4b4; border-bottom: #b4b4b4; width: 100%;">
													<table width="100%" border="0" cellspacing="0" cellpadding="0">
														<tr>
															<td style="width: 100%;">
																<table width="100%" border="0" cellspacing="0" cellpadding="0">
																	<tr>
																		<td style="padding: 10px 20px;">
																			<table style="width: 100%;">
																				<tr>
																					<td style="width: 50%">
				<p style="font-size: 1.6rem; font-weight: bold; color: #000; margin-top: 10px; margin-bottom: 0; text-transform: uppercase;">Delivery Address: </p>
				
				<p style="font-size: 1.3rem; font-weight: 600; margin-bottom: 5px; margin-top: 5px;"><b>'.$cust_name.'</b></p>
				<p style="font-size: 1.3rem; font-weight: 600; margin-bottom: 5px; margin-top: 5px;"><b>'.implode(" ",$shipping_address->getStreet()).'<br>'.$shipping_address->getCity().'</b></p>
				<p style="font-size: 1.3rem; font-weight: 600; margin-bottom: 5px; margin-top: 5px;"><b>'.$shipping_address->getRegion().' '.$shipping_address->getPostcode().'</b></p>
																
				<p style="font-size: 1.6rem; font-weight: 500; margin-top: 0; margin-bottom: 0;"><b>Contact Number: ' . $cust_phone . '</b></p>
				
				
																					</td>
																					<td style="width: 50%;" align="right">
																						<p style="text-align: right; font-size: 2.5rem; font-weight: bold;">' . $des_area . '/' . $des_loc . '</p>
																					</td>
																				</tr>
																			</table>
																		</td>
																	</tr>
					<tr>
						<td style="background: #eaeaea; border-top: 1px solid #808080; border-bottom: 1px solid #808080; padding: 10px">
							<p style="font-size: 1.3rem; margin-bottom: 10px; margin-top: 0px; margin-bottom: 0;">
								<b>Courier Name: Blue Dart</b>
							</p>
							<p style="font-size: 1.3rem; margin-top: 5px; margin-bottom: 0; margin-bottom: 0;">
								<b>Courier AWB No: ' . $AWB_No . '</b>
							</p>
							<p style="font-size: 1.3rem; margin-top: 5px; margin-bottom: 0; margin-bottom: 0;">
								<b>Invoice No: ' . $invIncrementIDs . '</b>
							</p>
						</td>
					</tr> 
					
					<tr>
						<td style="padding: 5px 20px 0;">
						   <p style="margin-top: 5px; margin-bottom: 10px; font-size: 1.6rem;"><b>Tracking ID: ' . $AWB_No . '</b>
							</p>
						</td>
					</tr>
					
					
					<tr align="center">
						<td style="padding: 5px 20px 0;" align="center">
							<div class="img-cntr"><barcode code=' . $AWB_No . ' type="C39" size="1.8" height="3.6" /></div>
							<p style="margin: 0; text-align: center; font-size: 1.6rem; font-weight: 600;"><b>' . $AWB_No . '</b></p>
						</td>
					</tr>
					
					
					
		<tr>
			<td style="border: 1px solid #000; padding: 5px 10px;">
				<p style="margin: 0; font-size: 1.6rem;"><span style="font-weight: 600;"><b>Sold By:</b></span> <span style="font-weight: 600;"><b>' . $storeName . ', ' . $oneLine_address . ',' . $storePincode . '</b></p>
			</td>
		</tr>
		<tr>
			<td style="border: 1px solid #000; border-top: 0; padding: 5px 10px;">
				<p style="margin: 0; font-size: 1.6rem;"><b>GSTIN: ' . $tin . '</b></p>
			</td>
		</tr>
		<tr>
			<td>
			
				'.$html_3.'
			
			
				
			</td>
		</tr>
		
		
		
		<tr>
			<td style="padding: 10px 20px 5px;">
			   <div style="margin-top: 5px; border-top: 2px solid #808080; border-bottom: 2px solid #808080; padding: 10px;"> 
				<p style="color: #fff; background: #000; padding: 5px; width: max-content; margin-top: 0; margin-bottom: 5px; font-size: 1.6rem;">Handover to BLUEDART
				</p>
			   </div>
			</td>
		</tr>
		
		<tr>
			<td style="padding: 5px 20px 0;">
				<p style="margin-top: 10px; margin-bottom: 0; font-size: 1.6rem;"><span style="font-weight: bold;">Order ID:</span><b> ' . $order_id . '</b>
			   </div>
			</td>
		</tr>
		
		<tr><td>&nbsp;</td></tr>
		
		<tr align="center">
			<td style="padding: 20px 20px 0;" align="center">
				<div class="img-cntr"><barcode code=' . $order_id . ' type="C39" size="1.8" height="3.6" /></div>
				<p style="margin: 0; text-align: center; font-size: 1.6rem; font-weight: 600;"><b>' . $order_id . '</b></p>
			</td>
		</tr>
		
		
		
		
		
		<tr><td><hr></td></tr>
		<tr><td>&nbsp;</td></tr>
		
		<tr>
			<td style="padding: 5px 20px 0;">
				<p style="font-size: 1.6rem; font-weight: bold; color: #000; margin-top: 10px; margin-bottom: 0; text-transform: uppercase;">Return Address: </p>
				<p style="font-size: 1.6rem; font-weight: 500; margin-top: 0; margin-bottom: 0;"><b>Webkraft Inc (+91-9372221906)</b></p>
				<p style="font-size: 1.6rem; font-weight: 500; margin-top: 0; margin-bottom: 0;"><b>' . $storeName . ', ' . $oneLine_address . ',' . $storePincode . '</b></p>
			</td>
		</tr>
		
		<tr><td>&nbsp;</td></tr>
		<tr><td><hr></td></tr>
		<tr><td>&nbsp;</td></tr>
		
																</table>
															</td>
														</tr>
													</table>
												</td>
											</tr>

										</table>

									</td>

								</tr>

							</table>



						</body>



						</html>';
					
					
                    
                    $awblist = $this->__helper->getAwbModel();  
                    $awblist->setData([
                        'awb_number'=>$AWB_No,
                        'order_id'=>$order->getId(),
                        'order_increment_id'=>$order_id,
                        'city_state'=>$des_area . '/' . $des_loc,
                        'product_details'=> implode(", ", $productsinawb),
                        'awb_weight'=>$actualWeight,
                        'awb_date'=>date('Y-m-d H:i:s'),
                        'created_at'=>date('Y-m-d H:i:s')
                    ]);
                    $awblist->save();
                    
                    //$cssPath = $helper->getDirPath('lib_web') . 'sp_bluedart/css/pdf.css'; //any CSS File you want to Include

                    //$mpdf = new \Mpdf\Mpdf('c', 'B3', '', '', 32, 25, 27, 25, 16, 13);
					$mpdf = new \Mpdf\Mpdf();

                    
                    $mpdf->SetDisplayMode('fullpage');
                    
                    $mpdf->showImageErrors = false;
                    
                    $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list   
                    
                    //$stylesheet = file_get_contents($cssPath);

                    //$mpdf->WriteHTML($stylesheet, 1);    // The parameter 1 tells that this is css/style only and no body/html/text

                    $mpdf->WriteHTML($html, 2);

                    if (!is_dir($helper->getDirPath('media') . 'bluredart_pdf/')) {
                        mkdir($helper->getDirPath('media') . 'bluredart_pdf/', 0777);
                    }
					
					$shipment__id=$this->createShipment($order, $AWB_No, $orItems,$param['shipmentItems']);
					/*$shipment__id='';*/
					
					
                    /*$file_name = 'order_' . $order_id . '_'.$shipment__id.'.pdf';*/
                    $file_name = 'order_' . $order_id.'.pdf';
                    $filename = $helper->getDirPath('media') . "bluredart_pdf/" . $file_name;
                    $mpdf->Output($filename, 'F');
					/*die;*/

                    /*                     * ******End Genarating PDF******** */

                    /*                     * ******Sending PDF To customer******** */

                    $filename = $file_name;
                    $from_mail = "$email";
                    $from_name = "$storeName";
                    $_msg = '';
                    
                    
                     
                    $this->mail_attachment($filename, $from_mail, $from_name, $order);
                    
                    $_msg .= "Consignment has been submitted, AWB no. is : " . $AWB_No;
                    
                    $errors['success'] = $_msg;
                    //echo "Done"; die;
                }
            }else{ 
				$_array = array( 
					"hasError"=>true,
					"message"=>'Consignment cannot submitted'
				);
				
				header('Content-Type: application/json');
				echo $response = \Zend_Json::encode($_array);die;
				
			} //-----End of extension enable If statement------
            //$this->_redirect('/admin/sales_order/view', array('order_id' => $param['order_id']));
            //$this->_redirectUrl($param['shipment_referer']);
        } catch (\Exception $e) {
			
			$_array = array( 
				"hasError"=>true,
				"message"=>$e->getMessage()
			);
			
			header('Content-Type: application/json');
			echo $response = \Zend_Json::encode($_array);die;
			
        }
        
        $errorHtml = '';
        foreach($errors as $msgClass=>$errmsg){
            $errorHtml .= $errmsg;
        }
        
        $reload = false;
        /*if($errors && is_array($errors) && isset($errors['success']) and count($errors['success'])>0)
            $reload = true;*/
		
		
		/*$_array = array( 
			"hasError"=>false,
			"error_flex"=>$errorHtml
		);
		
		header('Content-Type: application/json');
		echo $response = \Zend_Json::encode($_array);die;
        
        echo json_encode(['error_flex'=>$errorHtml,'reload'=>$reload]); die;*/
		
		$_uitems=array();
		
		foreach ($order->getAllVisibleItems() AS $orderItem) {

			if ($orderItem->getParentItem()){
				continue;
			} 	
			
			if(array_key_exists($orderItem->getSku(),$shipmentItems)){				
				$getQtyOrdered=$shipmentItems[$orderItem->getSku()];
				
				/*$_uitems[]=array(
					"channelSkuCode"=>$orderItem->getSku(),
					"orderItemCode"=>$orderItem->getSku(),
					"quantity"=>$orderItem->getQtyOrdered()*1,
				);*/
				$_uitems[]=array(
					"channelSkuCode"=>$orderItem->getSku(),
					"orderItemCode"=>$orderItem->getSku(),
					"quantity"=>$getQtyOrdered,
				);
				
			}
			
					
			
		}
		
		$_array = array(
			"hasError"=>false,
			"shipmentCode"=>$shipment__id,
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
	
    private function createShipment($order, $awbno, $orItems,$shipmentItems){
		
		
		$convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
		$shipment = $convertOrder->toShipment($order);


		foreach ($order->getAllVisibleItems() AS $orderItem) {
			
		    
			if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
		        continue;
		    }
			
			if(array_key_exists($orderItem->getSku(),$shipmentItems)){
				
				$qtyShipped = $shipmentItems[$orderItem->getSku()];
				
				if($orderItem->getQtyToShip()>=$qtyShipped){
					/*$qtyShipped = $orderItem->getQtyToShip();*/
					$shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
					$shipment->addItem($shipmentItem);
				}
			}

		}

		$track = $this->_objectManager->create('\Magento\Sales\Model\Order\Shipment\TrackFactory')->create();
		$track->setNumber($awbno);
        $track->setCarrierCode('custom');
        $track->setTitle('Bluedart');
        $shipment->addTrack($track);

		$shipment->register();
		$shipment->getOrder()->setIsInProcess(true);

	    // Save created shipment and order
	    $shipment->save();
	    $shipment->getOrder()->save();

		$this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')->notify($shipment);

		$shipment->save();
		
		return $shipment->getId();
		
    }

    private function mail_attachment($filename, $from_mail, $from_name, $order) {
        try {
            $helper = $this->__helper;
            
            /* ------Sending winner email------ */
            $salesName = $helper->getStoreConfig('trans_email/ident_sales/name');
            $mailTosalesEmail = $helper->getStoreConfig('trans_email/ident_sales/email');

            $this->transportBuilder->addTo(
                    $mailTosalesEmail, $salesName
            );

            $emailTemplateVariables = [];
            $emailTemplateVariables['name'] = $salesName;
            $emailTemplateVariables['order_id'] = $order;
            $this->transportBuilder->setTemplateVars($emailTemplateVariables);
            
            $this->transportBuilder->setTemplateIdentifier('bluedart_shipment_email_template');
            $this->transportBuilder->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                'store' => $order->getStoreId()
            ]);
            
            //$this->transportBuilder->getMail()->clearFrom();
             
            $this->transportBuilder->setFrom(['email' => $from_mail, 'name' => $from_name]); 
            //Create an array of variables to assign to template

            $fileContents = file_get_contents($helper->getDirPath('media') . "bluredart_pdf/" . $filename);

            /*$this->transportBuilder->getMail()->createAttachment(
                    $fileContents, \Zend_Mime::TYPE_OCTETSTREAM, \Zend_Mime::DISPOSITION_ATTACHMENT, \Zend_Mime::ENCODING_BASE64, basename($filename)
            );*/
            try {
                $transport = $this->transportBuilder->getTransport();
                $transport->sendMessage();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
            //echo 'doneeee';
        } catch (Exception $e) {
             
            $this->logger->error($e->getMessage());
        }
        return true;
    }

	public function outputWeight($gms)
	{
		$kg=0;
		/* if($lbs>0){
			$kg=$lbs*0.45359237;
		} */
		
	    $kg=$gms/1000;
	  $power = floor(log($kg, 10));    
	  switch($power) {
		case 5  :
		case 4  :
		case 3  : $unit = 'KG'; 
				  $power = 0;
				  break;
		case 2  :
		case 1  :    
		case 0  : $unit = 'KG'; 
				  $power = 0;
				  break;
		case -1 : 
		case -2 : 
		case -3 : $unit = 'grams'; 
				  $power = -3;
				  break;
		case -4 : 
		case -5 : 
		case -6 : $unit = 'grams'; 
				  $power = -3;
				  break;
		default : return 'n/a';
	  }
	  return ($kg / pow(10, $power)) . ' ' . $unit;
	}  

}
