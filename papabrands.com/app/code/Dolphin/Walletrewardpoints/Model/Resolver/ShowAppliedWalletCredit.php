<?php
declare (strict_types = 1);

namespace Dolphin\Walletrewardpoints\Model\Resolver;

use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ShowAppliedWalletCredit implements ResolverInterface
{
    private $catalogSession;

    public function __construct(
        CatalogSession $catalogSession
    ) {
        $this->catalogSession = $catalogSession;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $cart = $value['model'];
        $cartId = $cart->getId();
        $creditVal = 0;
        $isCredit = $this->catalogSession->getApplyCredit();
        $isCredit = abs($isCredit);
        if ($isCredit) {
            $creditVal = $isCredit;
        }
        return $creditVal;
    }
}
