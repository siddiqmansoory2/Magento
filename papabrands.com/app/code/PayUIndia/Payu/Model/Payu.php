<?php
namespace PayUIndia\Payu\Model;

use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order;

class Payu extends \Magento\Payment\Model\Method\AbstractMethod {

    const PAYMENT_PAYU_CODE = 'payu';
    const ACC_BIZ = 'payubiz';
    const ACC_MONEY = 'payumoney';

    protected $_code = self::PAYMENT_PAYU_CODE;

    /**
     *
     * @var \Magento\Framework\UrlInterface 
     */
    protected $_urlBuilder;
    protected $_supportedCurrencyCodes = array(
        'AFN', 'ALL', 'DZD', 'ARS', 'AUD', 'AZN', 'BSD', 'BDT', 'BBD',
        'BZD', 'BMD', 'BOB', 'BWP', 'BRL', 'GBP', 'BND', 'BGN', 'CAD',
        'CLP', 'CNY', 'COP', 'CRC', 'HRK', 'CZK', 'DKK', 'DOP', 'XCD',
        'EGP', 'EUR', 'FJD', 'GTQ', 'HKD', 'HNL', 'HUF', 'INR', 'IDR',
        'ILS', 'JMD', 'JPY', 'KZT', 'KES', 'LAK', 'MMK', 'LBP', 'LRD',
        'MOP', 'MYR', 'MVR', 'MRO', 'MUR', 'MXN', 'MAD', 'NPR', 'TWD',
        'NZD', 'NIO', 'NOK', 'PKR', 'PGK', 'PEN', 'PHP', 'PLN', 'QAR',
        'RON', 'RUB', 'WST', 'SAR', 'SCR', 'SGF', 'SBD', 'ZAR', 'KRW',
        'LKR', 'SEK', 'CHF', 'SYP', 'THB', 'TOP', 'TTD', 'TRY', 'UAH',
        'AED', 'USD', 'VUV', 'VND', 'XOF', 'YER'
    );
    
    private $checkoutSession;

    /**
     * 
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
      public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \PayUIndia\Payu\Helper\Payu $helper,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Checkout\Model\Session $checkoutSession      
              
    ) {
        $this->helper = $helper;
        $this->orderSender = $orderSender;
        $this->httpClientFactory = $httpClientFactory;
        $this->checkoutSession = $checkoutSession;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger
        );

    }

    public function canUseForCurrency($currencyCode) {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }

    public function getRedirectUrl() {
        return $this->helper->getUrl($this->getConfigData('redirect_url'));
    }

    public function getReturnUrl() {
        return $this->helper->getUrl($this->getConfigData('return_url'));
    }

    public function getCancelUrl() {
        return $this->helper->getUrl($this->getConfigData('cancel_url'));
    }

    /**
     * Return url according to environment
     * @return string
     */
    public function getCgiUrl() {
        $env = $this->getConfigData('environment');
        if ($env === 'production') {
            return $this->getConfigData('production_url');
        }
        return $this->getConfigData('sandbox_url');
    }

    public function buildCheckoutRequest() {
        $order = $this->checkoutSession->getLastRealOrder();
        $billing_address = $order->getBillingAddress();

        $params = array();
        $params["key"] = $this->getConfigData("merchant_key");
        if ($this->getConfigData('account_type') == self::ACC_MONEY) {
            $params["service_provider"] = $this->getConfigData("service_provider");
        }
		
		$params["txnid"] 		= $order->getIncrementId();
        $params["amount"] 		= round($order->getBaseGrandTotal(), 2);
        $params["productinfo"] 	= $this->checkoutSession->getLastRealOrderId();
        $params["firstname"] 	= $billing_address->getFirstName();
        $params["lastname"] 	= $billing_address->getLastname();
        $params["city"]         = $billing_address->getCity();
        $params["state"]        = $billing_address->getRegion();
        $params["zip"]          = $billing_address->getPostcode();
        $params["country"]      = $billing_address->getCountryId();
        $params["email"] 		= $order->getCustomerEmail();
		$params["udf1"] 		= $this->checkoutSession->getSessionId();
		$params["udf5"] 		= 'Magento_v.2.4.1';
        $params["phone"] 		= $billing_address->getTelephone();
        $params["curl"] 		= $this->getCancelUrl();
        $params["furl"] 		= $this->getReturnUrl();
        $params["surl"] 		= $this->getReturnUrl();

        $params["hash"] 		= $this->generatePayuHash($params['txnid'],
        $params['amount'],$params['productinfo'], $params['firstname'],
        $params['email'],$params["udf1"],$params["udf5"]);

        return $params;
    }

