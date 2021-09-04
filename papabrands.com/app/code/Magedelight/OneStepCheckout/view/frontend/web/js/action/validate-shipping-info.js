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
        'underscore',
        'uiRegistry',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magedelight_OneStepCheckout/js/model/validate-shipping',
        'Magedelight_OneStepCheckout/js/view/shipping',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/action/select-shipping-address'
    ],
    function (
        $,
        _,
        registry,
        addressList,
        quote,
        customer,
        ValidateShipping,
        Shipping,
        addressConverter,
        selectShippingAddress
    ) {
        'use strict';
        return function () {

            var loginFormSelector = 'form[data-role=email-with-possible-login]',
                emailValidationResult = customer.isLoggedIn();
            var addressComponent = registry.get('checkout.steps.shipping-step.shippingAddress');

            if (!quote.shippingMethod()) {
                ValidateShipping.errorValidationMessage('Please specify a shipping method.');
                return false;
            }

            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
            }

            if (addressComponent.isFormInline) {

                addressComponent.source.set('params.invalid', false);
                addressComponent.source.trigger('shippingAddress.data.validate');

                if (addressComponent.source.get('shippingAddress.custom_attributes')) {
                    addressComponent.source.trigger('shippingAddress.custom_attributes.data.validate');
                }

                if (addressComponent.source.get('params.invalid') ||
                    !quote.shippingMethod().method_code ||
                    !quote.shippingMethod().carrier_code ||
                    !emailValidationResult
                ) {
                    return false;
                }

                var shippingAddress = quote.shippingAddress();
                var addressData = addressConverter.formAddressDataToQuoteAddress(
                    addressComponent.source.get('shippingAddress')
                );

                for (var field in addressData) {

                    if (addressData.hasOwnProperty(field) &&
                        shippingAddress.hasOwnProperty(field) &&
                        typeof addressData[field] != 'function' &&
                        _.isEqual(shippingAddress[field], addressData[field])
                    ) {
                        shippingAddress[field] = addressData[field];
                    } else if (typeof addressData[field] != 'function' &&
                        !_.isEqual(shippingAddress[field], addressData[field])) {
                        shippingAddress = addressData;
                        break;
                    }
                }

                if (customer.isLoggedIn()) {
                    shippingAddress.save_in_address_book = 1;
                }
                selectShippingAddress(shippingAddress);
            }

            if (!emailValidationResult) {
                $(loginFormSelector + ' input[name=username]').focus();
                return false;
            }
            return true;
        };
    }
);
