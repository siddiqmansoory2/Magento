<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OneStepCheckout
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\OneStepCheckout\Controller\Cart;

class Update extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Sidebar
     */
    protected $_sidebar;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $_dataObjectFactory;
    /**
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     */
    protected $_cartTotalRepositoryInterface;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    /**
     * Update constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalRepositoryInterface
     * @param \Magento\Checkout\Model\Sidebar $sidebar
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalRepositoryInterface,
        \Magento\Checkout\Model\Sidebar $sidebar,
        \Magento\Checkout\Model\Cart $cart
    ) {
        parent::__construct($context);
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_jsonHelper = $jsonHelper;
        $this->_dataObjectFactory = $dataObjectFactory;
        $this->_sidebar = $sidebar;
        $this->_cartTotalRepositoryInterface = $cartTotalRepositoryInterface;
        $this->cart = $cart;
    }
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\DataObject $qtyData */
        $qtyData = $this->_dataObjectFactory->create([
            'data' => $this->_jsonHelper->jsonDecode($this->getRequest()->getContent()),
        ]);
        $updateType = $qtyData->getData('updateType');
        $result = [];
        $result['error'] = '';
        try {
            if ($updateType == 'update') {
                $this->_sidebar->checkQuoteItem($qtyData->getData('itemId'));
                $this->_sidebar->updateQuoteItem($qtyData->getData('itemId'), $qtyData->getData('qty'));
            }
            if ($updateType == 'delete') {
                $this->_sidebar->removeQuoteItem($qtyData->getData('itemId'));
            }
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }
        if ($this->cart->getSummaryQty() == 0) {
            $result['cartEmpty'] = true;
        }
        if ($this->cart->getQuote()->isVirtual()) {
            $result['is_virtual'] = true;
        } else {
            $result['is_virtual'] = false;
        }
        $resultJson = $this->_resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
