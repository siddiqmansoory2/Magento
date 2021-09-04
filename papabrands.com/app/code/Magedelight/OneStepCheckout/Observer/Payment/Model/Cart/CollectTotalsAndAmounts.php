<?php
namespace Magedelight\OneStepCheckout\Observer\Payment\Model\Cart;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;

class CollectTotalsAndAmounts implements ObserverInterface
{
    protected $quoteRepository;

    public function __construct(
        QuoteRepository $quoteRepository
    ){
        $this->quoteRepository = $quoteRepository;
    }

    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Paypal\Model\Cart $cart */
        $cart = $observer->getCart();
        $id = $cart->getSalesModel()->getDataUsingMethod('entity_id');
        if (!$id){
            $id = $cart->getSalesModel()->getDataUsingMethod('quote_id');
        }
        $quote = $this->quoteRepository->get($id);

        $labels = [];
        $baseFeeAmount = 0;

        if ($quote->getBaseMdoscExtraFee()) {
            $baseFeeAmount = $quote->getBaseMdoscExtraFee();
        }

        $cart->addCustomItem(
            implode(', ', $labels),
            1,
            $baseFeeAmount
        );
    }
}