<?php
    namespace Papa\CodTwoFactor\Helper;

    use Magento\Framework\App\Helper\AbstractHelper;

    class Success extends AbstractHelper
    {
        protected $_storeManager;

        public function __construct(
            \Magento\Framework\App\Helper\Context $context,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Sales\Model\OrderFactory $orderFactory,
            \Magento\Catalog\Api\ProductRepositoryInterface $product,
            \Magento\Catalog\Model\CategoryFactory $categoryFactory
        ) {
            $this->_storeManager = $storeManager;
            $this->orderFactory = $orderFactory;
            $this->product = $product;
            $this->categoryFactory = $categoryFactory;
              
            parent::__construct($context);
        }

        public function getProduct($productid)
        {
            return $this->product->getById($productid);
        }

        public function getCategory($categoryid)
        {
            return $this->categoryFactory->create()->load($categoryid);
        }

        public function getOrder($orderid)
        {
            return $this->orderFactory->create()->loadByIncrementId($orderid);
        }

        public function getMediaUrl($path)
        {
            return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path;
        }
    }