<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Model;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\ProductRepository;
class UpdateinventoryurlManagement implements \Papa\Restapi\Api\UpdateinventoryurlManagementInterface
{

	/**
	 * @var StockRegistryInterface
	 */
	protected $stockRegistry;
	/**
	 * @var productRepository
	 */
	protected $productRepository;

	public function __construct(
		StockRegistryInterface $stockRegistry,
		ProductRepository $productRepository
		)
	{
		$this->stockRegistry = $stockRegistry;
		$this->productRepository = $productRepository;
	}
    /**
     * {@inheritdoc}
     */
    public function putUpdateinventoryurl($locationCode,$inventories)
    {
       /* $paramaters = json_encode(array("locationCode"=>$locationCode,"inventories"=>$inventories));
		return $paramaters;*/
		$_inventories='';
		
		$successList=array();
		$failureList=array();
		
		foreach($inventories as $__inventories){			
			try {
			if($this->getProductBySku($__inventories['channelSkuCode'])){
				
				$successList[]=$__inventories['channelSkuCode'];
				
				$stockItem = $this->stockRegistry->getStockItemBySku($__inventories['channelSkuCode']);
				$stockItem->setIsInStock(1);
				$stockItem->setQty($__inventories['quantity']);
				$this->stockRegistry->updateStockItemBySku($__inventories['channelSkuCode'], $stockItem);
			}else{
				$failureList[]=$__inventories['channelSkuCode'];
			}
			$_inventories=$__inventories;
		} catch (\Exception $e) {
			$failureList[]=$__inventories['channelSkuCode'];
		}
			
		}		
		
		
		$_array=array('hasError'=>false,'failureList'=>$failureList,'successList'=>$successList);
		header('Content-Type: application/json');
		echo $response = \Zend_Json::encode($_array);die;
		
		/*$this->response = ["hasError"=>false,'failureList' => [implode(",",$failureList)],'successList' => [implode(",",$successList)]];
		//$this->response = $_array;
		return $this->response;*/
		
		/*$paramaters = json_encode(array("hasError"=>false,array("failureList"=>''),array("successList"=>$_inventories['channelSkuCode'])));
		return $paramaters;
		
		return 'hello api PUT return the $param ' . $param;*/
    }
	
	
	public function getProductById($id) {

        return $this->productRepository->getById($id);
    }

    public function getProductBySku($sku) {
  
		$product = $this->productRepository->get($sku);
		
        return $product->getEntityId();
    }
}

