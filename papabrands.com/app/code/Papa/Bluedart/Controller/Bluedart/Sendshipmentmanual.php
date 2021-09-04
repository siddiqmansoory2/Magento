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
class Sendshipmentmanual extends \Magento\Backend\App\Action {

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
		
		$AWB_No=89251564835;
		
		
		 $helper = $this->__helper;
		$_url_details='https://api.bluedart.com/servlet/RoutingServlet?handler=tnt&action=custawbquery&loginid=DDP81791&awb=awb&numbers='.$AWB_No.'&format=xml&lickey=%20kjkirtetmlnfseekprgg4qjplxluqlle&verno=1.3&scan=1';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $_url_details);
		$data = curl_exec($ch);
		curl_close($ch);
		$xml = (array)simplexml_load_string($data);		
		$Shipment=(array)$xml['Shipment'];		
		$Shipment['Status'];
		
		//echo '<pre>';print_r($Shipment);die;
		
		//$AWB_No = $Shipment['AWBNo'];
		//$des_area = $Shipment['DestinationAreaCode'];
		//$des_loc = $Shipment['Destination'];
		$des_area = '';
		$des_loc = '';
		
		try {
			
			$param = array();
			
			
			
			/*$param['order_id']='68098';
			$param['shipmentCode']='68098';*/
			
			$param = $this->getRequest()->getParams();
			$param['order_id']='94638';
			
			if(!(is_array($param) && array_key_exists('order_id',$param))){
				$_array = array( 
					"hasError"=>true,
					"message"=>'order_id is missing.'
				);
				
				header('Content-Type: application/json');
				echo $response = \Zend_Json::encode($_array);die;
			}
			
			$_uitems='';
			
			$order = $this->orderModel->load($param['order_id']);
			
			$order_id = $order->getIncrementId();
			
			
			$shipping_address = $order->getShippingAddress();
			
			
			$cust_street = $shipping_address->getStreet();
			$cust_street = $cust_street[0];

			$cust_resion = $shipping_address->getRegion();
			$cust_pin = $shipping_address->getPostcode();
			$cust_phone = $shipping_address->getTelephone();
			$oneLine_address='';
			
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

              
			
			$address='';
			foreach ($line_store_address as $add) {
				$address .= '<p>' . $add . '</p>';
				$oneLine_address .= $add;
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
				
			 $j = 1;
			
			 
			 
			 
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

			 
				$invIncrementIDs = '';
				$invDate = '';
				if ($order->hasInvoices()) {
					$invIncrementIDs = array();
					foreach ($order->getInvoiceCollection() as $inv) {
						$invIncrementIDs = $inv->getIncrementId();
						$invDate = date('d-M-Y', strtotime($inv->getCreatedAt()));
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

			
			foreach ($order->getAllVisibleItems() AS $orderItem) {
				
				
				if (!$orderItem->getParentItemId()) {
					$sku = $orderItem->getSku();
					$p_name = $orderItem->getName();
					$p_qty = number_format(($orderItem->getQtyOrdered()*1), 2);
					$p_baseprice = number_format(($orderItem->getBasePrice()*1), 2);
					$p_price = number_format(($orderItem->getPrice()*1), 2);
					$final_price = number_format((($orderItem->getQtyOrdered()*1) * (($orderItem->getPrice()*1))), 2);
					$productsinawb[] = $p_name;
					
					$shipping_label="";
					
					
					$getTaxPercent=$orderItem->getTaxPercent()*1;
					
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
						<td style="font-size: 1.3rem; text-align: left; width: 20%; border: 1px solid #000; padding: 5px 10px;"><b>'.$this->outputWeight($orderItem->getWeight()).'</b></td>
						<td style="font-size: 1.3rem; text-align: left; width: 20%; border: 1px solid #000; padding: 5px 10px;"><b>'.$shipping_label.'</b></td>
						<td style="font-size: 1.3rem; text-align: left; width: 20%; border: 1px solid #000; padding: 5px 10px;"><b>' . $p_price . '</b></td>
						<td style="font-size: 1.3rem; text-align: left; width: 20%; border: 1px solid #000; padding: 5px 10px;"><b>' . $final_price . '</b></td>
					</tr>';
					
					$j++;
				}
				
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
			
			$helper = $this->__helper;
			$address='';
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
			
			$logoUrl = $helper->getMediaUrl() . 'bluedart/' . $helper->getStoreConfig('Softprodigy_Bluedart/general/pdf_logo');
			
			
			$this->_sobjectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$UrlInterface = $this->_sobjectManager->create('\Magento\Framework\UrlInterface');
			

		$storeName = $helper->getStoreConfig('Softprodigy_Bluedart/general/store_name');
		$email = $helper->getStoreConfig('Softprodigy_Bluedart/general/email_id_from');
		$storePhone = $helper->getStoreConfig('Softprodigy_Bluedart/general/contact_number');
		$storePincode = $helper->getStoreConfig('Softprodigy_Bluedart/general/pin_code');
		$customercode = $helper->getStoreConfig('Softprodigy_Bluedart/general/customer_code');
		$vandercode = $helper->getStoreConfig('Softprodigy_Bluedart/general/vander_code');
		$originarea = $helper->getStoreConfig('Softprodigy_Bluedart/general/origin_area');
		$tin = $helper->getStoreConfig('Softprodigy_Bluedart/general/tin_no');
		$pan = 'AACFW0143L';
		
		$oneLine_address='';
		
		$blueAddress = ($helper->getStoreConfig('Softprodigy_Bluedart/general/store_contact_addr'));
		$line_store_address = explode("\n", $blueAddress);
		
		foreach ($line_store_address as $add) {
			$address .= '<p>' . $add . '</p>';
			$oneLine_address .= $add;
		}
		
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


			
		//echo $html;
		//die;	
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
			
			/*echo $file_name = 'order_invoice_' .$param['order_id']. '_'.$param['shipmentCode'].'.pdf';*/
			echo $file_name = 'order_' . $order_id.'.pdf';
			$filename = $helper->getDirPath('media') . "bluredart_pdf/" . $file_name;
			$mpdf->Output($filename, 'F');die;
			
        } catch (\Exception $e) {
			
			$_array = array( 
				"hasError"=>true,
				"message"=>$e->getMessage()
			);
			
			header('Content-Type: application/json');
			echo $response = \Zend_Json::encode($_array);die;
			
        }
        
    }
	
	public function convert_number($number) 
    {
        if (($number < 0) || ($number > 999999999)) 
        {
            throw new Exception("Number is out of range");
        }
        $giga = floor($number / 1000000);
        // Millions (giga)
        $number -= $giga * 1000000;
        $kilo = floor($number / 1000);
        // Thousands (kilo)
        $number -= $kilo * 1000;
        $hecto = floor($number / 100);
        // Hundreds (hecto)
        $number -= $hecto * 100;
        $deca = floor($number / 10);
        // Tens (deca)
        $n = $number % 10;
        // Ones
        $result = "";
        if ($giga) 
        {
            $result .= $this->convert_number($giga) .  "Million";
        }
        if ($kilo) 
        {
            $result .= (empty($result) ? "" : " ") .$this->convert_number($kilo) . " Thousand";
        }
        if ($hecto) 
        {
            $result .= (empty($result) ? "" : " ") .$this->convert_number($hecto) . " Hundred";
        }
        $ones = array("", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eightteen", "Nineteen");
        $tens = array("", "", "Twenty", "Thirty", "Fourty", "Fifty", "Sixty", "Seventy", "Eigthy", "Ninety");
        if ($deca || $n) {
            if (!empty($result)) 
            {
                $result .= " and ";
            }
            if ($deca < 2) 
            {
                $result .= $ones[$deca * 10 + $n];
            } else {
                $result .= $tens[$deca];
                if ($n) 
                {
                    $result .= "-" . $ones[$n];
                }
            }
        }
        if (empty($result)) 
        {
            $result = "zero";
        }
        return $result;
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