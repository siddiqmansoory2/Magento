<?php
declare (strict_types = 1);

namespace Dolphin\Walletrewardpoints\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetTransactionalHistory implements ResolverInterface
{
    protected $_walletDataHelper;
    private $transactionHistoryData;

    public function __construct(
        \Dolphin\Walletrewardpoints\Helper\Data $walletDataHelper,
        \Dolphin\Walletrewardpoints\Model\Resolver\DataProvider\GetTransactionalHistory $transactionHistoryData
    ) {
        $this->_walletDataHelper = $walletDataHelper;
        $this->transactionHistoryData = $transactionHistoryData;
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
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        $transHistoryData = $this->transactionHistoryData->getTransactionalHistory($args['pageSize'], $args['currentPage']);
        return $transHistoryData;
    }

}
