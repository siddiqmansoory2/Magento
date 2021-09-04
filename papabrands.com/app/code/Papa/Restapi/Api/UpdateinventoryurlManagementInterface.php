<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Api;

interface UpdateinventoryurlManagementInterface
{

    /**
     * PUT for updateinventorycount api
     * @param string $locationCode
     * @param mixed $inventories
     * @return mixed 
     */
    public function putUpdateinventoryurl($locationCode,$inventories);
}

