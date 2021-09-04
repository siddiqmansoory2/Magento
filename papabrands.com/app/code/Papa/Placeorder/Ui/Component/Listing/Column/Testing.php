<?php
namespace Papa\Placeorder\Ui\Component\Listing\Column;
 
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
 

class Testing extends Column
{
	protected $escaper;
 
	protected $systemStore;
	protected $productloader;
 
 
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Model\ProductFactory $productloader,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
		
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->escaper = $escaper;
		$this->productloader = $productloader;
		$this->_orderRepository =  $this->_objectManager->create('Magento\Sales\Api\Data\OrderInterface');
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
		
		
		if (isset($dataSource['data']['items'])) {
			foreach ($dataSource['data']['items'] as & $item) {
				try {
					if ($order = $this->_orderRepository->load($item['order_id'])) {
						
						$payment = $order->getPayment();
						$method = $payment->getMethodInstance();
						$methodTitle = $method->getTitle();
						
						$payment_method_code = $order->getPayment()->getMethodInstance()->getCode();
						
						$amount_due='';
						
						if ($payment_method_code == 'cashondelivery') {
							
							$item[$this->getData('name')] = "Pending Payment";
							
							
							$tracksCollection = $order->getTracksCollection();
							
							$awbNumber='';
							
							foreach ($tracksCollection->getItems() as $track) {				
								$awbNumber = $track->getTrackNumber();			 
							}
							
							if(!empty($awbNumber)){
								
								$_url_details='https://api.bluedart.com/servlet/RoutingServlet?handler=tnt&action=custawbquery&loginid=DDP81791&awb=awb&numbers='.$awbNumber.'&format=xml&lickey=%20kjkirtetmlnfseekprgg4qjplxluqlle&verno=1.3&scan=1';

								$ch = curl_init();
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
								curl_setopt($ch, CURLOPT_URL, $_url_details);
								$data = curl_exec($ch);
								curl_close($ch);
								$xml = (array)simplexml_load_string($data);		
								
								if($xml && is_array($xml) && array_key_exists('Shipment',$xml)){
									$Shipment=(array)$xml['Shipment'];	
									
									if($Shipment && is_array($Shipment) && array_key_exists('Status',$Shipment)){
										if($Shipment['Status']=='SHIPMENT DELIVERED'){
											$item[$this->getData('name')] = "Paid";
										}
									}
								}
							}
							
							
						}else{
							$item[$this->getData('name')] = "Paid";
						}
						
						
						
						
					}
				} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {

				}
			}
		}

		return $dataSource;
  
		
		/*
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = "test";
            }
        }
 
        return $dataSource;
    */}
}