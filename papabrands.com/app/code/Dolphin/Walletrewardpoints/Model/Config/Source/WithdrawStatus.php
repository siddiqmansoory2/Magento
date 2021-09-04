<?php

namespace Dolphin\Walletrewardpoints\Model\Config\Source;

use Dolphin\Walletrewardpoints\Model\WithdrawFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\OptionSourceInterface;

class WithdrawStatus implements OptionSourceInterface
{
    public function __construct(
        WithdrawFactory $withdrawFactory,
        Http $request
    ) {
        $this->withdrawFactory = $withdrawFactory;
        $this->request = $request;
    }

    public function toOptionArray()
    {
        $options = [
            [
                "value" => "0",
                "label" => ('Pending'),
            ],
            [
                "value" => "1",
                "label" => ('Approve'),
            ],
            [
                "value" => "2",
                "label" => ('Reject'),
            ],
        ];
        $withdraw_id = $this->request->getParam('withdraw_id');
        if ($withdraw_id) {
            $status = $this->withdrawFactory->create()->load($withdraw_id)->getStatus();
            if ($status == 1) {
                unset($options['0']);
            }
        }

        return $options;
    }
}
