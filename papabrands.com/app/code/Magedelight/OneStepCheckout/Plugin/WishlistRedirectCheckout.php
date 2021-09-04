<?php

namespace Magedelight\OneStepCheckout\Plugin;

use Magento\Framework\Controller\ResultFactory;
use Magedelight\OneStepCheckout\Helper\Data;

class WishlistRedirectCheckout
{
    public $url;
    public $resultFactory;
    protected $resourceConnection;
    protected $helperData;

    public function __construct(
        \Magento\Framework\UrlInterface $url,
        ResultFactory $resultFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        Data $helperData
    ) {
        $this->url = $url;
        $this->resultFactory = $resultFactory;
        $this->resourceConnection = $resourceConnection;
        $this->helperData = $helperData;
    }

    public function afterExecute(\Magento\Wishlist\Controller\Index\Cart $subject, $resultRedirect)
    {
         $isRedirectEnable = $this->helperData->allowRedirectCheckoutAfterProductAddToCart();
        if ($isRedirectEnable) {
            $item_id = $subject->getRequest()->getParam('item');
            $connection  = $this->resourceConnection->getConnection();
            $tableName   = $connection->getTableName('wishlist_item_option');
            $query = 'SELECT * FROM '.$tableName.' WHERE wishlist_item_id = '.$item_id.' ';

            $results = $this->resourceConnection->getConnection()->fetchAll($query);
            if (empty($results)) {
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                 $redirectUrl = $this->url->getUrl('checkout', ['_secure' => true]);
                 $resultRedirect->setUrl($redirectUrl);
            }
        }
         return $resultRedirect;
    }
}
