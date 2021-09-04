<?php

namespace Dolphin\Walletrewardpoints\Controller\Customer;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Checkout\Model\Cart as CheckoutCartModel;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Pricing\Helper\Data as Helper;

class Discount extends Action
{
    protected $scopeConfig;
    protected $customerSession;
    protected $removeCredit;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        CheckoutCartModel $cart,
        CatalogSession $catalogSession,
        CustomerSession $customerSession,
        Helper $helper,
        CheckoutSession $checkoutSession,
        Escaper $escaper,
        DataObject $dataObject,
        DataHelper $dataHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->cart = $cart;
        $this->catalogSession = $catalogSession;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->escaper = $escaper;
        $this->dataObject = $dataObject;
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }

    public function dispatch(RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    protected function _goBackUrl($backUrl = null)
    {
        $resultRedirects = $this->resultRedirectFactory->create();
        if ($backUrl || $backUrl = $this->getBackUrl($this->_redirect->getRefererUrl())) {
            $resultRedirects->setUrl($backUrl);
        }
        return $resultRedirects;
    }

    public function execute()
    {
        $inputCredit = 0;
        $creditApply = 0;
        $creditCancel = 0;
        $this->removeCredit = $this->getRequest()->getParam('remove-credit', 3);
        if ($this->removeCredit != 3) {
            if ($this->removeCredit) {
                $creditCancel = 1;
            } else {
                $creditApply = 1;
                $inputCredit = trim($this->getRequest()->getParam('apply_credit_value'));
            }
        } else {
            $inputCredit = $this->getRequest()->getpost('inputCreditVal');
            $creditApply = $this->getRequest()->getpost('credit_apply');
            $creditCancel = $this->getRequest()->getpost('credit_cancel');
        }
        $cartallitems = $this->cart->getQuote()->getAllItems();
        $orderGrandTotal = $this->cart->getQuote()->getGrandTotal();
        $grandTotalAfterDiscount = $orderGrandTotal - $inputCredit;
        if ($grandTotalAfterDiscount < 0 && $creditApply) {
            if ($this->removeCredit != 3) {
                $this->messageManager->addErrorMessage(__('Current credit(s) are greater than grand total.'));
                return $this->_goBackUrl();
            } else {
                $result[] = [
                    'type' => 'error',
                    'message' => __('Current credit(s) are greater than grand total.'),
                ];
                $this->dataObject->setData($result);
                $this->getResponse()->representJson($this->dataObject->toJson());
                return $this->getBackUrl();
            }
        }
        if ($creditApply) {
            if ($cartallitems) {
                foreach ($cartallitems as $itemId => $item) {
                    if (($item->getSku() == 'rewardpoints') && ($item->getQty() > 0)) {
                        if ($this->removeCredit != 3) {
                            $this->messageManager->addErrorMessage(
                                __('Credit is not applied on "' . $item->getName() . '" product.')
                            );
                            return $this->_goBackUrl();
                        } else {
                            $result[] = [
                                'type' => 'error',
                                'message' =>
                                __(
                                    'Credit is not applied on "' . $item->getName() . '" product.'
                                ),
                            ];
                            $this->dataObject->setData($result);
                            $this->getResponse()->representJson($this->dataObject->toJson());
                            return $this->getBackUrl();
                        }
                    }
                }
            }
            $this->checkCreditWithCoupon($inputCredit);
        }
        $this->setCreditToSession($creditCancel, $inputCredit);
        $this->cart->save();
        if ($this->removeCredit != 3) {
            return $this->_goBackUrl();
        }
        return $this->getBackUrl();
    }

    private function checkCreditWithCoupon($inputCredit)
    {
        $allowWithCoupon = $this->dataHelper->getUseCreditWithCoupon();

        $couponCode = $this->checkoutSession->getQuote()->getCouponCode();
        if ($allowWithCoupon != 0 || ($allowWithCoupon == 0 && !$couponCode)) {
            if ($this->removeCredit != 3) {
                $this->messageManager->addSuccessMessage(
                    __(
                        'Your credit(s) %1 was successfully applied.',
                        $this->escaper->escapeHtml($inputCredit)
                    )
                );
            } else {
                $result[] = [
                    'type' => 'success',
                    'message' =>
                    __(
                        'Your credit(s) %1 was successfully applied.',
                        $this->escaper->escapeHtml($inputCredit)
                    ),
                ];
                $this->dataObject->setData($result);
                $this->getResponse()->representJson($this->dataObject->toJson());
            }
        }
    }

    private function setCreditToSession($creditCancel, $inputCredit)
    {
        if ($creditCancel) {
            if ($this->removeCredit != 3) {
                $this->messageManager->addSuccessMessage(__('Your credit(s) was successfully canceled.'));
            } else {
                $result[] = [
                    'type' => 'success',
                    'message' =>
                    __(
                        'Your credit(s) was successfully canceled.'
                    ),
                ];
                $this->dataObject->setData($result);
                $this->getResponse()->representJson($this->dataObject->toJson());
            }
            $this->catalogSession->setApplyCredit(0);
        } else {
            $this->catalogSession->setApplyCredit(-$inputCredit);
        }
    }

    protected function getBackUrl($defaultUrl = null)
    {
        $returnsUrl = $this->getRequest()->getParam('return_url');
        if ($returnsUrl && $this->_isInternalUrl($returnsUrl)) {
            $this->messageManager->getMessages()->clear();
            return $returnsUrl;
        }
        $shouldRedirectToCarts = $this->scopeConfig->getValue(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($shouldRedirectToCarts || $this->getRequest()->getParam('in_cart')) {
            if ($this->getRequest()->getActionName() == 'add' && !$this->getRequest()->getParam('in_cart')) {
                $this->_checkoutSession->setContinueShoppingUrl($this->_redirect->getRefererUrl());
            }
            return $this->_url->getUrl('checkout/cart');
        }
        return $defaultUrl;
    }

    protected function _isInternalUrl($url)
    {
        if (strpos($url, 'http') === false) {
            return false;
        }
        $storeInfo = $this->_storeManager->getStore();
        $unsecureUrl = strpos($url, $storeInfo->getBaseUrl()) === 0;
        $secureUrl = strpos($url, $storeInfo->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, true)) === 0;
        return $unsecureUrl || $secureUrl;
    }
}
