/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OneStepCheckout
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
define([
    'underscore',
    'ko',
    'mageUtils',
    'uiComponent',
    'Magento_Checkout/js/model/payment/method-list',
    'Magento_Checkout/js/model/payment/renderer-list',
    'uiLayout',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'mage/translate',
    'uiRegistry',
    'Magento_Checkout/js/view/payment/list',
    'Amazon_Payment/js/view/payment/list'
], function (_, ko, utils, Component, paymentMethods, rendererList, layout, checkoutDataResolver, $t, registry, List, AmazonList) {
    'use strict';

        return List.extend({
            defaults: {
                template: 'Magedelight_OneStepCheckout/payment-methods/list'
            },
            /**
             * Returns payment group title
             *
             * @param {Object} group
             * @returns {String}
             */
            getGroupTitle: function (group) {
                var title = group().title;

                if (group().isDefault() && this.paymentGroupsList().length > 1) {
                    title = this.defaultGroupTitle;
                }
                return title;
            },

            getPaymentListTitle: function () {
                return window.checkoutConfig.payment_step_config_label ?
                    window.checkoutConfig.payment_step_config_label :
                    'Payment Methods';
            },

            canDisplayWallet: function (group){
                if(group().index === "vaultGroup"){
                    return false;
                }
                return true;
            },

            getSequence: function() {
                return parseInt(registry.get("checkout.steps.billing-step").sortOrder) + 1;
            }
        });
    }
);
