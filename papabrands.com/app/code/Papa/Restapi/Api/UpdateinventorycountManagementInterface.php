<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Api;

interface UpdateinventorycountManagementInterface
{

    /**
     * PUT for updateinventorycount api
     * @param string $locationCode
     * @param mixed $inventories
     * @return string
     */
    public function putUpdateinventorycount($locationCode,$inventories);
}

