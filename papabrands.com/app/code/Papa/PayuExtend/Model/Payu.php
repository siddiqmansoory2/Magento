<?php

namespace Papa\PayuExtend\Model;

class Payu extends \PayUIndia\Payu\Model\Payu 
{
    /**
     * Adding getOrderPlaceRedirectUrl method
    * 
    * to prevent sending order email
    * 
    * @return string
    */
    public function getOrderPlaceRedirectUrl() {
        //return true is enough
        return $this->helper->getUrl($this->getConfigData('redirect_url'));
    }

    public function postProcessing(\Magento\Sales\Model\Order $order,\Magento\Framework\DataObject $payment, $response) 
	{
		try {		
			if($this->verifyPayment($order,$response['txnid']))
			{	
				$payment->setTransactionId($response['txnid'])       
				->setPreparedMessage('SUCCESS')
				->setShouldCloseParentTransaction(true)
				->setIsTransactionClosed(0)
				->setAdditionalInformation('payu_mihpayid', $response['mihpayid'])
				->setAdditionalInformation('payu_order_status', 'approved');
				
				If (isset($response['additionalCharges'])) {
					$payment->setAdditionalInformation('Additional Charges', $response['additionalCharges']);		
					$payment->registerCaptureNotification($response['amount']+$response['additionalCharges'],true);
				}
				else {
					$payment->registerCaptureNotification($response['amount'],true);
				}
				$this->logger->debug($response);					
				$order->setTotalPaid($response['amount']);  
				$order->setState(Order::STATE_PROCESSING,true)->setStatus(Order::STATE_PROCESSING);				
				$order->setCanSendNewEmailFlag(true);
				$order->save();		
				$this->orderSender->send($order);
				
				$session = $objectManager->create('\Magento\Checkout\Model\Session');
				$session->setForceOrderMailSentOnSuccess(true);
				$emailSender = $objectManager->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
				$emailSender->send($order);
				
				//Uncomment this code if mail is configured
				/*$invoice = $payment->getCreatedInvoice();				
				if ($invoice && !$order->getEmailSent()) {
					$this->orderSender->send($order);
					$order->addStatusHistoryComment(
					__('Thank you for your order. Your Invoice #%1.', $invoice->getIncrementId())
					)->setIsCustomerNotified(
					true
					)->save();
				}*/
			}
		}
		catch(Exception $e){
			$this->logger->debug($e->getMessage());
		}
    }
}