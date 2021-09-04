<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Api;

interface ReturnordersManagementInterface
{

    /**
     * POST for returnorders api
     * @param mixed $forwardOrderCode
     * @param mixed $returnOrderCode
     * @param mixed $locationCode
     * @param mixed $returnOrderTime
     * @param mixed $orderItems
     * @param mixed $orderType
     * @param mixed $awbNumber
     * @param mixed $transporter
     * @return string
     */
    public function postReturnorders($forwardOrderCode,$returnOrderCode,$locationCode,$returnOrderTime,$orderItems,$orderType,$awbNumber,$transporter);
}

