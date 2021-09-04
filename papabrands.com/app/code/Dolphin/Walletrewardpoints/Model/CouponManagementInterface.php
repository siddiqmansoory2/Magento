<?php

namespace Dolphin\Walletrewardpoints\Model;

use Magento\Catalog\Model\Session;
use Magento\Checkout\Model\Cart as CheckoutCartModel;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

class CouponManagementInterface implements \Magento\Quote\Api\CouponManagementInterface
{
    protected $quoteRepository;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        CheckoutCartModel $cart,
        Session $catalogSession
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->cart = $cart;
        $this->catalogSession = $catalogSession;
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        return $quote->getCouponCode();
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId, $couponCode)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }
        if (!$quote->getStoreId()) {
            throw new NoSuchEntityException(__('Cart isn\'t assigned to correct store'));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);

        try {
            $quote->setCouponCode($couponCode);
            $this->quoteRepository->save($quote->collectTotals());
            $baseGrandTotal = $quote->getGrandTotal();
            if ($baseGrandTotal < 0) {
                $applyCredit = $this->catalogSession->getApplyCredit();
                $applyCredit = -1.00 * (abs($applyCredit) - abs($baseGrandTotal));
                $this->catalogSession->setApplyCredit($applyCredit);
                $this->catalogSession->setCreditFeeAmount($applyCredit);
            }
            $this->cart->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __("The coupon code couldn't be applied. Verify the coupon code and try again.")
            );
        }
        if ($quote->getCouponCode() != $couponCode) {
            throw new NoSuchEntityException(__("The coupon code isn't valid. Verify the code and try again."));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);
        try {
            $quote->setCouponCode('');
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __("The coupon code couldn't be deleted. Verify the coupon code and try again.")
            );
        }
        if ($quote->getCouponCode() != '') {
            throw new CouldNotDeleteException(
                __("The coupon code couldn't be deleted. Verify the coupon code and try again.")
            );
        }
        return true;
    }
}
