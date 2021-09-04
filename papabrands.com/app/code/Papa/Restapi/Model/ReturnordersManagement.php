<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Model;

class ReturnordersManagement implements \Papa\Restapi\Api\ReturnordersManagementInterface
{

    /**
     * {@inheritdoc}
     */
    public function postReturnorders($forwardOrderCode,$returnOrderCode,$locationCode,$returnOrderTime,$orderItems,$orderType,$awbNumber,$transporter)
    {
        $_array = array("hasError"=>false,"status"=>'Success');
		
		$this->response[] = $_array;
		return $this->response;
    }
}

