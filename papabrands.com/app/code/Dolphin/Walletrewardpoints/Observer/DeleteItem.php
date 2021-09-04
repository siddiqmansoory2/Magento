<?php

namespace Dolphin\Walletrewardpoints\Observer;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class DeleteItem implements ObserverInterface
{
    public function __construct(
        ManagerInterface $messageManager,
        CustomerModel $customerModel,
        CatalogSession $catalogSession,
        DataHelper $dataHelper
    ) {
        $this->messageManager = $messageManager;
        $this->customerModel = $customerModel;
        $this->catalogSession = $catalogSession;
        $this->dataHelper = $dataHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $maxAllowCredit = $this->dataHelper->getCustomerMaxCredit();

        if ($maxAllowCredit == '') {
            $maxAllowCredit = 0;
        }
        $customerId = $this->dataHelper->getCustomerIdFromSession();
        if ($customerId) {
            $customer = $this->customerModel->load($customerId);
            $customerWalletCredit = $customer->getWalletCredit();
            $quote = $observer->getQuoteItem()->getQuote()->getAllItems();
            $observer->getQuoteItem()->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
            $grandTotal = $observer->getQuoteItem()->getQuote()->getGrandTotal();
            $applyCredit = $this->catalogSession->getApplyCredit();
            $grandSubtotal = $observer->getQuoteItem()->getQuote()->getSubtotal();

            $applyCredit = (abs($applyCredit) > $grandSubtotal) ? $grandSubtotal : abs($applyCredit);
            $this->catalogSession->setApplyCredit(-$applyCredit);

            foreach ($quote as $itemId => $item) {
                if ($item->getSku() == 'rewardpoints') {
                    $totalCredit = $customerWalletCredit + $item->getQty();
                    if (($item->getQty() > 0) && ($maxAllowCredit != 0) && ($totalCredit > $maxAllowCredit)) {
                        $finalQty = $maxAllowCredit - $customerWalletCredit;
                        $noticeMessage = "Currently you have %1 credit(s). You can have maximum %2 credit(s).";
                        $this->messageManager->addNotice(
                            __(
                                $noticeMessage,
                                $customerWalletCredit,
                                $maxAllowCredit
                            )
                        );
                        $this->saveItem($item, $finalQty);
                        $this->saveQuoteItem($observer);
                        $observer->getQuoteItem()->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
                    }
                } else {
                    $item->setGrandTotal($grandSubtotal);
                    $this->saveQuoteItem($observer);
                    $observer->getQuoteItem()->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
                }
            }
        }
    }

    private function saveItem($item, $finalQty)
    {
        $item->setQty($finalQty)->save();
    }

    private function saveQuoteItem($observer)
    {
        $observer->getQuoteItem()->getQuote()->save();
    }
}
