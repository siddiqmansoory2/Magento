<?php
declare (strict_types = 1);

namespace Dolphin\Walletrewardpoints\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetWalletCreditEmailStatus implements ResolverInterface
{
    private $subscriptionData;

    public function __construct(
        \Dolphin\Walletrewardpoints\Model\Resolver\DataProvider\GetWalletRewardEmailSubscription $subscriptionData
    ) {
        $this->subscriptionData = $subscriptionData;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $emailStatus = $this->subscriptionData->getSubscriptionStatus();
        if (count($emailStatus->getData())) {
            if ($emailStatus->getSubscriberStatus()) {
                $status = 1;
            } else {
                $status = 0;
            }
        } else {
            $status = 0;
        }
        return $status;
    }

}
