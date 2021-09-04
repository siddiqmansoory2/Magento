<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Model;

class ReturnordernotificationManagement implements \Papa\Restapi\Api\ReturnordernotificationManagementInterface
{

    /**
     * {@inheritdoc}
     */
    public function putReturnordernotification($returnOrderCode,$locationCode,$orderItems)
    {
        /*$_inventories='';
		
		foreach($inventories as $__inventories){
			$_inventories=$__inventories;
		}*/
		
		$_array = array("hasError"=>false,"status"=>'Success');
		
		$this->response[] = $_array;
		return $this->response;
    }
}

