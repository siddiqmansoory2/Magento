define([
    'jquery',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Paypal/js/in-context/express-checkout-smart-buttons',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/translate'
], function (
    $,
    registry,
    quote,
    additionalValidators,
    checkoutSmartButtons,
    validateShippingInformationAction,
    selectBillingAddress,
    fullScreenLoader
) {
    'use strict';
    var mixin = {
            /**
             *  Validates Smart Buttons
             */
            validate: function () {
                if (this.actions) {
                    this.actions.enable();
                }
            },

            onClick: function () {
                var self = this;
                if(!this.customValidate(self)) {
                    self.addError("Please Fill all the required field first");
                }
                window.checkoutConfig.save_additional_info_from_payment = true;
                this.selectPaymentMethod();
            },

            customValidate: function (self) {
                var shippingAddressComponent = registry.get('checkout.steps.shippingMethods');
                if (additionalValidators.validate() === true) {
                    if (!quote.isVirtual()) {
                        if (shippingAddressComponent.validateShippingInformation()) {
                            validateShippingInformationAction().done(
                                function () {
                                    checkoutSmartButtons(self.prepareClientConfig(), window.paypalElement);
                                }
                            ).fail(
                                function () {
                                    fullScreenLoader.stopLoader();
                                }
                            );
                            return true;
                        }
                    }
                }
                return false;
            }
        };

    return function (target) {
        return target.extend(mixin);
    };
});