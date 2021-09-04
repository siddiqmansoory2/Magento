<?php
declare (strict_types = 1);

namespace Dolphin\Walletrewardpoints\Model\Resolver;

use Dolphin\Walletrewardpoints\Model\Sales\Total\CreditDiscount;
use Dolphin\Walletrewardpoints\Model\Withdraw;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class WalletRewardWithdrawCredit implements ResolverInterface
{
    protected $withdraw;
    protected $creditDiscountModel;

    public function __construct(
        CreditDiscount $creditDiscountModel,
        Withdraw $withdraw
    ) {
        $this->creditDiscountModel = $creditDiscountModel;
        $this->withdraw = $withdraw;
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
        if (!is_int($args['credit']) || $args['credit'] <= 0) {
            throw new GraphQlInputException(__('Specify the credit Int value greater then zero.'));
        }
        if (empty($args['email'])) {
            throw new GraphQlInputException(__('Specify the email String value.'));
        }
        if ((!empty($args['email'])) && (!filter_var($args['email'], FILTER_VALIDATE_EMAIL))) {
            throw new GraphQlInputException(__('Email is a not valid email address.'));
        }
        if (empty($args['reason'])) {
            throw new GraphQlInputException(__('Specify the reason String value.'));
        }
        try {
            $data = [];
            $data['customer_id'] = (int) $context->getUserId();
            $data['paypal_email'] = $args['email'];
            $data['reason'] = $args['reason'];
            $data['status'] = 0;
            $data['requested_date'] = date("Y-m-d H:i:s");
            $data['updated_date'] = date("Y-m-d H:i:s");
            $data['credit'] = $this->creditDiscountModel->convertPrice($args['credit'], 1);
            $model = $this->withdraw->setData($data);
            $model->save();
            $showMsg = (
                __(
                    'Your withdraw request submitted successfully. We will review your request and accept it as soon as possible.'
                )
            );
            return [
                'status' => "SUCCESS",
                'message' => $showMsg,
            ];
        } catch (Exception $e) {
            $showMsg = (__('Something went wrong while saving your subscription.'));
            return [
                'status' => "FAILED",
                'message' => $showMsg,
            ];

        }

    }
}
