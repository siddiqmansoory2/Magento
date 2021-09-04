<?php

namespace Dolphin\Walletrewardpoints\Model;

use Dolphin\Walletrewardpoints\Model\ResourceModel\Subscriber as SubscriberResourceModel;
use Magento\Framework\Model\AbstractModel;

class Subscriber extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(SubscriberResourceModel::class);
    }

    public function transactionSubscribeSave($subscriberData)
    {
        $this->setData($subscriberData)->save();
    }
}
