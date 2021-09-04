<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Increff\Controller\Updateqty;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     */
    public function __construct(PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->jsonResultFactory = $this->_objectManager->get('\Magento\Framework\Controller\Result\JsonFactory');
		
		$this->asim_link = "https://assure-proxy.increff.com/assuremagic2"; 
        $this->asim_authUsername = "PAPABRANDS_AMV2-1100014382"; 
        $this->authPassword = "6eeb2359-be7c-41c8-85c5-3b27ec053e6e"; 
		
		
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
	{
		
		
		
		/*$url = "https://staging-oltp.increff.com/assure-magic2/master/sku-listings";
		$url = "https://assure-proxy-asim.increff.com/assuremagic2/master/sku-listings";*/
		$url = $this->asim_link."/master/sku-listings";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$headers = array(
			'Content-Type: application/json',
			'authUsername: '.$this->asim_authUsername,
			'authPassword: '.$this->authPassword,
			'Accept: application/json'
		 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);	
		echo $curl_scraped_page = curl_exec($ch); die;
		curl_close($ch);
					
					
		/*$data = $this->getRequest()->getParams();*/
		$response = [];
		$param = array();
		
		$request =""; 
		
		$param[]=array(
			"sku"=>"0018",
			"qty"=>100,
			"store_view_code"=>"default",
			"attribute_set_code"=>"Default",
			"product_type"=>"simple",
			"product_websites"=>"base",
		);
		$param[]=array(
			"sku"=>"0017",
			"qty"=>100,
			"store_view_code"=>"default",
			"attribute_set_code"=>"Default",
			"product_type"=>"simple",
			"product_websites"=>"base",
		);
		
		
		try {
			$response = ['products' => $param];
		}
		catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
			$response = ['error' => true, 'message' => __($e->getMessage()),'param'=>""];
		}
		
		$resultJson = $this->jsonResultFactory->create();
    	$resultJson->setData($response); 
 
    	return $resultJson; 
	}
}

