<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Api;

interface GetinvoiceurlManagementInterface
{

    /**
     * PUT for getinvoiceurl api
     * @param string $shipmentCode
     * @param string $orderCode
     * @return mixed[]
     */
    public function putGetinvoiceurl($shipmentCode,$orderCode); 
}

