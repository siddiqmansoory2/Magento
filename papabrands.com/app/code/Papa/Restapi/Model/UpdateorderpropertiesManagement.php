<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Model;

class UpdateorderpropertiesManagement implements \Papa\Restapi\Api\UpdateorderpropertiesManagementInterface
{

    /**
     * {@inheritdoc}
     */
    public function putUpdateorderproperties($orderCode,$dispatchByTime,$locationCode,$onHold,$startProcessingTime)
    {
        
		$_array = array("hasError"=>false,"status"=>'Success');		
		header('Content-Type: application/json');
		echo $response = \Zend_Json::encode($_array);die;
    }
}

