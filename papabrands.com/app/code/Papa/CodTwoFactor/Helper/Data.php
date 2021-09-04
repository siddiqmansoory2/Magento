<?php
namespace Papa\CodTwoFactor\Helper;

use Magento\Catalog\Model\CategoryFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $orderItemRepository;

    protected $categoryRepository;


    /**
     * @var Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        CategoryFactory $categoryFactory
    ) {
        parent::__construct($context);
        $this->orderItemRepository = $orderItemRepository;
        $this->categoryRepository = $categoryRepository;
        $this->_categoryFactory = $categoryFactory;
    }
      /**
     * Get children categories 
     * 
     * @param $categoryId Parent category id
     * @return Magento\Catalog\Model\ResourceModel\Category\Collection
     */

    public function getOrderItem($orderItemId) {
        $orderItem = $this->orderItemRepository->get($orderItemId);

        return $orderItem;
    }

    public function getChildCategories($categoryId)
    {
        $_category = $this->_categoryFactory->create();
        $category = $_category->load($categoryId);
        $childrenCategories = $category->getChildrenCategories()->addAttributeToFilter(
            'include_in_menu',
            1
        );

        return $childrenCategories;
    }

    public function getCategoryUrl($categoryId)
    {
        $_category = $this->_categoryFactory->create();
        $category = $_category->load($categoryId);
        $categoryUrl = $category->getUrl();

        return $categoryUrl;
    }

}