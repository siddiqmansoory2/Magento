<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Api;

interface GetshippinglabelurlManagementInterface
{

    /**
     * PUT for getshippinglabelurl api
     * @param string $shipmentCode
     * @param string $orderCode
     * @return string
     */
    public function putGetshippinglabelurl($shipmentCode,$orderCode);
}

