<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Loginwithotp\Controller\Index;

use Magento\Quote\Model\QuoteRepository;
use Magento\Checkout\Model\Session as CheckoutSession;

class UpdateCart extends \Magento\Framework\App\Action\Action
{
	protected $quoteRepository;
	
	/**
     * @var CheckoutSession
     */
    private $checkoutSession;

	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        CheckoutSession $checkoutSession
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');

        $itemId = $this->getRequest()->getParam('itemId');
        $qty = $this->getRequest()->getParam('qty');
        
        $cartId = $this->checkoutSession->getQuote()->getId();
        $quote = $this->quoteRepository->get($cartId);
        foreach ($quote->getAllVisibleItems() as $item) {
        	if($item->getItemId() == $itemId){
        		$item->setQty($qty);
        	}
        }
        $this->quoteRepository->save($quote);
        $quote->collectTotals();
    }
}
?>