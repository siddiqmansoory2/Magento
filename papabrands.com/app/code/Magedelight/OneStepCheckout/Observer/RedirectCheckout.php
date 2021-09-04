<?php

namespace Magedelight\OneStepCheckout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Controller\ResultFactory;

class RedirectCheckout implements ObserverInterface
{

    public function __construct(
        CheckoutSession $checkoutSession,
        ResultFactory $resultFactory,
        \Magento\Framework\UrlInterface $url,
        \Magedelight\OneStepCheckout\Helper\Data $data
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->resultFactory = $resultFactory;
        $this->url = $url;
        $this->helper = $data;
    }

    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        if ($this->helper->allowRedirectCheckoutAfterProductAddToCart()) {
            if (!$observer->getEvent()->getRequest()->isAjax()) {
                $redirectUrl = $this->url->getUrl('checkout', ['_secure' => true]);
                $observer->getEvent()->getResponse()->setRedirect($redirectUrl);
            }
            $this->checkoutSession->setNoCartRedirect(true);
        }
    }
}
