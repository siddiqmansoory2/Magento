<?php
declare (strict_types = 1);

namespace Dolphin\Walletrewardpoints\Model\Resolver;

use Dolphin\Walletrewardpoints\Helper\Transaction as TransactionHelper;
use Dolphin\Walletrewardpoints\Model\Subscriber;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class SetWalletRewardEmailSubscription implements ResolverInterface
{
    protected $subscriber;
    protected $_customerSession;

    public function __construct(
        Subscriber $subscriber,
        TransactionHelper $transactionHelper,
        Session $customerSession
    ) {
        $this->subscriber = $subscriber;
        $this->transactionHelper = $transactionHelper;
        $this->_customerSession = $customerSession;
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

        if (!is_bool($args['subscription'])) {
            throw new GraphQlInputException(__('Specify the subscription boolean value.'));
        }

        if (!isset($args['subscription'])) {
            throw new GraphQlInputException(__('Specify the subscription boolean value.'));
        }

        try {
            $customerId = (int) $context->getUserId();
            $isSubscribedCustomer = $this->subscriber->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->getFirstItem();
            $isSubscribedParam = $args['subscription'];
            if (!$isSubscribedCustomer->getData() || ($isSubscribedParam != $isSubscribedCustomer->getSubscriberStatus())) {
                $custEmail = $this->_customerSession->getCustomer()->getEmail();
                $date = date("Y-m-d H:i:s");
                $subscribeData = [
                    'customer_id' => $customerId,
                    'subscriber_email' => $this->_customerSession->getCustomer()->getEmail(),
                    'subscriber_status' => $args['subscription'],
                    'subscribe_date' => $date,
                ];
                if ($isSubscribedCustomer->getData()) {
                    $subscriber_id = $isSubscribedCustomer->getSubscriberId();
                    $subscribeData['subscriber_id'] = $subscriber_id;
                    unset($subscribeData['subscribe_date']);
                }
                $this->subscriber->transactionSubscribeSave($subscribeData);
                if ($args['subscription']) {
                    $subMsg = (__('We have saved your subscription.'));
                    // Send subscription email to customer
                    $this->transactionHelper->transactionSubscription();
                } else {
                    $subMsg = (__('We have removed your transaction subscription.'));
                    // Send unsubscription email to customer
                    $this->transactionHelper->transactionUnsubscription();
                }
            } else {
                $subMsg = (__('We have updated your subscription.'));
            }
            return ['status' => $subMsg];
        } catch (AuthenticationException $e) {
            throw new GraphQlAuthenticationException(__($e->getMessage()), $e);
        }
    }
}
