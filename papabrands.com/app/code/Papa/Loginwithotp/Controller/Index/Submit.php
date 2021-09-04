<?php
 
namespace Papa\Loginwithotp\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
 
class Submit extends \Magento\Framework\App\Action\Action
{
 
	public function __construct(
    	Context $context
	) {
		
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->_scopeConfig = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
		$this->_encryptor = $this->_objectManager->get('Magento\Framework\Encryption\EncryptorInterface');
		$this->_customer = $this->_objectManager->get('\Magento\Customer\Model\Customer');
	
		$this->customerRepo = $this->_objectManager->get('\Magento\Customer\Api\CustomerRepositoryInterface');
		$this->customerFactory = $this->_objectManager->get('\Magento\Customer\Model\CustomerFactory');
		$this->customerSession = $this->_objectManager->get('\Magento\Customer\Model\Session');
		$this->storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		
		
    	parent::__construct($context);
	}
 
	public function execute()
	{
		
		$data = $this->getRequest()->getParams();
		$response = [];
		$param = [];
		
		$request =""; 
		
		if(!$data['mobile_no'] || !$data['mobile_otp']):
			$response = ['error' => true, 'message' => __('Invalid OTP'),'param'=>''];
		else:
		
			try {				
				
				$customerObj = $this->customerFactory->create()->getCollection()
				->addAttributeToSelect("*")
				->addAttributeToFilter("phone_number", array("eq" => $data['mobile_no']));
				
				$success=false;					
				
				foreach($customerObj as $_customer):
				
					if($_customer->getLoginOtp()==$data['mobile_otp']){
						
						$success=true;						
						$response = ['error' => false, 'message' => __("OTP Success"),'param'=>$param];	
						
						
						
						/*$customerRepo = $this->customerRepo->get($_customer->getEmail());
						$customer = $this->customerFactory->create()->load($customerRepo->getId()); 
						$this->customerSession->setCustomerAsLoggedIn($customer);*/
						
						$websiteId =$this->storeManager->getStore()->getWebsiteId();
						$model = $this->_customer->setWebsiteId( $websiteId)->loadByEmail($_customer->getEmail());
						
						$__customer = $this->_customer->loadByEmail($_customer->getEmail()); 
						$this->customerSession->setCustomerAsLoggedIn($__customer);
						$_customer->setLoginOtp("");
						$_customer->save();
					}
				
				endforeach;
				
				 
				if($success==false){
					$response = ['error' => true, 'message' => __('Invalid OTP'),'param'=>''];
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