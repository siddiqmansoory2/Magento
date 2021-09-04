<?php
 
namespace Papa\Loginwithotp\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Papa\CodTwoFactor\Helper\Apicall as Ebthelper;

class Sendotp extends \Magento\Framework\App\Action\Action
{
	/**
	 * @var Ebthelper
	 */
	private $ebtHelper;
	
	public function __construct(
    	Context $context,
		Ebthelper $ebtHelper
	) {
		
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->_scopeConfig = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
		$this->_encryptor = $this->_objectManager->get('Magento\Framework\Encryption\EncryptorInterface');
		$this->_customer = $this->_objectManager->get('\Magento\Customer\Model\Customer');
	
		$this->customerRepo = $this->_objectManager->get('\Magento\Customer\Api\CustomerRepositoryInterface');
		$this->customerFactory = $this->_objectManager->get('\Magento\Customer\Model\CustomerFactory');
		$this->customerSession = $this->_objectManager->get('\Magento\Customer\Model\Session');
		
		$this->ebtHelper = $ebtHelper;
		
    	parent::__construct($context);
	}
 
	public function execute()
	{
		
		$data = $this->getRequest()->getParams();
		$response = [];
		$param = [];
		
		$request =""; 
		
		if(!$data['mobile_no']):
			$response = ['error' => true, 'message' => __('Invalid Mobile Number'),'param'=>''];
		else:
		
			try {
				
				
				$customerObj = $this->customerFactory->create()->getCollection()
				->addAttributeToSelect("*")
				->addAttributeToFilter("phone_number", array("eq" => $data['mobile_no']));
				
				$success=false;				
				
				$_otp=mt_rand(100000,999999);
				
				foreach($customerObj as $_customer):
					if($_customer->getEmail()){
						$success=true;
						if($_customer->getLoginOtp()){
							$_otp=$_customer->getLoginOtp();
						}
						$customer = $this->customerRepo->getById($_customer->getId());
						$customer->setCustomAttribute('login_otp', $_otp);
						$this->customerRepo->save($customer);
						
						/*$this->customerSession()->setSentOtpMobileNumber($data['mobile_no']);
						$this->customerSession()->setSentOtpMobileNumberOtp('123456');*/
					}
				
					/*$customerRepo = $this->customerRepo->get($_customer->getEmail());
					$customer = $this->customerFactory->create()->load($customerRepo->getId()); 
					$this->customerSession->setCustomerAsLoggedIn($customer);*/
				endforeach;
				
				 
				if($success==false){
					$response = ['error' => true, 'message' => __("Invalid Mobile No."),'param'=>$param];
				}else{
					
					$param['method']= $this->_scopeConfig->getValue('login_with_otp/general_configuration/method', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
				
					$param['send_to'] = $data['mobile_no'];
					
					$param['msg'] = 'Your OTP is '.$_otp;
					
					$param['userid'] = $this->_scopeConfig->getValue('login_with_otp/general_configuration/userid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
					
					$param['password'] = $this->_encryptor->decrypt($this->_scopeConfig->getValue('login_with_otp/general_configuration/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
					
					$param['v'] = $this->_scopeConfig->getValue('login_with_otp/general_configuration/version', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
					
					$param['msg_type'] = 'TEXT';
					
					$param['auth_scheme'] = "PLAIN";
					
					foreach($param as $key=>$val) {
						$request.= $key."=".urlencode($val);
						$request.= "&";
					}
					$request = substr($request, 0, strlen($request)-1); 
					$responce = $this->ebtHelper->callApiUrl($param['send_to'],$_otp);
					
					if($responce['errors'] === "false")
					{
						$response = [
							'errors' => false,
							'message' => __("OTP Send Successfully."),
							'param'=>$param
						];
						
					}else{
						$response = [
							'errors' => true,
							'message' => $responce['message'],
							'param'=>$param
						];
					}
					
					/*$url = "https://enterprise.smsgupshup.com/GatewayAPI/rest?".$request;
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
					$curl_scraped_page = curl_exec($ch); 
					curl_close($ch);
					$respose_curl=explode("|",$curl_scraped_page);
					
					if(trim($respose_curl[0])=="error"){

						$response = ['error' => true, 'message' => __($respose_curl[2]),'param'=>$param];
					}else{
						$response = ['error' => false, 'message' => __("OTP Sent"),'param'=>$param];
					}*/
					
				}
				
				
			}
			catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
				$response = ['error' => true, 'message' => __($e->getMessage()),'param'=>$param];
			}
		endif;
    	$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON); 
    	$resultJson->setData($response); 
 
    	return $resultJson; 
	}
}
?>