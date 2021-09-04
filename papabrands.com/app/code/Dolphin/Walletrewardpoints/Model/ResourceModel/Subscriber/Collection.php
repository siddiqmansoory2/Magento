<?php

namespace Dolphin\Walletrewardpoints\Model\ResourceModel\Subscriber;

use Dolphin\Walletrewardpoints\Model\ResourceModel\Subscriber as SubscriberResourceModel;
use Dolphin\Walletrewardpoints\Model\Subscriber as SubscriberModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            SubscriberModel::class,
            SubscriberResourceModel::class
        );
    }
}
