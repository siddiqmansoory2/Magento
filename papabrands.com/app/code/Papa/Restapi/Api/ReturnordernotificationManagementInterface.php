<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Api;

interface ReturnordernotificationManagementInterface
{

    /**
     * PUT for returnordernotification api
     * @param mixed $returnOrderCode
     * @param mixed $locationCode
     * @param mixed $orderItems
     * @return string
     */
    public function putReturnordernotification($returnOrderCode,$locationCode,$orderItems);
}

