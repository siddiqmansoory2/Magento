<?php

namespace Dolphin\Walletrewardpoints\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class OrderActions extends Column
{
    private $urlBuilder;

    const ORDER_URL_PATH_EDIT = 'sales/order/view';

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderFactory $orderFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->orderFactory = $orderFactory;
        $this->urlBuilder = $urlBuilder;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['order_id'])) {
                    $order = $this->orderFactory->create()->loadByIncrementId($item['order_id']);
                    $item[$name] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::ORDER_URL_PATH_EDIT,
                                [
                                    'order_id' => $order->getId(),
                                ]
                            ),
                            'target' => '_blank',
                            'label' => __($item['order_id']),
                        ],
                    ];
                } else {
                    $item[$name] = [
                        'edit' => [
                            'label' => __('-'),
                        ],
                    ];
                }
            }
        }
        return $dataSource;
    }
}
