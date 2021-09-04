<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Model;

class PushsellercancellationManagement implements \Papa\Restapi\Api\PushsellercancellationManagementInterface
{

    /**
     * {@inheritdoc}
     */
    public function putPushsellercancellation($orderCode,$locationCode,$orderItems)
    {
        $_array = array("hasError"=>false,"status"=>'Success');
		
		$this->response[] = $_array;
		return $this->response;
    }
}

