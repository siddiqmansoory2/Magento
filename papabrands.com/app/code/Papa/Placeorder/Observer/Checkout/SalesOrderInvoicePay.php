<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Placeorder\Observer\Checkout;

class SalesOrderInvoicePay implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    
	protected $_objectManager;
	protected $_orderFactory;
	
	public function execute(\Magento\Framework\Event\Observer $observer)
    {
		
		/*$invoice = $observer->getEvent()->getInvoice();
		$order = $invoice->getOrder();
		
		
		
		$payment_method_code = $order->getPayment()->getMethodInstance()->getCode();
		
		$amount_due='';
		
		if ($payment_method_code == 'cashondelivery') {
			
			$order->setTotalPaid($order->getTotalPaid() - $invoice->getGrandTotal());
			$order->setBaseTotalPaid($order->getBaseTotalPaid() - $invoice->getBaseGrandTotal());
		}
		*/
		
		
		
        $shipment = $observer->getEvent()->getInvoice();
        $order = $shipment->getOrder();
		if(!$order->getIsVirtual()){
			
		
		$billingAddress = $order->getBillingAddress();
		$shippingAddress = $order->getShippingAddress();
		
		$invoiceIncrementID='';
		$invoiceCreatedAt=$order->getCreatedAt();
		
		$payment = $order->getPayment();
		$method = $payment->getMethodInstance();
		$methodTitle = $method->getTitle();
		
		foreach ($order->getInvoiceCollection() as $invoice) {
			$invoiceIncrementID = $invoice->getIncrementId();
			$invoiceCreatedAt = $invoice->getCreatedAt();
		}
		$_uitems='';
		foreach ($order->getAllVisibleItems() AS $orderItem) {
			
			if ($orderItem->getParentItem()){
				continue;
			}
			
			$_uitems .="<tr style='display: flex; margin-bottom: 10px;'><td style='font-size: 1.1rem; font-weight: 600; text-align: left; width: 40%;'>".$orderItem->getName()." ".$orderItem->getSku()."</td><td style='font-size: 1.1rem; font-weight: 600; text-align: left; width: 20%;'>".($orderItem->getQtyOrdered()*1)."</td><td style='font-size: 1.1rem; font-weight: 600; text-align: left; width: 20%;'>".$order->getOrderCurrencyCode()." ".($orderItem->getPrice()*1)."</td><td style='font-size: 1.1rem; font-weight: 600; text-align: left; width: 20%;'>".$order->getOrderCurrencyCode()." ".($orderItem->getPrice()*$orderItem->getQtyOrdered()*1)."</td></tr>";
		} 
		$this->_sobjectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$UrlInterface = $this->_sobjectManager->create('\Magento\Framework\UrlInterface');
		
		
		$_body="<!doctype html><html><head><meta http-equiv='Content-type' content='text/html; charset=utf-8' /><meta content='telephone=no' name='format-detection' /></head><body class='body' style='padding:0 !important; margin:0 !important; display:block !important; background:#ffffff; -webkit-text-size-adjust:none; font-family:calibiri;font-size:10px;'><table width='100%' border='0' cellspacing='0' cellpadding='0' valign='top'><tr><td align='left' valign='top'><table width='100%' border='0' cellspacing='0' cellpadding='0' valign='top'><tr><td style='background: #11304c; padding: 30px 20px;' align='center'> <img src='".rtrim($UrlInterface->getUrl('images/papa-logo.png'), "/")."' alt='logo' style='margin: auto; display: block; max-width: 70%;'></td></tr><tr><td style='border: 2px solid #acad94; padding: 20px; width: 100%;'><table width='100%' border='0' cellspacing='0' cellpadding='0' style='border: 2px solid #acad94'><tr><td style='padding: 15px 30px; width: 100%;'><table width='100%' border='0' cellspacing='0' cellpadding='0'><tr><td><p style='font-size: 1.1rem; font-weight: 600; margin-bottom: 10px;'>Greetings ".$billingAddress->getFirstname().",</p></td></tr><tr><td style='width: 100%;'><p style='font-size: 1.1rem; font-weight: 600; margin-bottom: 10px; margin-top: 5px;margin-bottom: 5px;'>Thank you for your order! This email is an authentication that we have received and confirmed your order.<br><br></p></td></tr><tr><td style='width: 100%;'>&nbsp;</td></tr><tr><td><table style='width: 100%;'><tr><td style='width: 50%'><p style='font-size: 1.5rem; font-weight: bold; color: #acad94; margin-top: 10px; margin-bottom: 10px; text-transform: uppercase;'>ORDER NO: #".$order->getId()."</p><p style='font-size: 1.1rem; font-weight: 600; margin-top: 5px; margin-bottom: 10px;'>Date: ".$invoiceCreatedAt."</p></td><td style='width: 50%;'><p style='font-size: 1.6rem; font-weight: bold; color: #acad94; margin-top: 10px; margin-bottom: 10px; text-transform: uppercase;'>PAYMENT METHOD:</p><p style='font-size: 1.1rem; font-weight: 600; margin-top: 5px; margin-bottom: 10px;'>".$methodTitle."</p></td></tr></table></td></tr><tr><td style='width: 100%;'>&nbsp;</td></tr><tr><td><table style='width: 100%;'><tr><td style='width: 50%'><p style='font-size: 1.5rem; font-weight: bold; color: #acad94; margin-bottom: 10px; margin-top: 10px; text-transform: uppercase;'>BILLING ADDRESS</p><p style='font-size: 1.1rem; font-weight: 600; margin-bottom: 5px; margin-top: 5px;'>".$billingAddress->getFirstname()."</p><p style='font-size: 1.1rem; font-weight: 600; margin-bottom: 5px; margin-top: 5px;'>".implode(' ',$billingAddress->getStreet())." ".$billingAddress->getCity()."</p><p style='font-size: 1.1rem; font-weight: 600; margin-bottom: 5px; margin-top: 5px;'>".$billingAddress->getRegion()." ".$billingAddress->getPostcode()."</p></td><td style='width: 50%;'><p style='font-size: 1.6rem; font-weight: bold; color: #acad94; margin-bottom: 10px; margin-top: 10px; text-transform: uppercase;'>SHIPPING ADDRESS</p><p style='font-size: 1.1rem; font-weight: 600; margin-bottom: 5px; margin-top: 5px;'>".$shippingAddress->getFirstname()."</p><p style='font-size: 1.1rem; font-weight: 600; margin-bottom: 5px; margin-top: 5px;'>".implode(' ',$shippingAddress->getStreet())." ".$shippingAddress->getCity()."</p><p style='font-size: 1.1rem; font-weight: 600; margin-bottom: 5px; margin-top: 5px;'>".$shippingAddress->getRegion()." ".$shippingAddress->getPostcode()."</p></td></tr></table></td></tr><tr><td><table style='width: 100%; margin-top: 40px;'><thead style='border-bottom: 5px solid #212e4e; display: table-caption; width: 100%; padding: 10px 0;'><tr style='display: flex;'><th style='color: #acad94; font-size: 1.2rem; font-weight: 600; width: 40%; text-align: left; text-transform: uppercase;'>Description</th><th style='color: #acad94; font-size: 1.2rem; font-weight: 600; width: 20%; text-align:left; text-transform: uppercase;'>QTY</th><th style='color: #acad94; font-size: 1.2rem; font-weight: 600; width: 20%; text-align: left; text-transform: uppercase;'>PRICE</th><th style='color: #acad94; font-size: 1.2rem; font-weight: 600; width: 20%; text-align: left; text-transform: uppercase;'>TOTAL</th></tr></thead>".$_uitems."<tr><td colspan='4'></td></tr><tr><td colspan='4'></td></tr><tr style='display: flex; margin-bottom: 10px;'><td></td><td colspan='2' style='font-size: 1.2rem; font-weight: 600; color: #acad94; text-transform: uppercase;'>TOTAL AMOUNT:</td><td style='font-size: 1.1rem; font-weight: 600; text-align: left; width: 20%;'>".$order->getOrderCurrencyCode()." ".($order->getBaseGrandTotal()*1)."</td></tr><tr style='display: flex; margin-bottom: 10px;'><td></td><td colspan='2' style='font-size: 1.2rem; font-weight: 600; color: #acad94; text-transform: uppercase;'>TAX:</td><td style='font-size: 1.1rem; font-weight: 600; text-align: left; width: 20%;'>".$order->getOrderCurrencyCode()." ".$order->getTaxAmount()."</td></tr><tr style='display: flex; margin-bottom: 10px;'><td></td><td colspan='2' style='font-size: 1.2rem; font-weight: 600; color: #acad94; text-transform: uppercase;'>AMOUNT DUE:</td><td style='font-size: 1.1rem; font-weight: 600; text-align: left; width: 20%;'>".$order->getOrderCurrencyCode()." ".($order->getGrandTotal()*1)."</td></tr></table></td></tr><tr><td style='width: 100%;'>&nbsp;</td></tr><tr><td style='width: 100%;'>&nbsp;</td></tr><tr><td><p style='font-size: 1.1rem; font-weight: 600; text-align: center; line-height: 25px; margin-bottom: 20px;'>If you have any queries, contact us at: support@papabrands.com or call/whatsapp us at: +91 9372221906</p></td></tr><tr><td style='width: 100%;'>&nbsp;</td></tr></table></td></tr><tr><td style='width: 100%;background: #11304c; padding: 10px; margin-top: 10px;' align='center'><table style='width: 100%'><tbody style='padding: 10px 0; display: table-caption;'><tr style='display: flex; margin-bottom: 10px;'><td> <a href='".$UrlInterface->getUrl('sales/order/history')."' style='font-size: 1.3rem; color: #fff; font-weight: 600; text-decoration: none;'>Track Order</a></td><td> <a href='".$UrlInterface->getUrl('terms-conditions')."' style='font-size: 1.3rem; color: #fff; font-weight: 600; text-decoration: none;'>T&C</a></td><td> <a href='".$UrlInterface->getUrl('sales/order/history')."' style='font-size: 1.3rem; color: #fff; font-weight: 600; text-decoration: none;'>Manage Order</a></td></tr></tbody></table></td></tr></table></td></tr></table></td></tr></table></body></html>";
		
		/*die;*/
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://app.yellowmessenger.com/api/engagements/notifications/v2/push?bot=x1627030069843',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => false,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS =>'{
				"userDetails": {
				"email": "'.$order->getCustomerEmail().'"
			},
			"notification": {
				"type": "email",
				"subject": "Invoice '.$order->getId().'",
				"sender": "support@papabrands.com",
				"freeTextContent": "'.$_body.'"
			}
			}',
			CURLOPT_HTTPHEADER => array(
				'x-auth-token: 5deabcd62f4191d541850fae2d6633188e208d5d8b7a1f7a11d898da73d169ae',
				'Content-Type: application/json'
			),
		));
		$response = curl_exec($curl);
		curl_close($curl);		
		/*die;*/
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/SalesOrderShipmentAfter.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		
		/*$logger->info('{
				"userDetails": {
				"email": "'.$order->getCustomerEmail().'"
			},
			"notification": {
				"type": "email",
				"subject": "Invoice '.$order->getId().'",
				"sender": "support@papabrands.com",
				"freeTextContent": "We are happy to update that we have dispatched your Order # '.$order->getId().' We/our delivery partner may need to contact you to coordinate a convenient delivery time."
			}
			}');*/
		$logger->info($response);
	}
		
    }
	
}

