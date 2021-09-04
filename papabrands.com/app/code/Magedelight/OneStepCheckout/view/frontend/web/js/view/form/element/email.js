define([
    'jquery',
    'uiRegistry',
    'ko',
    'Magento_Checkout/js/view/form/element/email',
    'Magedelight_OneStepCheckout/js/model/login-form-validator',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'mage/validation',
    'Magento_Ui/js/lib/view/utils/async',
], function ($, registry, ko, Component, loginFormValidator, customerData, quote, checkoutData) {
    'use strict';

    /**
     * Get Amazon customer email
     */
    function getAmazonCustomerEmail() {
        // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        if (window.checkoutConfig.hasOwnProperty('amazonLogin') &&
            typeof window.checkoutConfig.amazonLogin.amazon_customer_email === 'string'
        ) {
            return window.checkoutConfig.amazonLogin.amazon_customer_email;
        }
        // jscs:enable requireCamelCaseOrUpperCaseIdentifiers

        return '';
    }

    return Component.extend({
        defaults: {
            email: checkoutData.getInputFieldEmailValue() || getAmazonCustomerEmail(),
            template: 'Magedelight_OneStepCheckout/form/element/email'
        },
        isRegisterPasswordVisible: ko.observable(false),
        isRegister: ko.observable(false),
        isPassword: ko.observable(false),

        /**
         * Init email validator
         */
        initialize: function () {
            this._super();
            //this._super().observe({isRegister: ko.observable(false)});
            if (this.email()) {

                if ($.validator.methods['validate-email'].call(this, this.email())) {
                    quote.guestEmail = this.email();
                    checkoutData.setValidatedEmailValue(this.email());
                }
                checkoutData.setInputFieldEmailValue(this.email());
            }

            this.isRegister.subscribe(function (newValue) {
                if(newValue){
                    this.isRegisterPasswordVisible(true);
                }else{
                    this.isRegisterPasswordVisible(false);
                }
            }.bind(this));

            return this;
        },

        initObservable: function () {
            this._super();
            return this;
        },

        isPasswordSet: function (element) {
            this.isPassword(!!element.value);
        },

        getRequiredPasswordCharacter: function () {
            return parseInt(registry.get("checkout.steps.shipping-step.shippingAddress.customer-email").requiredPasswordCharacter);
        },

        getMinimumPasswordLength: function () {
            return parseInt(registry.get("checkout.steps.shipping-step.shippingAddress.customer-email").minimumPasswordLength);
        },

        isRegisterVisible: function () {
            var flag = false;
            this.isPasswordVisible() ? flag = false : flag = true;
            return flag;
        },

        // isRegister: function () {
        //     this.isRegisterPasswordVisible(true);
        // }
    });
});
