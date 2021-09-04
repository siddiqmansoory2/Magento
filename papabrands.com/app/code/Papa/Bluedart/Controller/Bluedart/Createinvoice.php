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
class Createinvoice extends \Magento\Backend\App\Action {

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
		
		try {
			
			$param = array();
			
			
			
			/*$param['order_id']='68098';
			$param['shipmentCode']='68098';*/
			
			$param = $this->getRequest()->getParams();
			
			/*$param['order_id']='94539';*/
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
			
			/*echo "<pre>";print_r($order->getData());die;*/
			
			$billingAddress = $order->getBillingAddress();
			$shippingAddress = $order->getShippingAddress();
			
			$invoiceIncrementID='';
			$invoiceCreatedAt=$order->getCreatedAt();
			
			$payment = $order->getPayment();
			$method = $payment->getMethodInstance();
			$methodTitle = $method->getTitle();
			
			$payment_method_code = $order->getPayment()->getMethodInstance()->getCode();
			
			$amount_due='';
			
			if ($payment_method_code == 'cashondelivery') {
				$amount_due .='<tr style="display: flex; margin-bottom: 10px;">
                    <td></td>
                    <td colspan="2" style="font-size: 1.2rem; font-weight: 600;  color: #ACAD94; text-transform: uppercase;">AMOUNT DUE:</td>
                    <td style="font-size: 1.1rem; font-weight: 600; text-align: left; width: 20%;">'.$order->getOrderCurrencyCode().''.number_format(($order->getGrandTotal()*1),2).'</td>
                </tr>';
			}
			
			foreach ($order->getInvoiceCollection() as $invoice) {
				$invoiceIncrementID = $invoice->getIncrementId();
				$invoiceCreatedAt = $invoice->getCreatedAt();
			}
			
			$sr_no=1;foreach ($order->getAllVisibleItems() AS $orderItem) {
				
				if ($orderItem->getParentItem()){
					continue;
				}
				
				$shipping_label="";
				$shipping_val="";
			
				$getTaxPercent=$orderItem->getTaxPercent()*1;
				
				if($getTaxPercent>0){
					if($shippingAddress->getRegion()=="Karnataka"){
						$shipping_label="CGST Tax + SGST Tax";
						$shipping_val=($getTaxPercent/2)."% + ".($getTaxPercent/2)."%";
					}else{
						$shipping_label="IGST TAX";
						$shipping_val=$getTaxPercent."%";
					}
				}
				
				
				/*$_uitems .='<tr style="display: flex; margin-bottom: 10px;">
					<td style="font-size: 1.1rem; font-weight: 600; text-align: left; width: 40%;">'.$orderItem->getName().' '.$orderItem->getSku().'</td>
					<td style="font-size: 1.1rem; font-weight: 600; text-align: left; width: 20%;">'.($orderItem->getQtyOrdered()*1).'</td>
					<td style="font-size: 1.1rem; font-weight: 600; text-align: left; width: 20%;">'.$order->getOrderCurrencyCode().' '.($orderItem->getPrice()*1).'</td>
					<td style="font-size: 1.1rem; font-weight: 600; text-align: left; width: 20%;">'.$shipping_label.'</td>
					<td style="font-size: 1.1rem; font-weight: 600; text-align: left; width: 20%;">'.$order->getOrderCurrencyCode().' '.($orderItem->getPrice()*$orderItem->getQtyOrdered()*1).'</td>
				</tr>';*/
				
				
				$_uitems .="<tr>
					<td style=' font-size: 60px; text-align: left; width: 5%; border: 1px solid #000; border-bottom: none; padding: 5px;'>
					".$sr_no."</td>
					<td style=' font-size: 60px; text-align: left; width: 40%; border: 1px solid #000; border-bottom: none; padding: 5px;'>
					".$orderItem->getName()." ".$orderItem->getSku()."<br>
					SKU:".$orderItem->getSku()."</td>
					<td style=' font-size: 60px; text-align: left; width: 10%; border: 1px solid #000; border-bottom: none; padding: 5px;'>
					".$order->getOrderCurrencyCode()." ".($orderItem->getPrice()*1)."</td>
					<td style=' font-size: 60px; text-align: left; width: 10%; border: 1px solid #000; border-bottom: none; padding: 5px;'>
					".$order->getOrderCurrencyCode()." ".($orderItem->getDiscountAmount()*1)."</td>
					<td style=' font-size: 60px; text-align: left; width: 5%; border: 1px solid #000; border-bottom: none; padding: 5px;'>
					".($orderItem->getQtyOrdered()*1)."</td>
					<td style=' font-size: 60px; text-align: left; width: 10%; border: 1px solid #000; border-bottom: none; padding: 5px;'>
					".$order->getOrderCurrencyCode()." ".($orderItem->getPrice()*$orderItem->getQtyOrdered()*1)." </td>
					<td style=' font-size: 60px; text-align: left; width: 5%; border: 1px solid #000; border-bottom: none; padding: 5px;'>
					".$shipping_val."</td>
					<td style=' font-size: 60px; text-align: left; width: 5%; border: 1px solid #000; border-bottom: none; padding: 5px;'>
					".$shipping_label."</td>
					<td style=' font-size: 60px; text-align: left; width: 10%; border: 1px solid #000; border-bottom: none; padding: 5px;'>
					".$order->getOrderCurrencyCode()." ".($orderItem->getTaxAmount()*1)."</td>
					<td style=' font-size: 60px; text-align: left; width: 10%; border: 1px solid #000; border-bottom: none; padding: 5px;'>
					".$order->getOrderCurrencyCode()." ".($orderItem->getRowTotalInclTax()*1)."</td>
					</tr>";
				$sr_no++;
			} 
			
			
			
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
		
		$html="<body style='font-family: serif; font-size: 50px;'>
		<table width='100%' border='0' cellspacing='0' cellpadding='0' style='font-size: 50px;'>

        <tr>

            <td align='center' valign='top'>

                <table width='700' border='0' cellspacing='0' cellpadding='0'>

                    <tr>
                        <td>
                            <table width='100%'>
                                <tr>
                                    <td width='50%' style='padding: 10px 0; font-size: 100px; ' align='left'>
                                        <img src='images/papa-logo-black.png' alt='logo' width='100%'>
                                    </td>
                                    <td width='50%' style='padding: 10px 0;' align='right'>
                                        <p
                                            style='font-weight: 600; font-size: 80px; text-align: right; margin-top: 0; margin-bottom: 10px;'>
                                            <b>Tax/Invoice</b> <br> <span
                                                style='font-weight: 400; font-size: 80px; text-align: right; margin-top: 0; margin-bottom: 10px;'>(Orignal Invoice)</span>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr> 

                    <tr>
                        <td>
                            <table width='100%;'>
                                <tr style='vertical-align: baseline;'>
                                    <td style='width: 50%; font-size: 80px; ' align='left' >
                                        <p style='font-weight: 600; text-align: left; margin-bottom: 0;'><b>Sold By</b> :</p>										
										" . $storeName . ", " . $oneLine_address . "," . $storePincode . "
										
                                    </td>
                                    <td style='width: 50%; font-size: 80px; ' align='right'>
                                        <p style='font-weight: 600; text-align: right; margin-bottom: 0;'><b>Billing
                                            Address :</b></p>
										<p style='text-align: left; margin: 0;text-align: right;'>".$billingAddress->getFirstname()."</p>
										<p style='text-align: left; margin: 0;text-align: right;'>".implode(" ",$billingAddress->getStreet())." ".$billingAddress->getCity()."</p>
										<p style='text-align: left; margin: 0;text-align: right;'>".$billingAddress->getRegion()." ".$billingAddress->getPostcode()."<br>".$billingAddress->getTelephone()."</p>
										<p style='text-align: left; margin: 0;text-align: right;'>".$billingAddress->getCountry()."</p>
										<br>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width='100%;'>
                                <tr>
                                    <td style='width: 50%; font-size: 80px; ' align='left' valign='top'>
                                        <p style='text-align: left; margin: 0;'><span style='font-weight: 600; '><b>PAN
                                                No :</b></span> ".$pan."</p>
                                        <p style='text-align: left; margin: 0;'><span style='font-weight: 600; '><b>GST
                                                Registration No :</b> </span> ".$tin."</p>

                                    </td>
                                    <td style='width: 50%; font-size: 80px; ' align='right'>
                                        <p style='font-weight: 600; text-align: right; margin: 0;'><b>Shipping Address :</b></p>
										<p style='text-align: left; margin: 0;text-align: right;'>".$shippingAddress->getFirstname()."</p>
										<p style='text-align: left; margin: 0;text-align: right;'>".implode(" ",$shippingAddress->getStreet())." ".$shippingAddress->getCity()."</p>
										<p style='text-align: left; margin: 0;text-align: right;'>".$shippingAddress->getRegion()." ".$shippingAddress->getPostcode()."<br>".$shippingAddress->getTelephone()."</p>
										<p style='text-align: left; margin: 0;text-align: right;'>".$shippingAddress->getCountry()."</p>
										<br>
										<p style='text-align: right; margin: 0;'><span style='font-weight: 600; '><b>Place
                                                of supply :</b></span> ".$shippingAddress->getRegion()." </p>
                                        <p style='text-align: right; margin: 0;'><span style='font-weight: 600; '><b>Place
                                                of delivery :</b></span> ".$shippingAddress->getRegion()."</p>
												
										
										
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width='100%;'>
                                <tr>
                                    <td style='width: 50%; font-size: 80px; ' align='left'>
                                        <p style='text-align: left; margin: 0;'><span style='font-weight: 600; '><b>Order
                                                Number :</b> </span> ".$param['order_id']."</p>
                                        <p style='text-align: left; margin: 0;'><span style='font-weight: 600; '><b>Order
                                                Date :</b> </span> ".$order->getCreatedAt()."</p>

                                    </td>
                                    <td style='width: 50%; font-size: 80px; ' align='right'>
                                        <p style='text-align: right; margin: 0;'><span
                                                style='font-weight: 600; '><b>Invoice Number :</b> </span> ".$param['order_id']."</p>
                                        <p style='text-align: right; margin: 0;'><span
                                                style='font-weight: 600; '><b>Invoice Date :</b></span> ".$invoiceCreatedAt."</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <table style='width: 100%; margin-top: 20px;' cellpadding='5' cellspacing='0' border='0'>
                                <tbody style='display: table-caption;'>
									
								
								
                                    <tr style='background: #eaeaea;'>
                                        <th
                                            style='color: #000;  font-size: 60px; font-weight: 600; width: 5%; text-align: left; border: 1px solid #000; padding: 5px;'><b>
                                            SL No.</b></th>
                                        <th
                                            style='color: #000;  font-size: 60px; font-weight: 600; width: 40%; text-align: left; border: 1px solid #000; padding: 5px;'><b>
                                            Description</b></th>
                                        <th
                                            style='color: #000;  font-size: 60px; font-weight: 600; width: 10%; text-align: left; border: 1px solid #000; padding: 5px;'><b>
                                            Unit Price</b></th>
                                        <th
                                            style='color: #000;  font-size: 60px; font-weight: 600; width: 10%; text-align:left; border: 1px solid #000; padding: 5px;'><b>
                                            Discount</b></th>
                                        <th
                                            style='color: #000;  font-size: 60px; font-weight: 600; width: 5%; text-align:left; border: 1px solid #000; padding: 5px;'><b>
                                            Qty</b></th>
                                        <th
                                            style='color: #000;  font-size: 60px; font-weight: 600; width: 10%; text-align: left; border: 1px solid #000; padding: 5px;'><b>
                                            Net Amount</b></th>
                                        <th
                                            style='color: #000;  font-size: 60px; font-weight: 600; width: 5%; text-align: left; border: 1px solid #000; padding: 5px;'><b>
                                            Tax Rate</b></th>
                                        <th
                                            style='color: #000;  font-size: 60px; font-weight: 600; width: 5%; text-align: left; border: 1px solid #000; padding: 5px;'><b>
                                            Tax Type</b></th>
                                        <th
                                            style='color: #000;  font-size: 60px; font-weight: 600; width: 10%; text-align: left; border: 1px solid #000; padding: 5px;'><b>
                                            Tax Amount</b></th>
                                        <th
                                            style='color: #000;  font-size: 60px; font-weight: 600; width: 10%; text-align: left; border: 1px solid #000; padding: 5px;'><b>
                                            Total Amount</b></th>
                                    </tr>
                                    ".$_uitems."
                                    
                                    <tr>
                                        <td colspan='8'
                                            style='border: 1px solid #000; text-transform: uppercase; font-weight: 600;' font-size: 80px;>
                                            Total</td>
                                        <td style='border: 1px solid #000; background: #eaeaea;' font-size: 80px;>".$order->getOrderCurrencyCode()." ".$order->getTaxAmount()."</td>
                                        <td style='border: 1px solid #000; background: #eaeaea;' font-size: 80px;>".$order->getOrderCurrencyCode()." ".($order->getBaseGrandTotal()*1)."</td>
                                    </tr>
                                    <tr>
                                        <td colspan='10' style='border: 1px solid #000; font-weight: 600; font-size: 80px; ' align='left'><b>Amount in
                                            Words: <br>
                                            ".$this->convert_number($order->getBaseGrandTotal()*1)." rupees only.</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan='10' style='text-align: right; border: 1px solid #000; font-weight: 600; font-size: 60px; '><b>For ".$storeName."</b>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
					
					<tr>
                        <td>
                            <table width='100%;'>
                                <tr>
                                    <td colspan='10' style='text-align: center; border: 0px solid #000; font-weight: 600; font-size: 60px; '><b>
                                            This is a computer generated invoice and no signature required.</b>
                                        </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
					
					
					
                </table>

            </td>

        </tr>

    </table></body>
";


			
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
			
			echo $file_name = 'order_invoice_' .$param['order_id']. '_'.$param['shipmentCode'].'.pdf';
			
			/*$file_name = 'invoice_.pdf';*/
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
	
	
}




	