<?php

namespace Meetanshi\OrderTracking\Api;

use Magento\Tests\NamingConvention\true\mixed;

interface OrderTrackingInterface
{
    /**
     * @param int $orderId
     * @param mixed $mailId
     * @return mixed
     */
    public function trackOrder($orderId, $mailId);
}
