<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Model;

class PushcustomercancellationManagement implements \Papa\Restapi\Api\PushcustomercancellationManagementInterface
{

    /**
     * {@inheritdoc}
     */
    public function putPushcustomercancellation($locationCode,$orderItems)
    {
        $paramaters = json_encode(array("hasError"=>false,"status"=>'Success'));
		return $paramaters;
    }
}

