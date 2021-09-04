<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */


namespace Amasty\Rma\Controller;

use Amasty\Rma\Utils\FileUpload;

class FrontendRma
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Amasty\Rma\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Amasty\Rma\Model\Cookie\HashChecker
     */
    private $hashChecker;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Rma\Api\ChatRepositoryInterface
     */
    private $chatRepository;

    /**
     * @var \Amasty\Rma\Utils\FileUpload
     */
    private $fileUpload;

    /**
     * @var \Amasty\Rma\Api\Data\RequestCustomFieldInterfaceFactory
     */
    private $customFieldFactory;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Amasty\Rma\Model\ConfigProvider $configProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Rma\Api\ChatRepositoryInterface $chatRepository,
        \Amasty\Rma\Model\Cookie\HashChecker $hashChecker,
        \Amasty\Rma\Utils\FileUpload $fileUpload,
        \Amasty\Rma\Api\Data\RequestCustomFieldInterfaceFactory $customFieldFactory
    ) {
        $this->customerSession = $customerSession;
        $this->configProvider = $configProvider;
        $this->hashChecker = $hashChecker;
        $this->storeManager = $storeManager;
        $this->chatRepository = $chatRepository;
        $this->fileUpload = $fileUpload;
        $this->customFieldFactory = $customFieldFactory;
		
		$this->asim_link = "https://assure-proxy.increff.com/assuremagic2"; 
        $this->asim_authUsername = "PAPABRANDS_AMV2-1100014382"; 
        $this->authPassword = "6eeb2359-be7c-41c8-85c5-3b27ec053e6e"; 
		
    }

    /**
     * @return string
     */
    public function getReturnRequestHomeUrl()
    {
        if ($customerId = $this->customerSession->getCustomerId()) {
            return $this->configProvider->getUrlPrefix() . '/account/history';
        } elseif ($this->configProvider->isGuestRmaAllowed()) {
            if ($hash = $this->hashChecker->getHash()) {
                return $this->configProvider->getUrlPrefix() . '/guest/history';
            } else {
                return $this->configProvider->getUrlPrefix() . '/guest/login';
            }
        }

        return 'customer/account/login';
    }

    /**
     * @param \Amasty\Rma\Api\CustomerRequestRepositoryInterface $requestRepository
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     *
     * @return \Amasty\Rma\Api\Data\RequestInterface
     */
    public function processNewRequest($requestRepository, $order, $httpRequest)
    {
        $customFields = [];
        $request = $requestRepository->getEmptyRequestModel();
        foreach ($httpRequest->getParam('custom_fields', []) as $code => $label) {
            $customFields[] = $this->customFieldFactory->create([
                'key' => $code,
                'value' => $label
            ]);
        }
        $request->setStoreId($this->storeManager->getStore()->getId())
            ->setOrderId($order->getEntityId())
            ->setCustomerName(
                $order->getBillingAddress()->getFirstname() . ' '
                . $order->getBillingAddress()->getLastname()
            )->setCustomFields($customFields);

        $request->setCustomerId($this->customerSession->getCustomerId());

        $returnItems = [];
        foreach ($httpRequest->getParam('items') as $itemId => $item) {
            if (empty($item['return']) || empty($item['qty']) || $item['qty'] < 0.0001
                || empty($item['condition']) || empty($item['reason']) || empty($item['resolution'])
            ) {
                continue;
            }

            $returnItems[] = $requestRepository->getEmptyRequestItemModel()
                ->setQty((float)$item['qty'])
                ->setResolutionId((int)$item['resolution'])
                ->setReasonId((int)$item['reason'])
                ->setConditionId((int)$item['condition'])
                ->setOrderItemId((int)$itemId);
        }
        $request->setRequestItems($returnItems);
		$this->Increffentry($request);
        return $request;
    }

    /**
     * @param \Amasty\Rma\Api\Data\RequestInterface $request
     * @param string $comment
     * @param array $files
     *
     * @throws \Exception
     */
    public function saveNewReturnMessage($request, $comment, $files)
    {
        $message = $this->chatRepository->getEmptyMessageModel();
        $message->setIsRead(0)
            ->setMessage($comment)
            ->setCustomerId($this->customerSession->getCustomerId())
            ->setName($request->getCustomerName())
            ->setRequestId($request->getRequestId());

        if ($files) {
            $messageFiles = [];
            foreach ($files as $file) {
                $messageFile = $this->chatRepository->getEmptyMessageFileModel();
                $messageFile->setFilepath($file[FileUpload::FILEHASH])
                    ->setFilename($file[FileUpload::FILENAME]);
                $messageFiles[] = $messageFile;
            }
            $message->setMessageFiles($messageFiles);
			
        }

        try {
            $this->chatRepository->save($message, false);
        } catch (\Exception $e) {
            null;
        }
    }
	
	
	public function Increffentry($request)
	{
		if(!empty($this->getShipmentID($request->getOrderId()))){
			
			$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($request->getOrderId());
			
			
			$objectManagers = \Magento\Framework\App\ObjectManager::getInstance();
			$_resource = $objectManagers->get('Magento\Framework\App\ResourceConnection');
			$_connection = $_resource->getConnection();
			$_tableName = $_resource->getTableName('amasty_rma_reason');

			$sql = "Select reason_id,title FROM " . $_tableName;
			$amasty_rma_reason = $_connection->fetchAll($sql);
			
			$_amasty_rma_reason=array();
			
			foreach($amasty_rma_reason as $amasty_rma_reason_val){
				$_amasty_rma_reason[$amasty_rma_reason_val['reason_id']]=$amasty_rma_reason_val['title'];
			}
			
			
			
			$tracksCollection = $order->getTracksCollection();
			
			$transporter=array();
			$awbNumber=array();
			
			foreach ($tracksCollection->getItems() as $track) {				
				$transporter[] = $track->getTitle();
				$awbNumber[] = $track->getTrackNumber();
				break;				
			}
			
			
			$_uitems=array();
			$_products=array();
			
			
			foreach ($order->getAllVisibleItems() as $_item) {
				
				
				if ($_item->getParentItem()){
					continue;
				}
				
				$_products[$_item->getItemId()]=$_item->getSku();
			}
			
			
			foreach ($request->getRequestItems() as $_item) {
				
				for($_i=0;$_i<$_item->getQty();$_i++){
					
					$_uitems[]=array(
						"itemCode"=>$_products[$_item->getOrderItemId()],
						"channelSkuCode"=>$_products[$_item->getOrderItemId()],
						"reason"=>$_amasty_rma_reason[$_item->getReasonId()]
					);
					
					//$getRequestId=$_item->getRequestId();
					
				}				
			}
			
			
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$tableName = $resource->getTableName('amasty_rma_request');

			$sql = "Select max(request_id) as old_request_id FROM " . $tableName;
			$old_request_id = $connection->fetchone($sql);
			
			$getRequestId=$old_request_id+1;
			
						
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $this->asim_link.'/return/return-orders',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => false,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_POSTFIELDS =>'{
				   "forwardOrderCode":'.$request->getOrderId().',
				   "returnOrderCode":'.$getRequestId.',
				   "locationCode":"110",
				   "returnOrderTime":"'.gmdate(DATE_W3C).'",
				   "orderItems":'.json_encode($_uitems).',
				   "orderType":"CUSTOMER_RETURN",
				   "awbNumber":"'.implode(",",$awbNumber).'",
				   "transporter":"'.implode(",",$transporter).'"
				}',
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json',
					'authUsername: '.$this->asim_authUsername,
					'authPassword: '.$this->authPassword,
					'Accept: application/json'
				 ),
			));

			$response = curl_exec($curl);
			curl_close($curl);
			
			
			if(!empty($response)){
				
				$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/rma.log');
				$logger = new \Zend\Log\Logger();
				$logger->addWriter($writer);
				
				$logger->info('{
				   "forwardOrderCode":'.$request->getOrderId().',
				   "returnOrderCode":'.$getRequestId.',
				   "locationCode":"110",
				   "returnOrderTime":"'.gmdate(DATE_W3C).'",
				   "orderItems":'.json_encode($_uitems).',
				   "orderType":"CUSTOMER_RETURN",
				   "awbNumber":"'.implode(",",$awbNumber).'",
				   "transporter":"'.implode(",",$transporter).'"
				}');
				$logger->info($response);
			}
			
		}
	}
	
	
	
	public function getShipmentID($orderId)
	{
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->orderRepository = $this->_objectManager->create('\Magento\Sales\Api\OrderRepositoryInterface');
		
		$order = $this->orderRepository->get($orderId);
		$shipmentCollection = $order->getShipmentsCollection();
		
		foreach ($shipmentCollection as $shipment) {
			return $shipment->getId();return $shipment->getId();
		}
		return false;
	}
}
