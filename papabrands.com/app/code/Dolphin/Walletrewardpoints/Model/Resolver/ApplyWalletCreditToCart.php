<?php
declare (strict_types = 1);

namespace Dolphin\Walletrewardpoints\Model\Resolver;

use Dolphin\Walletrewardpoints\Helper\Data as DataHelper;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Checkout\Model\Cart as CheckoutCartModel;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Quote\Api\CouponManagementInterface;

class ApplyWalletCreditToCart implements ResolverInterface
{
    private $getCartForUser;
    private $couponManagement;

    public function __construct(
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        CatalogSession $catalogSession,
        CheckoutSession $checkoutSession,
        DataHelper $dataHelper,
        CheckoutCartModel $cart,
        GetCartForUser $getCartForUser,
        CouponManagementInterface $couponManagement
    ) {
        $this->priceHelper = $priceHelper;
        $this->catalogSession = $catalogSession;
        $this->checkoutSession = $checkoutSession;
        $this->dataHelper = $dataHelper;
        $this->cart = $cart;
        $this->getCartForUser = $getCartForUser;
        $this->couponManagement = $couponManagement;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        if (empty($args['input']['credit'])) {
            throw new GraphQlInputException(__('Required parameter "credit" is missing'));
        }
        $allowCreditCoupon = $this->dataHelper->getMaxAllowCreditOrder();
        if (!is_int($args['input']['credit']) || $args['input']['credit'] >= $allowCreditCoupon) {
            $allowCreditCoupon = $this->priceHelper->currency($allowCreditCoupon, true, false);
            throw new GraphQlInputException(__("Please enter a value less than or equal to %1. Maximum redeemable credit(s) are %1", $allowCreditCoupon));
        }
        $couponCode = $args['input']['credit'];
        $inputCredit = $args['input']['credit'];
        $cartallitems = $this->cart->getQuote()->getAllItems();
        if (count($cartallitems)) {
            foreach ($cartallitems as $itemId => $item) {
                if (($item->getSku() == 'rewardpoints') && ($item->getQty() > 0)) {
                    $showMsg = (__('Credit is not applied on "' . $item->getName() . '" product.'));
                    return [
                        'status' => "FAILED",
                        'message' => $showMsg,
                    ];
                }
            }
        }
        $allowWithCoupon = $this->dataHelper->getUseCreditWithCoupon();
        $couponCode = $this->checkoutSession->getQuote()->getCouponCode();
        $isCredit = $this->catalogSession->getApplyCredit();
        $isCredit = abs($isCredit);
        if ($isCredit) {
            $showMsg = (__("'Your credit(s) was already applied.'"));
            return [
                'status' => "FAILED",
                'message' => $showMsg,
            ];
        }
        if ($allowWithCoupon != 0 || ($allowWithCoupon == 0 && !$couponCode)) {
            $this->catalogSession->setApplyCredit(-$inputCredit);
            $this->cart->save();
            $showMsg = (__("'Your credit(s) %1 was successfully applied.'", $inputCredit));
            return [
                'status' => "SUCCESS",
                'message' => $showMsg,
            ];
        } else {
            $showMsg = (__("'Your credit(s) value is not allow.'"));
            return [
                'status' => "FAILED",
                'message' => $showMsg,
            ];
        }
    }
}
