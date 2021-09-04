<?php
namespace Magedelight\OneStepCheckout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;
use Magedelight\OneStepCheckout\Helper\Data;

class SalesModelServiceQuoteSubmitBefore implements ObserverInterface
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var Data
     */
    private $oscHelper;

    /**
     * SalesModelServiceQuoteSubmitBefore constructor.
     * @param QuoteRepository $quoteRepository
     * @param Data $oscHelper
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        Data $oscHelper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->oscHelper = $oscHelper;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     * @throws \Exception
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getOrder();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->get($order->getQuoteId());
        if ($quote->getMdoscExtraFee()) {
            $extraFee = $quote->getMdoscExtraFee();
            $extraBaseFee = $quote->getBaseMdoscExtraFee();
            $order->setData('mdosc_extra_fee', $extraFee);
            $order->setData('base_mdosc_extra_fee', $extraBaseFee);
        }

        return $this;
    }
}
