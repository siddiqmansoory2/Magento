<?php

namespace Magedelight\OneStepCheckout\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Sales\Api\Data\OrderInterface;

interface RegistrationInterface
{
    /**
     * @param OrderInterface $order
     *
     * @return bool|CustomerInterface
     */
    public function createUser($order);

    /**
     * @param string $cartId
     * @param string $token
     * @return boolean
     */
    public function saveHashToken($cartId, $token);

    /**
     * @param $order
     * @return mixed
     */
    public function deleteHashToken($order);
}
