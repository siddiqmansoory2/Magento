<?php

namespace Dolphin\Walletrewardpoints\Model;

use Dolphin\Walletrewardpoints\Model\ResourceModel\Withdraw\CollectionFactory;

class WithdrawDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $loadedData;

    // @codingStandardsIgnoreStart
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $WithdrawCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $WithdrawCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
    // @codingStandardsIgnoreEnd

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $Withdraw) {
            $this->loadedData[$Withdraw->getId()] = $Withdraw->getData();
        }
        return $this->loadedData;
    }
}
