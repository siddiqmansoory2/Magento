/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Storepickup
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

define([
        'jquery',
        'mage/utils/wrapper',
        'underscore',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/checkout-data',
        'uiRegistry',
        'Magedelight_OneStepCheckout/js/model/billing-address-state'
    ], function (
        $,
        wrapper,
        _,
        ko,
        quote,
        resourceUrlManager,
        storage,
        paymentService,
        methodConverter,
        errorProcessor,
        fullScreenLoader,
        selectBillingAddressAction,
        createShippingAddress,
        selectShippingAddress,
        checkoutData,
        registry,
        BillingAddressState
    ) {
    'use strict';

    return function (payloadExtender) {
        return wrapper.wrap(payloadExtender, function (originalFunction, payload) {
            if (!quote.billingAddress() || BillingAddressState.sameAsShipping() === true) {
                selectBillingAddressAction(quote.shippingAddress());
            }

            payload = originalFunction(payload);

            _.extend(payload.addressInformation.extension_attributes, {
                'mdosc_extra_fee_checked': $('[name="extrafee_checkbox"]').prop("checked") ? 'checked' : 'unchecked',
            });

            return payload;
        });
    };
});