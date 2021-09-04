<?php
namespace Magecomp\Codverification\Plugin;

use Magento\Framework\Exception\LocalizedException;

class QuoteManagement
{
    protected $helperdata;
    protected $checkoutSession;
    protected $quoteRepository;

    public function __construct(
        \Magecomp\Codverification\Helper\Data $helperdata,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->helperdata = $helperdata;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    public function beforePlaceOrder(\Magento\Quote\Model\QuoteManagement $subject, $cartId, $paymentMethod = null)
    {
        if($this->helperdata->isEnabled())
        {
            $quoteId = $this->checkoutSession->getQuote()->getId();
            if ($quoteId > 0)
            {
                $quote = $this->quoteRepository->get($quoteId);
                $codverify = $quote->getCodverification();
                $paymentMethodCode = $quote->getPayment()->getMethod();

                if($paymentMethodCode == 'cashondelivery' && !$codverify)
                {
                    throw new LocalizedException(__('To Make Use Of Cash On Delivery, You Must Verify Your Billing Address Telephone Number.'));
                }
            }
        }
        return [$cartId, $paymentMethod];
    }
}
