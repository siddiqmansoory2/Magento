<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Api;

interface UpdateorderpropertiesManagementInterface
{

    /**
     * PUT for updateorderproperties api
     * @param string $orderCode
     * @param string $dispatchByTime
     * @param string $locationCode
     * @param string $onHold
     * @param string $startProcessingTime
     * @return string
     */
    public function putUpdateorderproperties($orderCode,$dispatchByTime,$locationCode,$onHold,$startProcessingTime);
}

