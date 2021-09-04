<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Model;

class UnholdorderManagement implements \Papa\Restapi\Api\UnholdorderManagementInterface
{

    /**
     * {@inheritdoc}
     */
    public function putUnholdorder($orderCode,$locationCode)
    {
        $paramaters = json_encode(array("hasError"=>false,"status"=>'Success'));
		return $paramaters;
    }
}

