<?php
declare (strict_types = 1);

namespace Dolphin\Walletrewardpoints\Model\Resolver;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetWalletCredit implements ResolverInterface
{
    private $walletDataHelper;

    public function __construct(
        \Dolphin\Walletrewardpoints\Helper\Data $walletDataHelper
    ) {
        $this->walletDataHelper = $walletDataHelper;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var CustomerInterface $customer */
        $customer = $value['model'];
        $customerId = (int) $customer->getId();
        $websiteId = (int) $customer->getWebsiteId();
        $walletCredit = $this->walletDataHelper->getWalletCredit($customerId);
        return $walletCredit;
    }
}
