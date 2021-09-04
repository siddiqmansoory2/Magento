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
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/select-billing-address',
         'Magedelight_OneStepCheckout/js/model/billing-address-state'
    ],
    function (
        $,
        ko,
        quote,
        resourceUrlManager,
        storage,
        errorProcessor,
        fullScreenLoader,
        selectBillingAddressAction,
        BillingAddressState
    ) {
        'use strict';

        return {
            saveShippingInformation: function () {
                var payload;

                if (!quote.billingAddress() || BillingAddressState.sameAsShipping() == true) {
                    selectBillingAddressAction(quote.shippingAddress());
                }
                payload = {
                    addressInformation: {
                        shipping_address: quote.shippingAddress(),
                        billing_address: quote.billingAddress(),
                        shipping_method_code: quote.shippingMethod().method_code,
                        shipping_carrier_code: quote.shippingMethod().carrier_code,
                        extension_attributes:{
                            md_osc_delivery_date: $('[name="md_osc_delivery_date"]').val(),
                            md_osc_delivery_time: $('[name="md_osc_delivery_time"]').val(),
                            md_osc_delivery_comment: $('[name="md_osc_delivery_comment"]').val(),
                            mdosc_extra_fee_checked: $('[name="extrafee_checkbox"]').prop("checked") ? 'checked' : 'unchecked',
                        }
                    }
                };

                fullScreenLoader.startLoader();

                return storage.post(
                    resourceUrlManager.getUrlForSetShippingInformation(quote),
                    JSON.stringify(payload)
                ).done(
                    function (response) {
                        quote.setTotals(response.totals);
                        fullScreenLoader.stopLoader();
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                        fullScreenLoader.stopLoader();
                    }
                );
            }
        };
    }
);
