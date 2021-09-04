define([
    'jquery',
    'mage/utils/wrapper',
    'uiRegistry',
    'Magedelight_OneStepCheckout/js/action/save-information',
    'Magedelight_OneStepCheckout/js/model/login-form-validator',
    'Magedelight_OneStepCheckout/js/action/registration-action'
], function (
    $,
    wrapper,
    registry,
    saveAdditionalInfo,
    loginFormValidator,
    RegistrationAction
) {
    'use strict';

    return function (setPaymentInformationAction) {
        return wrapper.wrap(setPaymentInformationAction, function (originalAction, messageContainer, paymentData) {
            var shippingAddressComponent = registry.get('checkout.steps.shippingMethods');
            if(shippingAddressComponent) {
                if (shippingAddressComponent.validateShippingInformation() === false) {
                    return false;
                }
            }
            if(window.checkoutConfig.save_additional_info_from_payment === true) {
                if(window.checkoutConfig.mdoscAutoRegistrationEnabled) {
                    RegistrationAction();
                }
                if(window.checkoutConfig.mdoscRegistrationEnabled &&
                    registry.get("checkout.steps.shipping-step.shippingAddress.customer-email").isRegisterPasswordVisible()
                ) {
                    if(loginFormValidator.validate()) {
                        RegistrationAction();
                    } else {
                        return false;
                    }
                }
                saveAdditionalInfo();
            }
            return originalAction(messageContainer, paymentData);
        });
    };
});
