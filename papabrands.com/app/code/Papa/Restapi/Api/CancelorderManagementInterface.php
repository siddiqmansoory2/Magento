<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Papa\Restapi\Api;

interface CancelorderManagementInterface
{

    /**
     * POST for postCancelorder api
     * @param mixed $order_id
     * @return string
     */
    public function postCancelorder($order_id);
}

