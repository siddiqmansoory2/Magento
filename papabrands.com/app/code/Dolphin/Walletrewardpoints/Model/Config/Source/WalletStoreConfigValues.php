<?php

namespace Dolphin\Walletrewardpoints\Model\Config\Source;

class WalletStoreConfigValues
{

    public function getStoreArray()
    {
        $storeVal = [];
        $storeVal["walletreward_wallet_status"] = "walletreward/wallet/enable";
        $storeVal["walletreward_wallet_credit_usages_buy_credite"] = "walletreward/wallet/credit_usages/enable";
        $storeVal["walletreward_wallet_credit_usages_max_credit_for_customer"] = "walletreward/wallet/credit_usages/max_credit_for_customer";
        $storeVal["walletreward_wallet_credit_usages_credit_with_coupons"] = "walletreward/wallet/credit_usages/credit_with_coupons";
        $storeVal["walletreward_wallet_withdraw_allow_withdrawal"] = "walletreward/wallet/withdraw/allow_withdrawal";
        $storeVal["walletreward_wallet_withdraw_min_withdraw"] = "walletreward/wallet/withdraw/min_withdraw";
        $storeVal["walletreward_wallet_withdraw_withdraw_email_sender"] = "walletreward/wallet/withdraw/withdraw_email_sender";
        $storeVal["walletreward_wallet_withdraw_withdraw_email_template"] = "walletreward/wallet/withdraw/withdraw_email_template";
        $storeVal["walletreward_wallet_withdraw_sendtofriend_allow_send_credit"] = "walletreward/wallet/sendtofriend/allow_send_credit";
        $storeVal["walletreward_wallet_withdraw_sendtofriend_email_sender"] = "walletreward/wallet/sendtofriend/stf_email_sender";
        $storeVal["walletreward_wallet_withdraw_sendtofriend_emailtemp"] = "walletreward/wallet/sendtofriend/sendtofriend_emailtemp";
        $storeVal["walletreward_wallet_withdraw_sendtofriend_sendto_unregisterfriend_emailtemp"] = "walletreward/wallet/sendtofriend/sendto_unregisterfriend_emailtemp";
        $storeVal["walletreward_wallet_order_max_credit_per_order"] = "walletreward/wallet/order/max_credit_per_order";
        $storeVal["walletreward_wallet_order_allow_max_credit_per_order"] = "walletreward/wallet/order/allow_max_credit_per_order";
        $storeVal["walletreward_wallet_order_percentage_of_order_subtotal"] = "walletreward/wallet/order/percentage_of_order_subtotal";
        $storeVal["walletreward_wallet_refund_credit"] = "walletreward/wallet/refund/refund_credit";
        $storeVal["walletreward_reward_enable"] = "walletreward/reward/enable_reward";
        $storeVal["walletreward_reward_one_point_cost"] = "walletreward/reward/one_point_cost";
        $storeVal["walletreward_reward_earn_reward_customer_registration_enable_on_create_account"] = "walletreward/reward/earn_reward/customer_registration/enable_on_create_account";
        $storeVal["walletreward_reward_earn_reward_customer_registration_reward_point"] = "walletreward/reward/earn_reward/customer_registration/ca_reward_point";
        $storeVal["walletreward_reward_earn_reward_creating_order_enable_create_order"] = "walletreward/reward/earn_reward/creating_order/enable_create_order";
        $storeVal["walletreward_reward_earn_reward_creating_order_min_order_qty"] = "walletreward/reward/earn_reward/creating_order/min_order_qty";
        $storeVal["walletreward_reward_earn_reward_creating_order_min_order_total"] = "walletreward/reward/earn_reward/creating_order/min_order_total";
        $storeVal["walletreward_reward_earn_reward_creating_order_earn_type"] = "walletreward/reward/earn_reward/creating_order/earn_type";
        $storeVal["walletreward_reward_earn_reward_creating_order_reward_point"] = "walletreward/reward/earn_reward/creating_order/co_reward_point";
        $storeVal["walletreward_reward_earn_reward_creating_order_max_reward_per_order"] = "walletreward/reward/earn_reward/creating_order/max_reward_per_order";
        $storeVal["walletreward_reward_earn_reward_creating_order_max_order"] = "walletreward/reward/earn_reward/creating_order/co_max_order";
        $storeVal["walletreward_reward_earn_reward_creating_order_reward_message"] = "walletreward/reward/earn_reward/creating_order/reward_message";
        $storeVal["walletreward_reward_earn_reward_newsletter_subscribers_enable"] = "walletreward/reward/earn_reward/newsletter_subscribers/enable_newsletter_subscribers";
        $storeVal["walletreward_reward_earn_reward_newsletter_subscribers_reward_point"] = "walletreward/reward/earn_reward/newsletter_subscribers/nl_reward_point";
        $storeVal["walletreward_reward_earn_reward_customer_review_enable"] = "walletreward/reward/earn_reward/customer_review/enable_customer_review";
        $storeVal["walletreward_reward_earn_reward_customer_review_reward_point"] = "walletreward/reward/earn_reward/customer_review/cr_reward_point";
        $storeVal["walletreward_reward_earn_reward_customer_review_max_review"] = "walletreward/reward/earn_reward/customer_review/cr_max_review";
        $storeVal["walletreward_reward_earn_reward_invited_friend_registration_enable"] = "walletreward/reward/earn_reward/invited_friend_registration/enable_ifr";
        $storeVal["walletreward_reward_earn_reward_invited_friend_registration_limit"] = "walletreward/reward/earn_reward/invited_friend_registration/ifr_limit";
        $storeVal["walletreward_reward_earn_reward_invited_friend_registration_reward_point"] = "walletreward/reward/earn_reward/invited_friend_registration/ifr_reward_point";
        $storeVal["walletreward_reward_earn_reward_invited_friend_registration_inv_email_sender"] = "walletreward/reward/earn_reward/invited_friend_registration/inv_email_sender";
        $storeVal["walletreward_reward_earn_reward_invited_friend_registration_invitefriendemailtemp"] = "walletreward/reward/earn_reward/invited_friend_registration/invitefriendemailtemp";
        $storeVal["walletreward_reward_earn_reward_invited_friend_registration_creating_order_enable_coif"] = "walletreward/reward/earn_reward/invited_friend_registration/creating_order_by_iv/enable_coif";
        $storeVal["walletreward_reward_earn_reward_invited_friend_registration_creating_order_coiv_earn_type"] = "walletreward/reward/earn_reward/invited_friend_registration/creating_order_by_iv/coiv_earn_type";
        $storeVal["walletreward_reward_earn_reward_invited_friend_registration_creating_order_coif_reward_point"] = "walletreward/reward/earn_reward/invited_friend_registration/creating_order_by_iv/coif_reward_point";
        $storeVal["walletreward_reward_earn_reward_invited_friend_registration_creating_order_coif_max_order"] = "walletreward/reward/earn_reward/invited_friend_registration/creating_order_by_iv/coif_max_order";
        $storeVal["walletreward_transaction_sub_email_sender"] = "walletreward/transaction/sub_email_sender";
        $storeVal["walletreward_transaction_subscribe"] = "walletreward/transaction/transaction_subscribe";
        $storeVal["walletreward_transaction_unsub_email_sender"] = "walletreward/transaction/unsub_email_sender";
        $storeVal["walletreward_transaction_unsubscribe"] = "walletreward/transaction/transaction_unsubscribe";
        return $storeVal;
    }
}
