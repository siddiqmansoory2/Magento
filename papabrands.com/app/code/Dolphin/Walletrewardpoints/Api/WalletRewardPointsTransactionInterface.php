<?php

namespace Dolphin\Walletrewardpoints\Api;

interface WalletRewardPointsTransactionInterface
{

    /**
     * POST Invite To Friend Save Customer Data
     * @param string[] $data
     * @return array
     */
    public function inviteToFriendSave($data);

    /**
     * GET Invite To Friend Customer Data
     * @param int $customerid
     * @return \Dolphin\Walletrewardpoints\Model\InviteFriendFactory
     */
    public function inviteSendToFriendData($customerid);

    /**
     * POST Send To Friend Save Customer Data
     * @param string[] $data
     * @return array
     */
    public function sendCreditToFriend($data);

    /**
     * POST Wallet Customer Credit Withdraw
     * @param string[] $data
     * @return array
     */
    public function withdrawCustomerCredit($data);

    /**
     * GET Wallet Credit Show Withdraw
     * @param int $customerid
     * @return \Dolphin\Walletrewardpoints\Model\WithdrawFactory
     */
    public function showCustomerWithdraw($customerid);

    /**
     * GET Show Wallet Credit Transactions
     * @param int $customerid
     * @return \Dolphin\Walletrewardpoints\Model\WithdrawFactory
     */
    public function showTransactionsHistory($customerid);

    /**
     * POST Customer Wallet Reward Email Subscription
     * @param string[] $data
     * @return array
     */
    public function setWalletEmailSubscription($data);

    /**
     * GET Customer Wallet Reward Email Subscription Status
     * @param int $customerid
     * @return array
     */
    public function getWalletEmailSubscriptionStatus($customerid);

    /**
     * POST Wallet Reward Buy Credit
     * @param string[] $data
     * @return array
     */
    public function WithdrawawCustomerCredit($data);

    /**
     * PUT Apply Wallete Credit To Cart
     * @param int $cartId The cart ID.
     * @param int $credit wallet credit
     * @return array
     */
    public function applyWalleteCredit($cartId, $credit);

    /**
     * DELETE Remove Wallete Credit From Cart
     * @param int $cartId The cart ID.
     * @return array
     */
    public function removeWalleteCredit($cartId);

    /**
     * GET Wallete Store Config Values
     * @return array
     */
    public function getStoreConfig();
}
