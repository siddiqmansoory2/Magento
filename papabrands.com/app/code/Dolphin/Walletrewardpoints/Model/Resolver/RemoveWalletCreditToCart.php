<?php
declare (strict_types = 1);

namespace Dolphin\Walletrewardpoints\Model\Resolver;

use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Quote\Api\CouponManagementInterface;

class RemoveWalletCreditToCart implements ResolverInterface
{
    private $getCartForUser;
    private $couponManagement;

    public function __construct(
        CatalogSession $catalogSession,
        GetCartForUser $getCartForUser,
        CouponManagementInterface $couponManagement
    ) {
        $this->catalogSession = $catalogSession;
        $this->getCartForUser = $getCartForUser;
        $this->couponManagement = $couponManagement;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $isCredit = $this->catalogSession->getApplyCredit();
        $isCredit = abs($isCredit);
        if ($isCredit) {
            $this->catalogSession->setApplyCredit(0);
            $showMsg = (__('Your credit(s) was successfully canceled.'));
            return [
                'status' => "SUCCESS",
                'message' => $showMsg,
            ];
        } else {
            $showMsg = (__('There are no any credit apply found. Please apply credit first.'));
            return [
                'status' => "FAILED",
                'message' => $showMsg,
            ];
        }
    }
}
