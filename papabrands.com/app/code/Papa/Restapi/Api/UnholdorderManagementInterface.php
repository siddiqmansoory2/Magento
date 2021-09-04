<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Api;

interface UnholdorderManagementInterface
{

    /**
     * PUT for unholdorder api
     * @param mixed $orderCode
     * @param mixed $locationCode
     * @return string
     */
    public function putUnholdorder($orderCode,$locationCode);
}

