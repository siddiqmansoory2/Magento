define(
    [
        'Magento_Ui/js/lib/validation/validator',
        'jquery',
        'jquery/ui',
        'jquery/validate',
        'mage/translate',
        'Magento_Customer/js/model/customer'
    ],
    function (validator, $, ui, validate, $t, customer) {
        'use strict';

        return {
            /**
             * Validate Login Form on checkout if available
             *
             * @returns {Boolean}
             */
            validate: function () {
                var loginForm = 'form[data-role=email-with-possible-login]',
                    password = $(loginForm).find('#register-customer-password');

                if (customer.isLoggedIn()) {
                    return true;
                }
                return $(loginForm).validation() && $(loginForm).validation('isValid');
                // if (password.val()) {
                //     return $(loginForm).validation() && $(loginForm).validation('isValid');
                // }
            }
        };
    }
);
