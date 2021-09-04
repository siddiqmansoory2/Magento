<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Api;

interface PushsellercancellationManagementInterface
{

    /**
     * PUT for pushsellercancellation api
     * @param mixed $orderCode
     * @param mixed $locationCode
     * @param mixed $orderItems
     * @return string
     */
    public function putPushsellercancellation($orderCode,$locationCode,$orderItems);
}