    public function generatePayuHash($txnid, $amount, $productInfo, $name,
            $email,$udf1,$udf5) {
        $SALT = $this->getConfigData('salt');

        $posted = array(
            'key' => $this->getConfigData("merchant_key"),
            'txnid' => $txnid,
            'amount' => $amount,
            'productinfo' => $productInfo,
            'firstname' => $name,
            'email' => $email,
			'udf1' => $udf1,
			'udf5' => $udf5,			
        );

        $hashSequence = 'key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10';

        $hashVarsSeq = explode('|', $hashSequence);
        $hash_string = '';
        foreach ($hashVarsSeq as $hash_var) {
            $hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] : '';
            $hash_string .= '|';
        }
        $hash_string .= $SALT;		
        return strtolower(hash('sha512', $hash_string));
    }

    //validate response
    public function validateResponse($returnParams) {
        if ($returnParams['status'] == 'pending' || $returnParams['status'] == 'failure') {
            return false;
        }
        if ($returnParams['key'] != $this->getConfigData("merchant_key")) {
            return false;
        }
		//validate hash
		if(isset($returnParams['hash'])){			
			$txnid 			= $returnParams['txnid'];
			$amount        	= $returnParams['amount'];
			$productinfo   	= $returnParams['productinfo'];
			$firstname     	= $returnParams['firstname'];;
			$email         	= $returnParams['email'];
			$Udf1 			= $returnParams['udf1'];
			$Udf2 			= $returnParams['udf2'];
		 	$Udf3 			= $returnParams['udf3'];
		 	$Udf4 			= $returnParams['udf4'];
		 	$Udf5 			= $returnParams['udf5'];
		 	$Udf6 			= $returnParams['udf6'];
		 	$Udf7 			= $returnParams['udf7'];
		 	$Udf8 			= $returnParams['udf8'];
		 	$Udf9 			= $returnParams['udf9'];
		 	$Udf10 			= $returnParams['udf10'];
			$additionalCharges 	= 	0; 
			if (isset($returnParams["additionalCharges"])) $additionalCharges = $returnParams['additionalCharges'];
							
			$keyString =  $this->getConfigData("merchant_key").'|'.$txnid.'|'.$amount.'|'.$productinfo.'|'.$firstname.'|'.$email.'|'.$Udf1.'|'.$Udf2.'|'.$Udf3.'|'.$Udf4.'|'.$Udf5.'|'.$Udf6.'|'.$Udf7.'|'.$Udf8.'|'.$Udf9.'|'.$Udf10;
		  
			$keyArray = explode("|",$keyString);
			$reverseKeyArray = array_reverse($keyArray);
			$reverseKeyString=implode("|",$reverseKeyArray);			 
			$status=$returnParams['status'];			
			$saltString     = $this->getConfigData('salt').'|'.$status.'|'.$reverseKeyString;
			if($additionalCharges > 0) 
				$saltString     = $additionalCharges.'|'.$saltString;
			
			$sentHashString = strtolower(hash('sha512', $saltString));
			if($sentHashString != $returnParams['hash'])
				return false;
			else
				return true;
		}
        return false;
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
				/*
				$session = $objectManager->create('\Magento\Checkout\Model\Session');
				$session->setForceOrderMailSentOnSuccess(true);
				$emailSender = $objectManager->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
				$emailSender->send($order);
				*/
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

	public function verifyPayment(\Magento\Sales\Model\Order $order,$txnid)
	{
		$flag = $this->getConfigData('verifypayment');
		
		if(!$flag) return true;
		
		$fields = array(
				'key' => $this->getConfigData("merchant_key"),
				'command' => 'verify_payment',
				'var1' => $txnid,
				'hash' => ''
			);
				
		$hash = hash("sha512", $fields['key'].'|'.$fields['command'].'|'.$fields['var1'].'|'.$this->getConfigData('salt') );
		$fields['hash'] = $hash;
		$fields_string = http_build_query($fields);
		$url = 'https://info.payu.in/merchant/postservice.php?form=2';
		if( $this->getConfigData('environment') == 'sandbox' )
			$url = "https://test.payu.in/merchant/postservice.php?form=2";	
        	
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSLVERSION, 6);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
		$response = curl_exec($curl);
		$curlerr = curl_error($curl);
			
		$message = '';
		$res ='';
		if($curlerr !=''){
			$message = $curlerr;
			return false;
		}
		else 
		{
			$res = json_decode($response,true);				
			
			if(!isset($res['status']))
				$message = $res['msg'];
			else{
				$res = $res['transaction_details'];
				$res = $res[$txnid];					
			}
			if($res['status'] == 'success')
			{	
				return true;
			}
			else return false;
		}			
	}	
}
