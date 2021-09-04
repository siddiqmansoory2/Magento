<?php

namespace Dolphin\Walletrewardpoints\Observer\Coupon;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class ApplyCheckoutCoupon implements ObserverInterface
{
    /**
     * [__construct Initialise Dependencies]
     * @param ManagerInterface $messageManager [description]
     * @param CatalogSession   $catalogSession [description]
     * @param DataHelper       $dataHelper     [description]
     * @param Http             $request        [description]
     */
    public function __construct(
        ManagerInterface $messageManager,
        CatalogSession $catalogSession,
        DataHelper $dataHelper,
        Http $request
    ) {
        $this->messageManager = $messageManager;
        $this->catalogSession = $catalogSession;
        $this->dataHelper = $dataHelper;
        $this->request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $couponCode = $observer->getEvent()->getRule()->getCode();
        $allowWithCoupon = $this->dataHelper->getUseCreditWithCoupon();
        if ($couponCode != "" && $allowWithCoupon == 0) {
            if ($this->catalogSession->getApplyCredit() != 0) {
                $this->catalogSession->setApplyCredit(0);
                $action = $this->request->getFullActionName();
                if ($action == 'checkout_cart_couponPost') {
                    $this->messageManager->addError(
                        __('Wallet Credit is not apply when coupon is applied.')
                    );
                }
            }
        }
    }
}
