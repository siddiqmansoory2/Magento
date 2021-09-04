<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Api;

interface CreateshipmenturlManagementInterface
{

     /**
     * POST for packorder api
     * @param mixed $orderCode
     * @param mixed $locationCode
     * @param mixed $shipmentId
     * @param mixed $dimensions
     * @param mixed $invoice
     * @param mixed $shipmentItems
     * @return string
     */
    public function putCreateshipmenturl($orderCode,$locationCode,$shipmentId,$dimensions,$invoice,$shipmentItems);
}

